{{-- resources/views/auth/login.blade.php (Tema Jababeka) --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - GudangJababeka</title>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    
    {{-- =================================== --}}
    {{-- == STYLE BARU (TEMA JABABEKA) == --}}
    {{-- =================================== --}}
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            /* GANTI: Gradien Biru -> Gradien Hijau (dari logo) */
            background-image: 'img/bgbizpark.jpg';
            background: linear-gradient(135deg, #005a41 0%, #007a5a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 440px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-header {
            text-align: center;
            padding: 40px 40px 30px;
            /* GANTI: Gradien Biru -> Gradien Hijau (dari logo) */
            background: linear-gradient(135deg, #005a41 0%, #007a5a 100%);
            position: relative;
        }

        .logo-container {
            width: 80px;
            height: 80px;
            background: #ffffff;
            border-radius: 20px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 5px; /* Tambahan padding */
        }

        .logo-container img {
            /* GANTI: Sesuaikan agar logo Jababeka pas */
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .login-header h1 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .login-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 15px;
            font-weight: 400;
        }

        .login-body {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 16px;
        }

        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 16px;
            cursor: pointer;
            transition: color 0.3s ease;
            z-index: 10;
        }

        /* GANTI: Aksen Biru -> Aksen Emas (dari logo) */
        .toggle-password:hover {
            color: #e89a0f; 
        }

        .form-control-modern {
            width: 100%;
            padding: 14px 45px 14px 45px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 400;
            color: #1f2937;
            transition: all 0.3s ease;
            background: #ffffff;
        }

        /* GANTI: Fokus Biru -> Fokus Hijau */
        .form-control-modern:focus {
            outline: none;
            border-color: #007a5a;
            box-shadow: 0 0 0 4px rgba(0, 122, 90, 0.1);
        }

        .form-control-modern.is-invalid {
            border-color: #ef4444;
        }

        .form-control-modern.is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        .invalid-feedback {
            display: block;
            color: #ef4444;
            font-size: 13px;
            margin-top: 6px;
            margin-left: 4px;
        }

        .remember-forgot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .custom-checkbox-modern {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        /* GANTI: Aksen Biru -> Aksen Hijau */
        .custom-checkbox-modern input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            cursor: pointer;
            accent-color: #005a41;
        }

        .custom-checkbox-modern label {
            font-size: 14px;
            color: #6b7280;
            cursor: pointer;
            user-select: none;
            margin-bottom: 0; /* Bootstrap override */
        }

        /* GANTI: Tombol Biru -> Tombol Hijau */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #005a41 0%, #007a5a 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 90, 65, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 90, 65, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        @media (max-width: 576px) {
            .login-body { padding: 30px 25px; }
            .login-header { padding: 30px 25px 25px; }
            .login-header h1 { font-size: 24px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-container">
                    {{-- GANTI: Path ke logo barumu --}}
                    <img src="{{ asset('img/Logo.jpeg') }}" alt="Logo Jababeka">
                </div>
                <h1>GudangJababeka</h1>
                <p>Gate System Login</p>
            </div>

            <div class="login-body">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input 
                                type="email" 
                                name="email" 
                                class="form-control-modern @error('email') is-invalid @enderror" 
                                placeholder="nama@email.com" 
                                value="{{ old('email') }}" 
                                required 
                                autofocus
                            >
                        </div>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input 
                                type="password" 
                                name="password" 
                                id="password"
                                class="form-control-modern @error('password') is-invalid @enderror"
                                placeholder="Masukkan password" 
                                required
                            >
                            <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                        </div>
                         @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="remember-forgot">
                        <div class="custom-checkbox-modern">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Ingat Saya</label>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        Masuk
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    
    <script>
        // Toggle Password Visibility (Kode ini tetap sama)
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>