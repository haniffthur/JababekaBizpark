import time
import json
import requests
import datetime
from flask import Flask, request, jsonify
from flask_cors import CORS
import sys

app = Flask(__name__)
CORS(app)

# ==============================================================================
# 1. KONFIGURASI DASAR
# ==============================================================================
LARAVEL_IP = "192.168.200.100"
LARAVEL_URL = f"http://{LARAVEL_IP}/api/gate"

# ==============================================================================
# 2. MEMORI TERPISAH (BUFFER GATE)
# ==============================================================================
# Kita buat "Dua Ember" terpisah. 
# Key "1" untuk Masuk, Key "0" untuk Keluar.
BUFFER_GATE = {
    "1": { # EMBER MASUK
        "qr": None, "plat": None, 
        "ts_qr": 0, "ts_plat": 0, 
        "termno": "001", "waktu_respon": 0, "hasil_respon": None
    },
    "0": { # EMBER KELUAR
        "qr": None, "plat": None, 
        "ts_qr": 0, "ts_plat": 0, 
        "termno": "002", "waktu_respon": 0, "hasil_respon": None
    }
}

# ==============================================================================
# 3. HELPER FORMAT RESPON
# ==============================================================================
def format_response(status, message, io_mode, qr=None, plat=None):
    direction = "In" if io_mode == "1" else "Out"
    term_used = BUFFER_GATE[io_mode]["termno"]
    
    return {
        "Status": status,
        "Date": datetime.datetime.now().strftime("%d-%m-%Y %H:%M:%S"),
        "Message": message,
        "QrCode": qr,
        "Plat": plat,
        "Direction": direction,
        "TerminalID": term_used,
        "IO": io_mode
    }

# ==============================================================================
# 4. ENDPOINT: TERIMA PLAT (KAMERA HARUS SET PARAMETER IO)
# ==============================================================================
@app.route("/api/terima_plat", methods=['POST'])
def terima_plat():
    # --- PENTING: AMBIL IO DARI URL KAMERA ---
    # Setting di kamera Masuk: http://IP:5000/api/terima_plat?io=1
    # Setting di kamera Keluar: http://IP:5000/api/terima_plat?io=0
    req_io = request.args.get("io") 
    
    # Jika kamera lupa disetting param IO-nya, kita reject atau default ke error
    if req_io not in ["0", "1"]:
        print("\n[KAMERA ERROR] Parameter ?io=1 atau ?io=0 tidak ditemukan di URL Kamera!")
        return jsonify({"status": "error", "message": "missing io parameter"})

    plat_terbaca = None
    try:
        raw_data = request.get_data(as_text=True)
        if request.is_json:
            data_json = request.json
            if data_json.get("Active") == "keepAlive": return jsonify({"status": "ignored"})

            # Parsing standar Hikvision/Dahua/ZK
            if "Picture" in data_json and "Plate" in data_json["Picture"]:
                plat_terbaca = data_json["Picture"]["Plate"].get("PlateNumber")
            elif "Plate" in data_json and isinstance(data_json["Plate"], dict):
                 plat_terbaca = data_json["Plate"].get("PlateNumber")
    except: pass

    if plat_terbaca:
        arah = "MASUK" if req_io == "1" else "KELUAR"
        print(f"\n[KAMERA {arah}] 📸 Plat: {plat_terbaca}")
        
        # Simpan ke EMBER yang sesuai dengan IO
        BUFFER_GATE[req_io]["plat"] = plat_terbaca
        BUFFER_GATE[req_io]["ts_plat"] = time.time()
        
        # Cek hanya bucket IO ini
        return cek_dan_kirim(req_io) 
    
    return jsonify({"status": "no valid plate data"})

