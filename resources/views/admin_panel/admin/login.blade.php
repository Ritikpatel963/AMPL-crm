<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            height: 100vh;
            background: linear-gradient(135deg, #2b5876, #4e4376);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .admin-glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .admin-glass-card h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .admin-glass-card label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 500;
        }

        .admin-glass-card input {
            width: 100%;
            padding: 12px 14px;
            border: none;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 15px;
            background-color: rgba(255, 255, 255, 0.15);
            color: #fff;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .admin-glass-card input::placeholder {
            color: #e0e0e0;
        }

        .admin-glass-card input:focus {
            background-color: rgba(255, 255, 255, 0.25);
            outline: none;
            box-shadow: 0 0 0 2px #00c3ff;
        }

        .admin-login-button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            background-color: #00c3ff;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .admin-login-button:hover {
            background-color: #00a6db;
        }

        .admin-error-message {
            background: rgba(255, 0, 0, 0.1);
            padding: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #ff4d4d;
            color: #ffcccc;
            font-size: 14px;
            border-radius: 6px;
        }

        @media (max-width: 480px) {
            .admin-glass-card {
                padding: 30px 20px;
            }

            .admin-glass-card h2 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-glass-card">
        <h2>Admin Login</h2>

        @if(session('error'))
            <div class="admin-error-message">
                {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div class="admin-error-message" style="border-left-color:#22c55e;color:#d1fae5;background:rgba(34,197,94,0.12);">
                {{ session('success') }}
            </div>
        @endif
        @error('phone')
            <div class="admin-error-message">
                {{ $message }}
            </div>
        @enderror
        @error('otp')
            <div class="admin-error-message">
                {{ $message }}
            </div>
        @enderror

        @php
            $otpPhone = session('admin_login_phone', old('phone'));
        @endphp

        <form method="POST" action="{{ route('admin_panel.admin.send.otp') }}">
            @csrf
            <label for="phone">Phone Number</label>
            <input type="tel" name="phone" id="phone" value="{{ $otpPhone }}" placeholder="Enter phone number" required>

            <button type="submit" class="admin-login-button">Send OTP</button>
        </form>

        @if($otpPhone)
            <form method="POST" action="{{ route('admin_panel.admin.login.submit') }}" style="margin-top:20px;">
                @csrf
                <input type="hidden" name="phone" value="{{ $otpPhone }}">

                <label for="otp">OTP</label>
                <input type="text" name="otp" id="otp" inputmode="numeric" maxlength="6" placeholder="Enter OTP" required>

                <button type="submit" class="admin-login-button">Login</button>
            </form>
        @endif
    </div>
</body>
</html>
