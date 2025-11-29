<?php

// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import Fassad Auth
use Illuminate\Support\Facades\Validator; // Import Validator
use Illuminate\Http\RedirectResponse; // Import tipe RedirectResponse
use Illuminate\View\View; // Import tipe View

class AuthController extends Controller
{
    /**
     * Menampilkan halaman/form login.
     * Rute: GET /login
     */
    public function showLoginForm(): View
    {
        // Hanya mengembalikan view 'auth.login'
        // Kita akan buat file ini di langkah berikutnya
        return view('auth.login');
    }

    /**
     * Menangani proses login.
     * Rute: POST /login
     */
    public function login(Request $request): RedirectResponse
    {
        // 1. Validasi input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        // 2. Ambil kredensial yang sudah divalidasi
        $credentials = $validator->validated();

        // 3. Coba lakukan autentikasi
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            // 4. Jika berhasil, regenerasi session (keamanan)
            $request->session()->regenerate();

            // 5. Redirect ke halaman 'dashboard' (rute '/')
            // intended() akan mengarahkan ke halaman yg sebelumnya ingin diakses
            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali!');
        }

        // 6. Jika gagal, kembali ke halaman login
        return back()
            ->withErrors([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ])
            ->onlyInput('email'); // Kembalikan input email saja
    }

    /**
     * Menangani proses logout.
     * Rute: POST /logout
     */
    public function logout(Request $request): RedirectResponse
    {
        // 1. Logout user
        Auth::logout();

        // 2. Invalidate session (wajib)
        $request->session()->invalidate();

        // 3. Regenerasi CSRF token (wajib)
        $request->session()->regenerateToken();

        // 4. Redirect ke halaman login
        return redirect()->route('login')
            ->with('status', 'Anda telah berhasil logout.');
    }
}
