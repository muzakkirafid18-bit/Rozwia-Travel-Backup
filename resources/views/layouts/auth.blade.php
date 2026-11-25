<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Autentikasi') - ROZWIA Islamic Travel</title>
    @include('partials.favicon')
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #005c4b;
            --primary-dark: #004437;
            --primary-light: #e6f1ef;
            --gold: #b8860b;
            --gold-light: #fef8eb;
            --bg: #f9fbf9;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --danger: #dc2626;
            --danger-bg: #fef2f2;
            --success: #16a34a;
            --success-bg: #f0fdf4;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .auth-container {
            width: 100%;
            max-width: 440px;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            border-radius: 16px;
            font-size: 26px;
            margin-bottom: 12px;
            box-shadow: 0 8px 16px rgba(0, 92, 75, 0.2);
        }

        .brand-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: 0.5px;
        }

        .brand-subtitle {
            font-size: 13px;
            color: var(--gold);
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .auth-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
            border-top: 5px solid var(--primary);
        }

        .card-header {
            margin-bottom: 20px;
            text-align: center;
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .card-desc {
            font-size: 14px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            font-size: 14px;
            font-family: inherit;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            transition: all 0.2s ease;
            background-color: #fff;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 92, 75, 0.12);
        }

        .form-control.is-invalid {
            border-color: var(--danger);
            background-color: var(--danger-bg);
        }

        .invalid-feedback {
            color: var(--danger);
            font-size: 12px;
            margin-top: 4px;
            display: block;
        }

        .btn-primary {
            width: 100%;
            padding: 12px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            color: #ffffff;
            background-color: var(--primary);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
            line-height: 1.4;
        }

        .alert-danger {
            background-color: var(--danger-bg);
            color: var(--danger);
            border: 1px solid #fecaca;
        }

        .alert-success {
            background-color: var(--success-bg);
            color: var(--success);
            border: 1px solid #bbf7d0;
        }

        .card-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
        }

        .card-footer a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }

        .card-footer a:hover {
            text-decoration: underline;
        }

        /* Demo credentials pill */
        .demo-box {
            margin-top: 20px;
            background-color: #f8fafc;
            border: 1px dashed var(--border);
            border-radius: 8px;
            padding: 12px;
            font-size: 12px;
        }

        .demo-title {
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 6px;
        }

        .demo-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            color: var(--text-muted);
        }

        .demo-badge {
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
            color: #334155;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="brand-header">
            <div class="brand-logo">🕌</div>
            <div class="brand-title">ROZWIA</div>
            <div class="brand-subtitle">Islamic Travel</div>
        </div>

        <div class="auth-card">
            @yield('content')
        </div>

        @yield('demo-credentials')
    </div>
</body>
</html>