import time
import json
import requests
import serial
import datetime
from flask import Flask, request, jsonify
from flask_cors import CORS
import sys
import os

app = Flask(__name__)
CORS(app)

# ==============================================================================
# 1. KONFIGURASI DASAR
# ==============================================================================
# IP Server Laravel (Tetap harus di-set)
LARAVEL_IP = "192.168.200.37"
LARAVEL_URL = f"http://{LARAVEL_IP}/api/gate"



# ==============================================================================
# 2. MEMORI DINAMIS
# ==============================================================================
# Kita simpan termno dan IO di dalam memori, karena bisa berubah-ubah dari URL
data_terkumpul = {
    "qr": None, 
    "plat": None, 
    "ts_qr": 0, 
    "ts_plat": 0,
    "current_termno": "001", # Default (jika tidak ada info)
    "current_io": "1"        # Default (1=Masuk)
}

hasil_terakhir = {
    "qr": None, "response": None, "waktu": 0
}

# ==============================================================================
# 3. HELPER FORMAT RESPON
# ==============================================================================
def format_response(status, message, qr=None, plat=None):
    # Ambil status arah dari memori saat ini
    direction = "In" if data_terkumpul["current_io"] == "1" else "Out"
    
    return {
        "Status": status,
        "Date": datetime.datetime.now().strftime("%d-%m-%Y %H:%M:%S"),
        "Message": message,
        "QrCode": qr,
        "Plat": plat,
        "Direction": direction,
        "TerminalID": data_terkumpul["current_termno"], # Tampilkan TermNo yang aktif
        "IO": data_terkumpul["current_io"]
    }

# ==============================================================================
# 4. ENDPOINT: TERIMA PLAT (KAMERA)
# ==============================================================================
@app.route("/api/terima_plat", methods=['POST'])
def terima_plat():
    plat_terbaca = None
    try:
        raw_data = request.get_data(as_text=True)
        if request.is_json:
            data_json = request.json
            if data_json.get("Active") == "keepAlive": return jsonify({"status": "ignored"})

            if "Picture" in data_json and "Plate" in data_json["Picture"]:
                plat_terbaca = data_json["Picture"]["Plate"].get("PlateNumber")
            elif "Plate" in data_json and isinstance(data_json["Plate"], dict):
                 plat_terbaca = data_json["Plate"].get("PlateNumber")
    except: pass

    if plat_terbaca:
        print(f"\n[KAMERA] 📸 Plat: {plat_terbaca}")
        data_terkumpul["plat"] = plat_terbaca
        data_terkumpul["timestamp_plat"] = time.time()
        return cek_dan_kirim() 
    
    return jsonify({"status": "no valid plate data"})

# ==============================================================================
# 5. ENDPOINT: TERIMA QR (DINAMIS DARI URL)
# ==============================================================================
@app.route("/api/terima_qr", methods=['GET', 'POST'])
def terima_qr():
    qr_terbaca = None
    args = request.args
    qr_terbaca = args.get("qr_code") or args.get("code") or args.get("data")
    if not qr_terbaca: qr_terbaca = request.values.get("qr_code")

    # --- LOGIKA DINAMIS: BACA TERMNO DARI URL ---
    req_termno = args.get("termno")
    
    if req_termno:
        # Update Memori dengan TermNo baru
        data_terkumpul["current_termno"] = req_termno
        
        # Hitung IO secara otomatis (Ganjil=1, Genap=0)
        if int(req_termno) % 2 != 0:
            data_terkumpul["current_io"] = "1"
            mode_str = "MASUK (IN)"
        else:
            data_terkumpul["current_io"] = "0"
            mode_str = "KELUAR (OUT)"
            
        print(f"--> ⚙️ KONFIG UPDATE: Term {req_termno} => {mode_str}")
    # --------------------------------------------

    if qr_terbaca:
        print(f"\n[SCANNER] 📟 QR: {qr_terbaca}")
        data_terkumpul["qr"] = qr_terbaca
        data_terkumpul["timestamp_qr"] = time.time()
        return tunggu_hasil_browser(qr_terbaca)
    
    return jsonify(format_response(0, "Tidak ada data QR"))

# ==============================================================================
# 6. LOGIKA UTAMA
# ==============================================================================

def cek_dan_kirim(return_json=False):
    qr = data_terkumpul["qr"]
    plat = data_terkumpul["plat"]
    
    if qr and plat:
        if abs(data_terkumpul["timestamp_qr"] - data_terkumpul["timestamp_plat"]) < 60:
            
            # Ambil konfigurasi yang tersimpan di memori saat ini
            term_now = data_terkumpul["current_termno"]
            io_now = data_terkumpul["current_io"]
            
            print(f"--> DATA LENGKAP! Kirim ke Laravel (Term: {term_now}, IO: {io_now})...")
            
            payload = {
                "termno": term_now,
                "IO": io_now,
                "qr_code": qr,
                "license_plate": plat
            }
            
            data_terkumpul["qr"] = None
            data_terkumpul["plat"] = None
            
            try:
                response = requests.get(LARAVEL_URL, params=payload, timeout=10)
                if response.status_code == 200:
                    res_json = response.json()
                    if res_json.get("Status") == 1:
                        print(f"--> ✅ SUKSES: {res_json.get('Message')}")
                    
                    else:
                        print(f"--> ❌ DITOLAK: {res_json.get('Message')}")
                    
                    hasil_terakhir["qr"] = qr
                    hasil_terakhir["response"] = res_json
                    hasil_terakhir["waktu"] = time.time()
                    return res_json
                
                return format_response(0, f"Server Error {response.status_code}", qr, plat)

            except Exception as e:
                print(f"--> ERROR: {e}")
                return format_response(0, "Koneksi Gagal", qr, plat)
        else:
             # Reset logic
             if data_terkumpul["timestamp_qr"] < data_terkumpul["timestamp_plat"]: data_terkumpul["qr"] = None
             else: data_terkumpul["plat"] = None

    if not return_json:
        return jsonify(format_response(0, "Menunggu pasangan data...", qr, plat))
    
    return None

def tunggu_hasil_browser(qr):
    for _ in range(120):
        res = cek_dan_kirim(return_json=True)
        if res: return jsonify(res)
        if hasil_terakhir["qr"] == qr and (time.time() - hasil_terakhir["waktu"] < 5):
             return jsonify(hasil_terakhir["response"])
        time.sleep(0.5)
    return jsonify(format_response(0, "Timeout: Menunggu plat nomor...", qr=qr))

if __name__ == '__main__':
    print(f"\n{'='*50}")
    print(f" OTAK LOKAL DINAMIS AKTIF")
    print(f" Menunggu input ?termno=... dari Scanner")
    print(f"{'='*50}\n")
    app.run(host='0.0.0.0', port=5000, debug=False, threaded=True)