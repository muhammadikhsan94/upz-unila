<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - Unit Pengelola Zakat Universitas Lampung</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <!-- favicon -->
    <link rel="icon" href="{{ asset('images/logo-unila.png') }}" type="image/png">
    <link rel="stylesheet" type="text/css" href="{{ asset('lte/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('lte/bower_components/font-awesome/css/font-awesome.min.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2c3e50 0%, #3c8dbc 50%, #5faee3 100%);
            padding: 20px;
        }
        .login-container {
            width: 100%;
            max-width: 420px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #357ca5 0%, #3c8dbc 100%);
            padding: 30px 20px;
            text-align: center;
            color: white;
        }
        .login-header .logo-container {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .login-header .logo-container img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        .login-header h1 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .login-header p {
            font-size: 13px;
            opacity: 0.9;
            margin: 0;
        }
        .login-body {
            padding: 35px 30px;
        }
        .login-body h2 {
            font-size: 22px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            text-align: center;
        }
        .login-body .subtitle {
            font-size: 13px;
            color: #777;
            text-align: center;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        .form-group label {
            font-size: 13px;
            font-weight: 500;
            color: #555;
            margin-bottom: 8px;
            display: block;
        }
        .form-group .input-wrapper {
            position: relative;
        }
        .form-group .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: 16px;
        }
        .form-group input {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 2px solid #e8e8e8;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f9f9f9;
        }
        .form-group input:focus {
            outline: none;
            border-color: #3c8dbc;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(60, 141, 188, 0.1);
        }
        .form-group input::placeholder {
            color: #aaa;
        }
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 13px;
        }
        .form-options label {
            display: flex;
            align-items: center;
            color: #666;
            cursor: pointer;
        }
        .form-options input[type="checkbox"] {
            margin-right: 8px;
            accent-color: #3c8dbc;
        }
        .form-options a {
            color: #3c8dbc;
            text-decoration: none;
            font-weight: 500;
        }
        .form-options a:hover {
            text-decoration: underline;
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #357ca5 0%, #3c8dbc 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #2c6a91 0%, #367fa9 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(60, 141, 188, 0.4);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .login-footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #eee;
            margin-top: 25px;
        }
        .login-footer p {
            font-size: 13px;
            color: #666;
            margin-bottom: 10px;
        }
        .btn-register {
            display: inline-block;
            padding: 10px 30px;
            background: transparent;
            border: 2px solid #3c8dbc;
            border-radius: 8px;
            color: #3c8dbc;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            background: #3c8dbc;
            color: white;
            text-decoration: none;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .copyright {
            text-align: center;
            margin-top: 20px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 12px;
        }
        .copyright a {
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-container">
                    <img src="{{ asset('images/logo-unila.png') }}" alt="Logo Unila">
                </div>
                <h1>Unit Pengelola Zakat</h1>
                <p>Universitas Lampung</p>
            </div>

            <div class="login-body">
                <h2>Selamat Datang</h2>
                <p class="subtitle">Silakan masuk untuk melanjutkan</p>

                @if ($message = Session::get('success'))
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i> {{ $message }}
                </div>
                @endif

                @if(session('failed'))
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-circle"></i> {{ session('failed') }}
                </div>
                @endif

                @error('username')
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-circle"></i> {{ $message }}
                </div>
                @enderror

                <form action="{{ url('login') }}" method="post">
                    @csrf
                    <div class="form-group">
                        <label for="username">Username / Email</label>
                        <div class="input-wrapper">
                            <i class="fa fa-user"></i>
                            <input type="text" name="username" id="username" placeholder="Masukkan username atau email" required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Kata Sandi</label>
                        <div class="input-wrapper">
                            <i class="fa fa-lock"></i>
                            <input type="password" name="password" id="password" placeholder="Masukkan kata sandi" required>
                        </div>
                    </div>

                    <div class="form-options">
                        <label>
                            <input type="checkbox" name="remember"> Ingat saya
                        </label>
                        <a href="{{ url('/forgot-password') }}">Lupa kata sandi?</a>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fa fa-sign-in"></i> Masuk
                    </button>
                </form>

                <div class="login-footer">
                    <p>Civitas Akademik</p>
                    <a href="{{ route('loginSSO') }}" class="btn-register">LOGIN WITH SSO</a>
                </div>
            </div>
        </div>

        <p class="copyright">
            &copy; {{ date('Y') }} Unit Pengelola Zakat - <a href="https://unila.ac.id" target="_blank">Universitas Lampung</a>
        </p>
    </div>
</body>
</html>
