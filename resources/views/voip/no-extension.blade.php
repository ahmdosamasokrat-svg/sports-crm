<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('crm.voip') }} - {{ __('crm.access_denied') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/tajawal.css') }}?v=1.0.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --red: #ef4444;
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #1e293b;
            --muted: #64748b;
            --line: #e2e8f0;
            --font: 'Plus Jakarta Sans', 'Cairo', sans-serif;
        }
        html.dark-mode {
            --bg: #09090b;
            --card: #18181b;
            --text: #f4f4f5;
            --muted: #a1a1aa;
            --line: rgba(255, 255, 255, 0.1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font);
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }
        .notice-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 40px 32px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }
        .notice-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            background: rgba(239, 68, 68, 0.1);
            color: var(--red);
            display: grid;
            place-items: center;
            font-size: 28px;
        }
        .notice-title {
            font-size: 20px;
            font-weight: 800;
        }
        .notice-desc {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }
        .btn-back {
            margin-top: 8px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 12px;
            color: var(--text);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 700;
            transition: 0.15s ease;
        }
        .btn-back:hover {
            border-color: var(--red);
            color: var(--red);
        }
    </style>
</head>
<body>
    <div class="notice-card">
        <div class="notice-icon">
            <i class="bi bi-telephone-x-fill"></i>
        </div>
        <h1 class="notice-title">
            {{ app()->getLocale() === 'en' ? 'No VoIP Extension Assigned' : 'لا توجد تحويلة هاتفية مسجلة' }}
        </h1>
        <p class="notice-desc">
            {{ app()->getLocale() === 'en'
                ? 'Your CRM user account does not have a configured VoIP extension. Please contact your system administrator to assign an extension in Settings.'
                : 'حسابك في النظام لا يحتوي على تحويلة هاتفية مرتبطة (VoIP Extension). يرجى التواصل مع مدير النظام لربط تحويلة هاتفية بحسابك من الإعدادات.' }}
        </p>
        <a href="{{ route('dashboard') }}" class="btn-back">
            <i class="bi bi-arrow-right rtl-flip"></i>
            <span>{{ app()->getLocale() === 'en' ? 'Return to Dashboard' : 'العودة للرئيسية' }}</span>
        </a>
    </div>
</body>
</html>