# ==============================================================================
# 5. ENDPOINT: TERIMA QR (READER MENENTUKAN EMBER)
# ==============================================================================
@app.route("/api/terima_qr", methods=['GET', 'POST'])
def terima_qr():
    args = request.args
    qr_terbaca = args.get("qr_code") or args.get("code") or args.get("data") or request.values.get("qr_code")
    req_termno = args.get("termno")

    if not req_termno:
        return jsonify(format_response(0, "Terminal ID (termno) Missing", "1"))

    # --- LOGIKA PENENTUAN IO BERDASARKAN TERMNO ---
    # Ganjil = 1 (Masuk), Genap = 0 (Keluar)
    # Reader 1, 3 => Masuk ke Ember "1"
    # Reader 2, 4 => Masuk ke Ember "0"
    if int(req_termno) % 2 != 0:
        current_io = "1"
    else:
        current_io = "0"

    # Update TermNo terakhir yang aktif di bucket tersebut
    BUFFER_GATE[current_io]["termno"] = req_termno

    if qr_terbaca:
        arah = "MASUK" if current_io == "1" else "KELUAR"
        print(f"\n[SCANNER {arah}] 📟 QR: {qr_terbaca} (Term: {req_termno})")
        
        # Simpan ke EMBER yang sesuai
        BUFFER_GATE[current_io]["qr"] = qr_terbaca
        BUFFER_GATE[current_io]["ts_qr"] = time.time()
        
        return tunggu_hasil_browser(qr_terbaca, current_io)
    
    return jsonify(format_response(0, "Tidak ada data QR", current_io))

# ==============================================================================
# 6. LOGIKA UTAMA (PER IO)
# ==============================================================================

def cek_dan_kirim(io_mode, return_json=False):
    # Ambil data HANYA dari ember yang relevan
    bucket = BUFFER_GATE[io_mode]
    
    qr = bucket["qr"]
    plat = bucket["plat"]
    
    # Cek apakah pasangan lengkap di ember ini
    if qr and plat:
        # Validasi waktu (misal expired 60 detik)
        if abs(bucket["ts_qr"] - bucket["ts_plat"]) < 60:
            
            print(f"--> [IO:{io_mode}] MATCH! Kirim ke Laravel...")
            
            payload = {
                "termno": bucket["termno"],
                "IO": io_mode,
                "qr_code": qr,
                "license_plate": plat
            }
            
            # Kosongkan Ember SEBELUM request (untuk mencegah double entry cepat)
            bucket["qr"] = None
            bucket["plat"] = None
            
            try:
                response = requests.get(LARAVEL_URL, params=payload, timeout=10)
                if response.status_code == 200:
                    res_json = response.json()
                    status_msg = "✅ SUKSES" if res_json.get("Status") == 1 else "❌ DITOLAK"
                    print(f"--> [IO:{io_mode}] {status_msg}: {res_json.get('Message')}")
                    
                    # Simpan hasil terakhir di ember tersebut untuk polling
                    bucket["hasil_respon"] = res_json
                    bucket["waktu_respon"] = time.time()
                    return res_json
                
                return format_response(0, f"Server Error {response.status_code}", io_mode, qr, plat)

            except Exception as e:
                print(f"--> ERROR: {e}")
                return format_response(0, "Koneksi Gagal", io_mode, qr, plat)
        else:
             # Reset logic jika expired (mana yang lebih tua dihapus)
             if bucket["ts_qr"] < bucket["ts_plat"]: bucket["qr"] = None
             else: bucket["plat"] = None

    if not return_json:
        return jsonify(format_response(0, f"Menunggu pasangan di Gate IO {io_mode}...", io_mode, qr, plat))
    
    return None

def tunggu_hasil_browser(qr, io_mode):
    # Polling khusus untuk IO tertentu
    bucket = BUFFER_GATE[io_mode]
    
    for _ in range(60): # 60 detik timeout
        res = cek_dan_kirim(io_mode, return_json=True)
        if res: return jsonify(res)
        
        # Cek apakah hasil sudah tersedia di cache memori (jika trigger dari plat duluan)
        if bucket["hasil_respon"] and (time.time() - bucket["waktu_respon"] < 5):
             # Pastikan respon ini milik QR yang sama (opsional, tapi aman)
             # Di sini kita asumsikan urutan kejadian linier per gate
             return jsonify(bucket["hasil_respon"])
             
        time.sleep(0.5)
        
    return jsonify(format_response(0, "Timeout: Menunggu plat nomor...", io_mode, qr=qr))

if __name__ == '__main__':
    print(f"\n{'='*50}")
    print(f" OTAK DUAL-CHANNEL (IN/OUT) AKTIF")
    print(f" Pastikan URL Kamera diset:")
    print(f" - Kamera Masuk : /api/terima_plat?io=1")
    print(f" - Kamera Keluar: /api/terima_plat?io=0")
    print(f"{'='*50}\n")
    app.run(host='0.0.0.0', port=5000, debug=False, threaded=True)