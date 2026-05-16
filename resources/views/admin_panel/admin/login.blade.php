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

        <form method="POST" action="{{ route('admin_panel.admin.login.submit') }}">
            @csrf
            <label for="email">Email</label>
            <input type="email" name="email" id="email" placeholder="Enter email" required>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Enter password" required>

            <button type="submit" class="admin-login-button">Login</button>
        </form>
    </div>
</body>
</html>
