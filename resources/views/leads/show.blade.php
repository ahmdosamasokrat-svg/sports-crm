<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $lead->name }} — {{ __('crm.lead_details') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/tajawal.css') }}?v=1.0.0">
    <link rel="stylesheet" href="{{ asset('crm-sidebar-shared.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('crm-notifications.css') }}?v=1.0.0">
    <script>
    (() => {
        try {
            const theme = localStorage.getItem('sokrat.crm.theme');
            if (theme === 'dark') {
                document.documentElement.classList.add('dark-mode');
            }
        } catch (e) {}
    })();
    </script>
    <style>
        *{box-sizing:border-box}
        :root{--red:#ef4444;--red-dark:#dc2626;--dark:#182033;--muted:#7e899b;--line:#e4e8ef;--bg:#f4f6f9;--card:#fff;--blue:#3478f6}
        body{margin:0;background:var(--bg);color:var(--dark);font-family:'Plus Jakarta Sans', 'Cairo', sans-serif !important;}
        .crm-app{min-height:100vh;display:flex}
        .crm-main{min-width:0;flex:1;padding:24px 30px 60px}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:42px;padding:0 16px;border:1px solid var(--line);border-radius:10px;background:#fff;color:var(--dark);font-weight:800;text-decoration:none;cursor:pointer;font-family:inherit;font-size:13px;transition:all .15s}
        .btn.primary{background:var(--red);border-color:var(--red);color:#fff}
        .btn.primary:hover{background:#b81829;border-color:#b81829;color:#fff}
        .btn.soft{background:var(--card);color:var(--dark)}
        .btn.soft:hover{background:var(--bg);border-color:var(--dark)}
        .btn.small{min-height:38px;padding:0 12px;font-size:12px}
        .btn.success{background:#16a34a;border-color:#16a34a;color:#fff}
        .btn.success:hover{background:#15803d;border-color:#15803d;color:#fff}
        .badge{display:inline-flex;padding:4px 9px;border-radius:999px;font-size:11px;font-weight:900;background:#eef2f7;color:#536078}
        .badge.active{background:#e7f8ed;color:#18733a}
        .badge.inactive{background:#fff0f1;color:#b42332}
        .badge.system{background:#fff5d9;color:#8a6100}

        /* Body Modal Shell & Centered Dialog */
        body.modal-open { overflow: hidden !important; }
        .crm-body-modal-shell {
            position: fixed !important;
            inset: 0 !important;
            background: rgba(15, 23, 42, 0.65) !important;
            backdrop-filter: blur(4px) !important;
            z-index: 999999 !important;
            display: none;
            align-items: center !important;
            justify-content: center !important;
            padding: 18px !important;
            box-sizing: border-box !important;
        }
        .crm-body-modal-shell.is-open {
            display: flex !important;
        }
        .crm-body-modal-dialog {
            background: var(--card, #ffffff) !important;
            color: var(--dark, #182033) !important;
            border: 1px solid var(--line, #e2e8f0) !important;
            border-radius: 16px !important;
            max-width: 500px !important;
            width: 100% !important;
            padding: 24px !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35) !important;
            max-height: calc(100dvh - 36px) !important;
            overflow-y: auto !important;
            position: relative !important;
            margin: auto !important;
            z-index: 1000000 !important;
        }
        html.dark-mode .crm-body-modal-dialog {
            background: #182033 !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            color: #f1f5f9 !important;
        }

        /* Lead Profile Header Card */
        .lead-header-card{background:transparent !important;border:0 !important;border-radius:16px;padding:12px 0 20px;margin-bottom:12px;box-shadow:none !important}
        .lead-header-top{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
        .lead-identity{display:flex;align-items:center;gap:16px}
        .lead-avatar{width:56px;height:56px;border-radius:16px;background:#fef2f2;color:var(--red);display:grid;place-items:center;font-size:24px;font-weight:900}
        .lead-names h2{margin:0;font-size:20px;font-weight:900;color:var(--dark)}
        .lead-names p{margin:4px 0 0;color:var(--muted);font-size:13px;display:flex;align-items:center;gap:8px;flex-wrap:wrap}

        /* Enhanced Lead Stage Indicator Card */
        .lead-stage-card {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 8px 18px 8px 10px;
            background: color-mix(in srgb, var(--stage-color, #3478f6) 8%, transparent);
            border: 1px solid color-mix(in srgb, var(--stage-color, #3478f6) 28%, var(--line, #e2e8f0));
            border-radius: 999px;
            box-shadow: 0 4px 16px color-mix(in srgb, var(--stage-color, #3478f6) 12%, transparent);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .lead-stage-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px color-mix(in srgb, var(--stage-color, #3478f6) 22%, transparent);
            border-color: var(--stage-color, #3478f6);
        }
        .stage-icon-halo {
            position: relative;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--stage-color, #3478f6), color-mix(in srgb, var(--stage-color, #3478f6) 80%, #000));
            color: #ffffff;
            display: grid;
            place-items: center;
            font-size: 15px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px color-mix(in srgb, var(--stage-color, #3478f6) 45%, transparent);
        }
        .stage-pulse {
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            border: 2px solid var(--stage-color, #3478f6);
            opacity: 0.4;
            animation: stagePulseAnim 2.4s infinite ease-out;
        }
        @keyframes stagePulseAnim {
            0% { transform: scale(0.92); opacity: 0.6; }
            50% { transform: scale(1.18); opacity: 0.08; }
            100% { transform: scale(0.92); opacity: 0.6; }
        }
        .stage-text-group {
            display: flex;
            flex-direction: column;
            line-height: 1.25;
            text-align: start;
        }
        .stage-caption {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--muted, #64748b);
        }
        .stage-title {
            font-size: 14px;
            font-weight: 900;
            color: var(--dark, #0f172a);
        }
        html.dark-mode .lead-stage-card {
            background: color-mix(in srgb, var(--stage-color, #3478f6) 14%, rgba(255,255,255,0.02));
            border-color: color-mix(in srgb, var(--stage-color, #3478f6) 38%, rgba(255,255,255,0.12));
            box-shadow: 0 4px 18px rgba(0,0,0,0.35);
        }
        html.dark-mode .stage-title { color: #f8fafc; }
        html.dark-mode .stage-caption { color: #94a3b8; }

        .metrics-bar{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-top:20px;padding-top:20px;border-top:1px solid var(--line)}
        .metric-box{background:var(--bg);border:1px solid var(--line);border-radius:12px;padding:12px 14px}
        .metric-box span{display:block;color:var(--muted);font-size:11px;font-weight:800;margin-bottom:4px}
        .metric-box b{display:block;font-size:15px;font-weight:900;color:var(--dark)}

        /* Two Columns Layout */
        .details-grid{display:grid;grid-template-columns:1fr 1.2fr;gap:20px}
        .panel{background:transparent !important;border:0 !important;border-radius:16px;padding:16px 0;margin-bottom:20px;box-shadow:none !important}
        .panel-head{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:16px;background:transparent !important;border:0 !important}
        .panel-head h2{margin:0;font-size:16px;font-weight:900;color:var(--dark);display:flex;align-items:center;gap:8px}

        .info-list{display:grid;gap:10px}
        .info-row{display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:var(--bg);border:1px solid var(--line);border-radius:10px;font-size:13px}
        .info-row.is-primary-phone{background:rgba(22,163,74,0.08);border:1px solid rgba(22,163,74,0.25)}
        .info-label{color:var(--muted);font-weight:700}
        .info-value{font-weight:800;color:var(--dark);text-align:end}

        /* Timeline Styles */
        .timeline{position:relative;padding-inline-start:24px;margin-top:10px}
        .timeline::before{
            content:'';position:absolute;top:0;bottom:0;inset-inline-start:7px;
            width:2px;background:var(--line);
        }
        .timeline-item{position:relative;margin-bottom:20px}
        .timeline-dot{
            position:absolute;inset-inline-start:-24px;top:4px;
            width:16px;height:16px;border-radius:50%;background:var(--red);border:3px solid #fff;
            box-shadow:0 0 0 2px var(--line);
        }
        .timeline-card{
            background:var(--bg);border:1px solid var(--line);border-radius:12px;padding:14px 16px;
        }
        .timeline-head{
            display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:8px;flex-wrap:wrap;
        }
        .timeline-date{color:var(--muted);font-size:12px;font-weight:700}
        .timeline-employee{font-weight:800;color:var(--dark);font-size:13px}
        .timeline-body{color:#334155;font-size:13px;line-height:1.6}

        /* VoIP Call Insights Styles */
        .call-filters{display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;margin-bottom:16px}
        .call-filters > div{flex:1 1 140px;min-width:130px}
        .call-filters button{flex:0 0 auto;height:40px;padding:0 16px;white-space:nowrap}
        .call-filters label{display:block;color:var(--muted);font-size:11px;font-weight:800;margin-bottom:5px}
        .call-filters input,.call-filters select{width:100%;height:40px;border:1px solid var(--line);border-radius:9px;padding:8px 10px;background:var(--card);color:var(--dark);font-family:inherit;box-sizing:border-box}
        .call-metrics{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:10px;margin-bottom:16px}
        .call-metric{padding:12px;background:var(--bg);border-radius:12px;border:1px solid var(--line)}
        .call-metric span{display:block;color:var(--muted);font-size:11px;font-weight:800}
        .call-metric strong{display:block;margin-top:5px;font-size:18px;font-variant-numeric:tabular-nums}
        .call-bars{display:grid;gap:9px;padding:14px;background:var(--bg);border:1px solid var(--line);border-radius:12px;margin-bottom:16px}
        .call-bar{display:grid;grid-template-columns:78px 1fr 34px;gap:8px;align-items:center;font-size:12px;font-weight:800}
        .call-bar-track{height:7px;background:var(--line);border-radius:999px;overflow:hidden}
        .call-bar-fill{display:block;height:100%;border-radius:inherit}
        .call-list{display:grid;gap:9px}
        .call-row{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;border:1px solid var(--line);border-radius:12px;background:var(--card);transition:border-color .15s}
        .call-row > div:nth-child(1){flex:1 1 140px}
        .call-row > div:nth-child(2){flex:1 1 140px}
        .call-row > div:nth-child(3){flex:0 0 80px;text-align:center}
        .call-row > div:nth-child(4){flex:0 0 auto;text-align:end}
        .call-row small{display:block;color:var(--muted);margin-top:3px}
        .call-state{text-align:center;padding:26px;color:var(--muted);font-weight:700}
        .call-state i{display:block;font-size:26px;margin-bottom:7px}
        .crm-audio-player{grid-column:1/-1;display:flex;align-items:center;gap:10px;padding:8px 12px;background:var(--bg);border:1px solid var(--line);border-radius:12px;margin-top:4px;transition:border-color .15s}
        .crm-audio-player.is-playing{border-color:var(--red)}
        .audio-play-btn{width:32px;height:32px;flex:0 0 32px;border-radius:50%;background:var(--red);color:#fff;border:0;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;font-size:15px;transition:transform .15s,background .15s;padding:0}
        .audio-play-btn:hover{background:#b81829;transform:scale(1.06)}
        .audio-track-wrap{flex:1;min-width:0;display:flex;flex-direction:column;gap:3px}
        .audio-seek{width:100%;height:6px;-webkit-appearance:none;appearance:none;background:var(--line);border-radius:999px;outline:none;cursor:pointer;margin:0}
        .audio-seek::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;width:12px;height:12px;border-radius:50%;background:var(--red);cursor:pointer;transition:transform .1s}
        .audio-seek::-webkit-slider-thumb:hover{transform:scale(1.25)}
        .audio-seek::-moz-range-thumb{width:12px;height:12px;border-radius:50%;background:var(--red);border:0;cursor:pointer}
        .audio-time-row{display:flex;justify-content:space-between;font-size:10px;font-weight:700;color:var(--muted);font-variant-numeric:tabular-nums;font-family:monospace}
        .audio-speed-btn{min-width:32px;height:26px;padding:0 5px;border-radius:6px;border:1px solid var(--line);background:var(--card);color:var(--dark);font-size:11px;font-weight:800;cursor:pointer;transition:all .15s}
        .audio-speed-btn:hover{border-color:var(--red);color:var(--red)}
        .audio-download-btn{width:26px;height:26px;flex:0 0 26px;border-radius:6px;border:1px solid var(--line);background:var(--card);color:var(--muted);display:inline-flex;align-items:center;justify-content:center;text-decoration:none;font-size:12px;transition:all .15s}
        .audio-download-btn:hover{border-color:var(--dark);color:var(--dark)}
        .call-pagination{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 4px 4px;margin-top:6px;border-top:1px solid var(--line);flex-wrap:wrap}
        .call-pagination-info{font-size:12px;font-weight:700;color:var(--muted)}
        .call-pagination-controls{display:flex;align-items:center;gap:6px}
        .btn-call-page{min-width:30px;height:30px;padding:0 8px;border-radius:8px;border:1px solid var(--line);background:var(--card);color:var(--dark);font-size:12px;font-weight:800;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:all .15s}
        .btn-call-page:hover:not(:disabled){background:var(--bg);border-color:var(--dark)}
        .btn-call-page.active{background:var(--red)!important;border-color:var(--red)!important;color:#fff!important}
        .btn-call-page:disabled{opacity:0.35;cursor:not-allowed}

        /* Field changes & stage answers */
        .followup-field-changes{margin-top:10px;padding:10px 12px;border:1px solid var(--line);border-radius:10px;background:var(--card)}
        .followup-field-changes strong{display:block;margin-bottom:8px;font-size:12px;color:var(--dark)}
        .followup-change-list{display:grid;gap:6px;margin:0;padding:0;list-style:none}
        .followup-change-item{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:6px 8px;border-radius:6px;background:var(--bg);font-size:12px}
        .followup-change-label{color:var(--muted);font-weight:700}
        .followup-change-old{color:#b42332;text-decoration:line-through}
        .followup-change-new{color:#15803d;font-weight:800}

        html.dark-mode {
            --bg: #09090b;
            --card: #18181b;
            --dark: #f4f4f5;
            --muted: #a1a1aa;
            --line: rgba(255,255,255,0.08);
        }
        html.dark-mode body { background: #09090b !important; color: #f4f4f5 !important; }
        html.dark-mode .info-row { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08); }
        html.dark-mode .info-row.is-primary-phone { background: rgba(34,197,94,0.12) !important; border-color: rgba(34,197,94,0.3) !important; }
        html.dark-mode .info-row strong, html.dark-mode .info-value { color: #f4f4f5 !important; }
        html.dark-mode .metric-box { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08); }
        html.dark-mode .timeline-card { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08); }
        html.dark-mode .timeline-body { color: #d4d4d8; }
        html.dark-mode .btn.soft { background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.1); color: #f4f4f5; }
        html.dark-mode .btn.soft:hover { background: rgba(255,255,255,0.12); color: #fff; }
        html.dark-mode .badge { background: rgba(255,255,255,0.08); color: #d4d4d8; }
        html.dark-mode .badge.active { background: rgba(34,197,94,0.18); color: #4ade80; }
        html.dark-mode .call-metric { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); }
        html.dark-mode .call-bars { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); }
        html.dark-mode .call-filters input, html.dark-mode .call-filters select { background: #18181b; border-color: rgba(255,255,255,0.12); color: #f4f4f5; }
        html.dark-mode .call-row { background: #18181b; border-color: rgba(255,255,255,0.08); }
        html.dark-mode .crm-audio-player { background: #27272a; border-color: rgba(255,255,255,0.1); }
        html.dark-mode .audio-speed-btn, html.dark-mode .audio-download-btn, html.dark-mode .btn-call-page { background: #18181b; border-color: rgba(255,255,255,0.12); color: #f4f4f5; }
        html.dark-mode .audio-seek { background: rgba(255,255,255,0.12); }
        html.dark-mode .followup-field-changes { background: #18181b; border-color: rgba(255,255,255,0.08); }
        html.dark-mode .followup-change-item { background: rgba(255,255,255,0.03); }

        /* Stage Details Panel */
        .stage-selector-field-group {
            background: var(--bg);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 14px;
        }
        .stage-selector-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 8px;
        }
        .stage-select-wrap {
            position: relative;
        }
        .stage-select-input {
            width: 100%;
            height: 44px;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 0 14px;
            padding-inline-end: 38px;
            background: var(--card);
            color: var(--dark);
            font-weight: 800;
            font-size: 13px;
            cursor: pointer;
            font-family: inherit;
            outline: none;
            appearance: none;
            -webkit-appearance: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .stage-select-input:focus {
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12);
        }
        .stage-select-icon {
            position: absolute;
            inset-inline-end: 14px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: var(--muted);
            font-size: 13px;
        }
        .stage-pane-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            background: var(--bg);
            border: 1px solid var(--line);
            border-radius: 10px;
            margin-bottom: 12px;
            gap: 10px;
            flex-wrap: wrap;
        }
        .stage-pane-name {
            font-size: 14px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .stage-empty-box {
            text-align: center;
            padding: 28px 16px;
            background: var(--bg);
            border: 1px dashed var(--line);
            border-radius: 12px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }
        .stage-empty-box i {
            display: block;
            font-size: 28px;
            margin-bottom: 6px;
            opacity: 0.6;
        }
        .stage-history-subpanel {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px dashed var(--line);
        }
        .stage-history-subhead {
            font-size: 12px;
            font-weight: 800;
            color: var(--muted);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .stage-history-list {
            display: grid;
            gap: 8px;
        }
        .stage-history-card {
            padding: 10px 12px;
            background: var(--bg);
            border: 1px solid var(--line);
            border-radius: 10px;
            font-size: 12px;
        }
        .stage-history-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
        }
        .stage-history-values {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .stage-history-pill {
            display: inline-flex;
            gap: 4px;
            background: var(--card);
            border: 1px solid var(--line);
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
        }

        /* Dark mode stage details */
        html.dark-mode .stage-selector-field-group {
            background: rgba(255,255,255,0.02);
            border-color: rgba(255,255,255,0.08);
        }
        html.dark-mode .stage-select-input {
            background: #18181b;
            border-color: rgba(255,255,255,0.12);
            color: #f4f4f5;
        }
        html.dark-mode .stage-pane-header {
            background: rgba(255,255,255,0.03);
            border-color: rgba(255,255,255,0.08);
        }
        html.dark-mode .stage-empty-box {
            background: rgba(255,255,255,0.02);
            border-color: rgba(255,255,255,0.08);
        }
        html.dark-mode .stage-history-card {
            background: rgba(255,255,255,0.03);
            border-color: rgba(255,255,255,0.08);
        }
        html.dark-mode .stage-history-pill {
            background: #18181b;
            border-color: rgba(255,255,255,0.08);
            color: #e4e4e7;
        }

        @media(max-width:1024px){
            .details-grid{grid-template-columns:1fr}
            .metrics-bar{grid-template-columns:repeat(2,1fr)}
        }
        @media(max-width:768px){
            .crm-main{padding:16px 12px 60px;min-width:0;width:100%;max-width:100%}
            .top-actions{width:100%;flex-wrap:wrap;gap:8px}
            .top-actions .btn{flex:1 1 auto;min-height:44px}
            .lead-header-top{flex-direction:column;align-items:stretch;gap:14px}
            .lead-stage-card{align-self:flex-start}
            .metrics-bar{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
            .call-filters{grid-template-columns:1fr}
            .call-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}
            .call-row{grid-template-columns:1fr;gap:8px}
            .info-row{flex-wrap:wrap;gap:8px}
            .info-row .btn.small{min-height:44px;min-width:44px;height:44px;padding:0 12px}
        }
        @media(max-width:480px){
            .metrics-bar{grid-template-columns:1fr}
            .call-metrics{grid-template-columns:1fr}
        }
        /* Kanban popup embedded mode */
        body.kanban-followup-popup {
            background: transparent !important;
        }
        body.kanban-followup-popup .crm-app {
            min-height: auto;
            display: block;
        }
        body.kanban-followup-popup .crm-main {
            padding: 14px;
        }
        body.kanban-followup-popup .crm-topbar,
        body.kanban-followup-popup .topbar,
        body.kanban-followup-popup .crm-side {
            display: none !important;
        }
    </style>
</head>
<body class="{{ request()->boolean('kanban_popup') ? 'kanban-followup-popup' : '' }}">
<div class="crm-app lead-detail-page">
    @include('partials.crm-sidebar')

    <main class="crm-main">
        @php
            $showTopActions = '';
            if (auth()->user()?->can('leads.update')) {
                $showTopActions .= '<a href="' . route('v2.leads.edit', $lead) . '" class="btn soft"><i class="bi bi-pencil-square"></i> ' . __('crm.edit_data') . '</a>';
            }
            if (auth()->user()?->can('leads.followups.view')) {
                if ($callPhone) {
                    $showTopActions .= '<a class="btn primary js-call-followup" href="' . route('v2.leads.followups.index', ['lead' => $lead, 'channel' => 'call']) . '" data-call-href="tel:' . $callPhone . '" title="' . __('crm.open_microsip_followup') . '"><i class="bi bi-telephone-outbound"></i> ' . __('crm.call_action') . '</a>';
                }
                $showTopActions .= '<a href="' . route('v2.leads.followups.index', $lead) . '" class="btn soft" title="' . __('crm.log_new_followup') . '"><i class="bi bi-plus-lg"></i> ' . __('crm.log_new_followup') . '</a>';
            } else {
                if ($callPhone) {
                    $showTopActions .= '<a class="btn primary" href="tel:' . $callPhone . '" title="' . __('crm.call_action') . '"><i class="bi bi-telephone-outbound"></i> ' . __('crm.call_action') . '</a>';
                }
            }
            if (auth()->user()?->can('leads.create')) {
                $showTopActions .= '<button type="button" class="btn soft" onclick="openReferralModal()" title="إحالة عميل جديد من خلال هذا المشترك" style="color:#059669; border-color:#a7f3d0; background:#ecfdf5;"><i class="bi bi-person-plus"></i> إحالة صديق</button>';
            }
        @endphp

        @include('partials.topbar', [
           'title' => __('crm.lead_details'),
           'subtitle' => '<span>' . __('crm.lead_code_label') . ': #' . $lead->id . '</span> <span style="margin:0 6px">•</span> <span>' . __('crm.registered_at') . ': ' . ($lead->created_at?->format('Y-m-d') ?? '—') . '</span>',
           'icon' => 'bi-person-badge-fill',
           'backUrl' => route('v2.leads', $backQuery),
           'backTitle' => __('crm.back_to_leads_list'),
           'actions' => $showTopActions,
       ])

        @if (session('success'))
            <div class="alert alert-success" style="margin-bottom:16px;padding:12px 16px;border-radius:12px;background:#ecfdf5;border:1px solid #6ee7b7;color:#065f46;display:flex;align-items:center;gap:8px;font-weight:700;">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif

       <!-- PROFILE HEADER CARD -->
       <section class="lead-header-card">
           <div class="lead-header-top">
               <div class="lead-identity">
                   <div class="lead-avatar">
                       <i class="bi bi-person"></i>
                   </div>
                    <div class="lead-names">
                        <h2>{{ $lead->name }}</h2>
                        <p>
                            @if ($lead->branch)
                                <span class="badge" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;font-size:12px;font-weight:700">
                                    <i class="bi bi-geo-alt-fill"></i> {{ $lead->branch->localizedName() }}
                                </span>
                                <span>•</span>
                            @endif
                            @if ($lead->company_name)
                               <span><i class="bi bi-building"></i> {{ $lead->company_name }}</span>
                               <span>•</span>
                           @endif
                           @if ($lead->governorate || $lead->address)
                               <span><i class="bi bi-geo-alt"></i> {{ $lead->governorate ?: $lead->address }}</span>
                               <span>•</span>
                           @endif
                           <span><i class="bi bi-person-badge"></i> {{ $lead->assignedUser?->name ?? $lead->assigned_employee ?? __('crm.unassigned') }}</span>
                            @php
                                $topTempVal = $customerFieldValues['lead_temperature'] ?? null;
                                $topTempBadgeStyle = match($topTempVal) {
                                    'hot' => 'background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;',
                                    'warm' => 'background:#fef3c7;color:#d97706;border:1px solid #fcd34d;',
                                    'cold' => 'background:#e0f2fe;color:#0284c7;border:1px solid #7dd3fc;',
                                    default => 'background:var(--bg);color:var(--muted);border:1px dashed var(--line);'
                                };
                                $topTempLabel = match($topTempVal) {
                                    'hot' => '🔥 ' . (app()->getLocale() === 'en' ? 'Hot' : 'حار (Hot)'),
                                    'warm' => '⚡ ' . (app()->getLocale() === 'en' ? 'Warm' : 'متوسط (Warm)'),
                                    'cold' => '❄️ ' . (app()->getLocale() === 'en' ? 'Cold' : 'بارد (Cold)'),
                                    default => '🔘 ' . (app()->getLocale() === 'en' ? 'Temp: Not Set' : 'حرارة العميل: غير محدد')
                                };
                            @endphp
                            <span>•</span>
                            <span class="badge" style="padding:2px 8px;border-radius:6px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:4px;{{ $topTempBadgeStyle }}">
                                <i class="bi bi-thermometer-half"></i> {{ $topTempLabel }}
                            </span>
                       </p>
                   </div>
               </div>

                   {{-- Cloned / Parent Lead Linking Banner --}}
                   @if ($lead->parentLead || $lead->clonedLeads->isNotEmpty())
                       <div style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                           @if ($lead->parentLead)
                               <span class="badge" style="background: rgba(79, 70, 229, 0.1); color: #4f46e5; border: 1px solid rgba(79, 70, 229, 0.3); font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                                   <i class="bi bi-diagram-3-fill"></i>
                                   {{ __('متفرع من العميل الأصلي:') }}
                                   <a href="{{ route('v2.leads.show', $lead->parentLead) }}" style="color: #4f46e5; text-decoration: underline; font-weight: 800;">
                                       #{{ $lead->parentLead->id }} {{ $lead->parentLead->name }} ({{ $lead->parentLead->status?->stage?->category?->localizedName() ?? $lead->parentLead->status?->stage?->localizedName() }})
                                   </a>
                               </span>
                           @endif
                           @if ($lead->clonedLeads->isNotEmpty())
                               @foreach ($lead->clonedLeads as $cLead)
                                   <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                                       <i class="bi bi-arrow-right-circle-fill"></i>
                                       {{ __('تم نسخه لمسار آخر:') }}
                                       <a href="{{ route('v2.leads.show', $cLead) }}" style="color: #059669; text-decoration: underline; font-weight: 800;">
                                           #{{ $cLead->id }} ({{ $cLead->status?->stage?->category?->localizedName() ?? $cLead->status?->stage?->localizedName() }})
                                       </a>
                                   </span>
                               @endforeach
                           @endif
                       </div>
                   @endif
                <div>
                    @php
                        $stageCode = strtolower((string) ($lead->status?->code ?? $lead->status?->stage?->code ?? ''));
                        $stageIcon = match(true) {
                            str_contains($stageCode, 'won') || str_contains($stageCode, 'closed') || str_contains($stageCode, 'contract') || str_contains($stageCode, 'donor') => 'bi-check-circle-fill',
                            str_contains($stageCode, 'not') || str_contains($stageCode, 'reject') || str_contains($stageCode, 'lost') || str_contains($stageCode, 'disinterest') => 'bi-slash-circle-fill',
                            str_contains($stageCode, 'follow') || str_contains($stageCode, 'no_answer') || str_contains($stageCode, 'negotiation') => 'bi-arrow-repeat',
                            str_contains($stageCode, 'new') => 'bi-stars',
                            str_contains($stageCode, 'quotation') || str_contains($stageCode, 'proposal') => 'bi-file-earmark-text-fill',
                            default => 'bi-diagram-3-fill',
                        };
                        $stageName = $lead->status?->localizedName() ?? ($lead->status?->stage?->localizedName() ?? ($lead->status?->name_ar ?? __('crm.no_status')));
                    @endphp
                    <div class="lead-stage-card" style="--stage-color: {{ $statusColor }};">
                        <div class="stage-icon-halo">
                            <span class="stage-pulse"></span>
                            <i class="bi {{ $stageIcon }}"></i>
                        </div>
                        <div class="stage-text-group">
                            <span class="stage-caption">{{ __('crm.current_stage') }}</span>
                            <span class="stage-title">{{ $stageName }}</span>
                        </div>
                    </div>
                </div>
            </div>

           <div class="metrics-bar">
               <div class="metric-box">
                   <span>{{ __('crm.company_name') }}</span>
                   <b>{{ $lead->company_name ?: '—' }}</b>
               </div>
               <div class="metric-box">
                    <span><i class="bi bi-thermometer-half"></i> {{ app()->getLocale() === 'en' ? 'Lead Temperature' : 'حرارة العميل' }}</span>
                    <b>
                        @if ($topTempVal === 'hot')
                            <span style="color:#dc2626">🔥 {{ app()->getLocale() === 'en' ? 'Hot' : 'حار (Hot)' }}</span>
                        @elseif ($topTempVal === 'warm')
                            <span style="color:#d97706">⚡ {{ app()->getLocale() === 'en' ? 'Warm' : 'متوسط (Warm)' }}</span>
                        @elseif ($topTempVal === 'cold')
                            <span style="color:#0284c7">❄️ {{ app()->getLocale() === 'en' ? 'Cold' : 'بارد (Cold)' }}</span>
                        @else
                            <span style="color:var(--muted);font-weight:600;">🔘 {{ app()->getLocale() === 'en' ? 'Not Set' : 'غير محدد' }}</span>
                        @endif
                    </b>
               </div>
               <div class="metric-box">
                   <span>{{ __('crm.quotation_status') }}</span>
                    <b style="color:{{ $lead->quotation_sent ? '#16a34a' : ($hasQuotationFile ? '#2563eb' : 'inherit') }}">
                        {{ $lead->quotation_sent ? 'تم الإرسال للعميل' : ($hasQuotationFile ? 'جاهز للإرسال' : 'لم يتم الإنشاء') }}
                    </b>
                </div>
                <div class="metric-box">
                    <span>{{ __('crm.next_followup') }}</span>
                    <b>
                        @if ($lead->next_follow_up_at)
                            {{ $lead->next_follow_up_at->format('Y-m-d h:i A') }}
                        @else
                            {{ __('crm.no_followup_scheduled') }}
                        @endif
                    </b>
                </div>
                <div class="metric-box">
                    <span>محاولات الاتصال</span>
                    <b>
                        @if ($lead->call_attempts_count > 0)
                            <span style="color:#d97706"><i class="bi bi-telephone"></i> {{ $lead->call_attempts_count }} محاولة</span>
                            @if ($lead->last_call_status)
                                <small style="display:block; font-size:10px; color:var(--muted); font-weight:600;">{{ $lead->last_call_status }}</small>
                            @endif
                        @else
                            <span style="color:var(--muted); font-weight:600;">لم يتم الاتصال</span>
                        @endif
                    </b>
                </div>
                <div class="metric-box">
                    <span>آخر تواصل</span>
                    <b>
                        @if ($lead->last_contacted_at)
                            {{ $lead->last_contacted_at->format('Y-m-d h:i A') }}
                        @else
                            <span style="color:var(--muted); font-weight:600;">—</span>
                        @endif
                    </b>
                </div>
            </div>
        </section>

        <div class="details-grid">
            <!-- LEFT COLUMN: PROFILE, CONTACTS & RECORDS -->
            <div>
                <!-- CUSTOMER CORE INFO -->
                <section class="panel">
                    <div class="panel-head">
                        <h2><i class="bi bi-info-circle"></i> {{ __('crm.customer_data') }}</h2>
                    </div>
                    <div class="info-list">
                        <div class="info-row">
                            <span class="info-label">{{ __('crm.full_name') }}</span>
                            <span class="info-value">{{ $lead->name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">{{ __('crm.company_name') }}</span>
                            <span class="info-value">{{ $lead->company_name ?: '—' }}</span>
                        </div>
                        @if ($lead->job_title)
                            <div class="info-row">
                                <span class="info-label">{{ __('crm.job_title') }}</span>
                                <span class="info-value">{{ $lead->job_title }}</span>
                            </div>
                        @endif
                        @if ($lead->activity)
                            <div class="info-row">
                                <span class="info-label">{{ __('crm.activity') }}</span>
                                <span class="info-value">{{ $lead->activity }}</span>
                            </div>
                        @endif
                        <div class="info-row">
                            <span class="info-label">{{ __('crm.birth_date') ?? 'تاريخ الميلاد' }}</span>
                            <span class="info-value">
                                @if ($lead->birth_date)
                                    {{ $lead->birth_date->format('Y-m-d') }}
                                    <span class="badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-weight:700;margin-inline-start:6px;font-size:12px;">
                                        <i class="bi bi-cake2"></i> {{ $lead->age }} {{ __('سنة') }}
                                    </span>
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">{{ __('crm.age') ?? 'العمر (محسوب تلقائيًا)' }}</span>
                            <span class="info-value">
                                @if ($lead->age !== null)
                                    <strong style="color:var(--dark); font-size:14px;">{{ $lead->age }}</strong> {{ __('سنوات') }}
                                @else
                                    <span style="color:var(--muted)">{{ __('غير محدد (يتطلب تاريخ الميلاد)') }}</span>
                                @endif
                            </span>
                        </div>
                        <!-- GUARDIAN & SIBLINGS (DYNAMIC INTERFACE) -->
                        <div class="info-row" style="flex-direction: column; align-items: stretch; gap: 8px; background: rgba(248, 250, 252, 0.6); border: 1px solid var(--line); border-radius: 10px; padding: 12px; margin: 6px 0;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span class="info-label" style="font-weight:800; color:var(--dark); display:flex; align-items:center; gap:6px;">
                                    <i class="bi bi-people-fill" style="color:#4f46e5;"></i> {{ __('ولي الأمر (Guardian)') }}
                                </span>
                                <div id="guardianActionBtns">
                                    @if ($lead->guardian)
                                        <button type="button" class="btn small soft" onclick="unlinkGuardian()" style="color:#dc2626; padding:3px 8px; font-size:11px;" title="{{ __('فك ارتباط ولي الأمر') }}">
                                            <i class="bi bi-x-circle"></i> {{ __('إلغاء الربط') }}
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- CURRENT LINKED GUARDIAN BADGE -->
                            <div id="linkedGuardianDisplay" style="{{ $lead->guardian ? 'display:block;' : 'display:none;' }}">
                                @if ($lead->guardian)
                                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                        <span class="badge" style="background:#eef2ff; color:#4338ca; border:1px solid #c7d2fe; font-size:13px; font-weight:700; padding:6px 12px; border-radius:8px; display:inline-flex; align-items:center; gap:6px;">
                                            <i class="bi bi-person-check-fill"></i>
                                            <span id="guardianDisplayName">{{ $lead->guardian->name }}</span>
                                            <small style="color:#6366f1; font-weight:600;">(#ID {{ $lead->guardian->id }})</small>
                                            @if ($lead->guardian->relationship)
                                                <span style="opacity:0.7;">• {{ $lead->guardian->relationship }}</span>
                                            @endif
                                            @if ($lead->guardian->phone)
                                                <span style="direction:ltr; font-family:var(--font-mono); font-size:12px;">• {{ $lead->guardian->phone }}</span>
                                            @endif
                                        </span>
                                    </div>
                                    @if ($lead->guardian->notes)
                                        <div id="guardianNotesWrap" style="display:flex; align-items:flex-start; gap:6px; margin-top:6px; font-size:12px; color:var(--dark); background:#fff; border:1px solid var(--line); border-radius:8px; padding:6px 10px;">
                                            <i class="bi bi-card-text" style="color:#4f46e5; margin-top:2px;"></i>
                                            <div>
                                                <strong style="color:var(--muted); font-size:11px; display:block;">{{ __('ملاحظات الأسرة:') }}</strong>
                                                <span id="guardianNotesText">{{ $lead->guardian->notes }}</span>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <!-- GUARDIAN SEARCH & ADD INTERFACE -->
                            <div id="unlinkedGuardianControls" style="{{ $lead->guardian ? 'display:none;' : 'display:block;' }}">
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <div style="position:relative; flex:1;">
                                        <input type="text" id="guardianSearchInput" placeholder="🔍 ابحث عن ولي أمر بالاسم أو الهاتف..." 
                                               style="width:100%; height:36px; padding:0 10px; font-size:12.5px; border:1px solid var(--line); border-radius:8px; background:var(--card); color:var(--dark);" autocomplete="off">
                                        <div id="guardianSearchResults" style="display:none; position:absolute; top:calc(100% + 4px); inset-inline-start:0; width:100%; max-height:200px; overflow-y:auto; background:var(--card); border:1px solid var(--line); border-radius:8px; z-index:120; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1);"></div>
                                    </div>
                                    <button type="button" class="btn small primary" onclick="openCreateGuardianModal()" style="height:36px; font-size:12px; white-space:nowrap; padding:0 12px; display:inline-flex; align-items:center; gap:4px;">
                                        <i class="bi bi-plus-lg"></i> {{ __('ولي أمر جديد') }}
                                    </button>
                                </div>
                            </div>

                            <!-- SIBLINGS SECTION (DYNAMIC RADAR) -->
                            <div id="guardianSiblingsWrap" style="{{ ($lead->guardian && $lead->siblings->isNotEmpty()) ? 'display:block;' : 'display:none;' }} margin-top:6px; border-top:1px dashed var(--line); padding-top:8px;">
                                <span style="font-size:11px; font-weight:700; color:var(--muted); display:block; margin-bottom:4px;">
                                    <i class="bi bi-diagram-2"></i> {{ __('الأشقاء المسجلون لنفس ولي الأمر:') }}
                                </span>
                                <div id="siblingsList" style="display:flex; flex-wrap:wrap; gap:6px;">
                                    @if ($lead->guardian)
                                        @foreach ($lead->siblings as $sibling)
                                            <a href="{{ route('v2.leads.show', $sibling) }}" class="badge" style="background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; font-size:11.5px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:6px;">
                                                <i class="bi bi-person"></i> #{{ $sibling->id }} {{ $sibling->name }}
                                                <small style="color:#64748b;">({{ $sibling->status?->stage?->localizedName() ?? '—' }})</small>
                                            </a>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- REFERRALS & REFERRER NETWORK SECTION -->
                        @if ($lead->referredBy || $lead->referrals->isNotEmpty())
                            <div class="info-row" style="flex-direction: column; align-items: stretch; gap: 8px; background: rgba(236, 253, 245, 0.5); border: 1px solid #a7f3d0; border-radius: 10px; padding: 12px; margin: 6px 0;">
                                @if ($lead->referredBy)
                                    <div>
                                        <span class="info-label" style="font-weight:800; color:#065f46; display:flex; align-items:center; gap:6px; font-size:12px;">
                                            <i class="bi bi-gift-fill"></i> مُحال من المشترك:
                                        </span>
                                        <a href="{{ route('v2.leads.show', $lead->referredBy) }}" style="display:inline-flex; align-items:center; gap:6px; margin-top:4px; font-weight:700; color:#047857; text-decoration:none; background:#fff; padding:4px 10px; border-radius:6px; border:1px solid #6ee7b7; font-size:12px;">
                                            <i class="bi bi-person-heart"></i> #{{ $lead->referredBy->id }} {{ $lead->referredBy->name }}
                                        </a>
                                    </div>
                                @endif

                                @if ($lead->referrals->isNotEmpty())
                                    <div style="{{ $lead->referredBy ? 'margin-top:6px; border-top:1px dashed #a7f3d0; padding-top:6px;' : '' }}">
                                        <span class="info-label" style="font-weight:800; color:#065f46; display:flex; align-items:center; gap:6px; font-size:12px; margin-bottom:6px;">
                                            <i class="bi bi-people"></i> إحالات قام بها هذا المشترك ({{ $lead->referrals->count() }}):
                                        </span>
                                        <div style="display:flex; flex-wrap:wrap; gap:6px;" id="referralsListContainer">
                                            @foreach ($lead->referrals as $refLead)
                                                <a href="{{ route('v2.leads.show', $refLead) }}" class="badge" style="background:#fff; color:#065f46; border:1px solid #6ee7b7; font-size:11.5px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px; padding:4px 8px; border-radius:6px;">
                                                    <i class="bi bi-person"></i> #{{ $refLead->id }} {{ $refLead->name }}
                                                    <small style="color:#047857;">({{ $refLead->status?->stage?->localizedName() ?? '—' }})</small>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div id="dynamicReferralsNetwork" style="display:none;" class="info-row"></div>
                        @endif

                        <div class="info-row">
                            <span class="info-label">{{ __('crm.address') }}</span>
                            <span class="info-value">{{ trim(($lead->governorate ?? '').' '.($lead->address ?? '')) ?: '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">{{ __('crm.solution_type') }}</span>
                            <span class="info-value">{{ $solutionTypeLabel ?: '—' }}</span>
                        </div>
                        @if ($lead->lines_count)
                            <div class="info-row">
                                <span class="info-label">عدد الخطوط المطلوبة</span>
                                <span class="info-value">{{ $lead->lines_count }} خطوط</span>
                            </div>
                        @endif
                        @if ($lead->extensions)
                            <div class="info-row">
                                <span class="info-label">التحويلات المطلوبة</span>
                                <span class="info-value">{{ $lead->extensions }}</span>
                            </div>
                        @endif
                        @if ($lead->departments)
                            <div class="info-row">
                                <span class="info-label">الأقسام المطلوبة</span>
                                <span class="info-value">{{ $lead->departments }}</span>
                            </div>
                        @endif
                        <div class="info-row">
                            <span class="info-label">{{ __('crm.lead_source') }}</span>
                            <span class="info-value">{{ $lead->source ? __($lead->source) : '—' }}</span>
                        </div>
                       <div class="info-row">
                           <span class="info-label">{{ __('crm.assigned_employee') }}</span>
                           <span class="info-value">{{ $lead->assignedUser?->name ?? $lead->assigned_employee ?? __('crm.unassigned') }}</span>
                       </div>
                       <!-- DYNAMIC CUSTOMER FIELDS (TEMPERATURE, FITNESS GOALS, ETC.) -->
                       @if (!empty($customerFields))
                           @foreach ($customerFields as $cField)
                               @php
                                   $cfVal = $customerFieldValues[$cField->key] ?? null;
                               @endphp
                                @if (!in_array($cField->lead_attribute, ['first_name', 'last_name', 'phone', 'email', 'company_name', 'job_title', 'activity', 'governorate', 'address'], true))
                                   <div class="info-row">
                                       <span class="info-label">{{ $cField->localizedLabel() }}</span>
                                       <span class="info-value">
                                            @if ($cField->key === 'lead_temperature')
                                                @php
                                                    $cfTempStyle = match($cfVal) {
                                                        'hot' => 'background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;',
                                                        'warm' => 'background:#fef3c7;color:#d97706;border:1px solid #fcd34d;',
                                                        'cold' => 'background:#e0f2fe;color:#0284c7;border:1px solid #7dd3fc;',
                                                        default => 'background:var(--bg);color:var(--muted);border:1px dashed var(--line);'
                                                    };
                                                    $cfDisplay = match($cfVal) {
                                                        'hot' => '🔥 ' . (app()->getLocale() === 'en' ? 'Hot' : 'حار (Hot)'),
                                                        'warm' => '⚡ ' . (app()->getLocale() === 'en' ? 'Warm' : 'متوسط (Warm)'),
                                                        'cold' => '❄️ ' . (app()->getLocale() === 'en' ? 'Cold' : 'بارد (Cold)'),
                                                        default => '🔘 ' . (app()->getLocale() === 'en' ? 'Not Set' : 'غير محدد')
                                                    };
                                                @endphp
                                                <div style="display:inline-flex;align-items:center;gap:10px;flex-wrap:wrap;">
                                                    <span class="badge" style="padding:4px 10px;border-radius:6px;font-weight:700;display:inline-flex;align-items:center;gap:4px;{{ $cfTempStyle }}">
                                                        <i class="bi bi-thermometer-half"></i> {{ $cfDisplay }}
                                                    </span>
                                                    @can('update', $lead)
                                                        <form method="POST" action="{{ route('v2.leads.temperature.update', $lead) }}" style="margin:0;display:inline-flex;align-items:center;">
                                                            @csrf
                                                            @method('PATCH')
                                                            <select name="temperature" onchange="this.form.submit()" style="font-size:12px;padding:3px 8px;border-radius:6px;border:1px solid var(--line);background:var(--card);color:var(--dark);cursor:pointer;" title="{{ app()->getLocale() === 'en' ? 'Change Temperature' : 'تغيير درجة الحرارة مباشرة' }}">
                                                                <option value="" @selected(empty($cfVal))>-- {{ app()->getLocale() === 'en' ? 'Set Temperature' : 'تحديد الحرارة' }} --</option>
                                                                <option value="hot" @selected($cfVal === 'hot')>🔥 {{ app()->getLocale() === 'en' ? 'Hot' : 'حار (Hot)' }}</option>
                                                                <option value="warm" @selected($cfVal === 'warm')>⚡ {{ app()->getLocale() === 'en' ? 'Warm' : 'متوسط (Warm)' }}</option>
                                                                <option value="cold" @selected($cfVal === 'cold')>❄️ {{ app()->getLocale() === 'en' ? 'Cold' : 'بارد (Cold)' }}</option>
                                                            </select>
                                                        </form>
                                                    @endcan
                                                </div>
                                            @elseif ($cfVal !== null && $cfVal !== '')
                                            @if ($cField->type === 'select' || $cField->type === 'multiselect')
                                                @php
                                                    $cfOptions = collect($cField->normalizedOptions())->keyBy('value');
                                                    if (is_array($cfVal)) {
                                                        $cfDisplay = implode(', ', array_map(fn($v) => (app()->getLocale() === 'en' && !empty($cfOptions->get($v)['label_en'])) ? $cfOptions->get($v)['label_en'] : ($cfOptions->get($v)['label_ar'] ?? $v), $cfVal));
                                                    } else {
                                                        $opt = $cfOptions->get($cfVal);
                                                        $cfDisplay = ($opt && app()->getLocale() === 'en' && !empty($opt['label_en'])) ? $opt['label_en'] : ($opt['label_ar'] ?? $cfVal);
                                                    }
                                                @endphp
                                                    <span class="badge" style="padding:4px 10px;border-radius:6px;font-weight:700;">{{ $cfDisplay }}</span>
                                            @elseif ($cField->type === 'checkbox')
                                                {{ $cfVal ? __('crm.yes') : __('crm.no') }}
                                            @else
                                                {{ is_array($cfVal) ? implode(', ', $cfVal) : $cfVal }}
                                            @endif
                                            @else
                                                <span style="color:var(--muted);font-style:italic;">— {{ __('crm.not_specified') ?: 'غير محدد' }} —</span>
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        <!-- ANY UNREGISTERED DYNAMIC DATA FIELDS -->
                        @php
                            $registeredKeys = collect($customerFields ?? [])->pluck('key')->all();
                            $rawCustom = is_array($lead->custom_fields) ? $lead->custom_fields : [];
                            $unregisteredCustom = array_diff_key($rawCustom, array_flip($registeredKeys));
                        @endphp
                        @foreach ($unregisteredCustom as $uKey => $uVal)
                            @if ($uVal !== null && $uVal !== '')
                                <div class="info-row">
                                    <span class="info-label">{{ ucwords(str_replace(['_', '-'], ' ', (string) $uKey)) }}</span>
                                    <span class="info-value">{{ is_array($uVal) ? implode(', ', $uVal) : $uVal }}</span>
                                </div>
                            @endif
                        @endforeach

                        <div class="info-row">
                            <span class="info-label">{{ __('crm.registration_date') }}</span>
                            <span class="info-value">{{ $lead->created_at ? $lead->created_at->format('Y-m-d h:i A') : '—' }}</span>
                        </div>
                        @if ($lead->creator || $lead->created_by)
                            <div class="info-row">
                                <span class="info-label">{{ __('crm.registered_by') }}</span>
                                <span class="info-value">{{ $lead->creator?->name ?? $lead->created_by }}</span>
                            </div>
                        @endif
                    </div>
                </section>

                <!-- STAGE DATA SECTION -->
                @if (!empty($stageSections))
                <section class="panel" id="lead-stage-panel">
                    <div class="panel-head" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                        <h2>
                            <i class="bi bi-diagram-3-fill" style="color:var(--red);"></i>
                            <span>{{ __('crm.stage_data') }}</span>
                        </h2>

                        <!-- Display Filter: All Stages vs Current Stage -->
                        <div class="stage-view-filter-dock" style="display:inline-flex; align-items:center; background:var(--bg); border:1px solid var(--line); border-radius:10px; padding:3px; gap:4px;">
                            <button type="button" class="btn small stage-filter-btn is-active" data-stage-filter="all" id="btnStageModeAll" style="font-size:12px; padding:4px 12px; border-radius:8px; border:none; background:var(--card); color:var(--dark); font-weight:700; cursor:pointer; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                                <i class="bi bi-grid-fill"></i>
                                <span>{{ app()->getLocale() === 'ar' ? 'عرض جميع المراحل' : 'All Stages' }}</span>
                            </button>
                            <button type="button" class="btn small stage-filter-btn" data-stage-filter="current" id="btnStageModeCurrent" style="font-size:12px; padding:4px 12px; border-radius:8px; border:none; background:transparent; color:var(--muted); font-weight:700; cursor:pointer;">
                                <i class="bi bi-check-circle-fill" style="color:var(--red);"></i>
                                <span>{{ app()->getLocale() === 'ar' ? 'المرحلة الحالية فقط' : 'Current Stage Only' }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Quick Stage Jump Bar -->
                    <div class="stage-jump-pills-bar" style="display:flex; gap:8px; overflow-x:auto; padding:10px 14px; border-bottom:1px solid var(--line); background:var(--bg); border-radius:10px; margin:12px 0 16px;">
                        @foreach ($stageSections as $section)
                            <button
                                type="button"
                                class="stage-jump-pill {{ $section['id'] === $currentStageId ? 'is-current' : '' }}"
                                data-jump-to-stage="{{ $section['id'] }}"
                                style="display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border-radius:8px; border:1px solid {{ $section['id'] === $currentStageId ? $section['color'] : 'var(--line)' }}; background:{{ $section['id'] === $currentStageId ? 'var(--card)' : 'transparent' }}; font-size:12px; font-weight:700; color:var(--dark); cursor:pointer; white-space:nowrap; flex-shrink:0;"
                            >
                                <span style="width:8px; height:8px; border-radius:50%; background:{{ $section['color'] }};"></span>
                                <span>{{ $section['name'] }}</span>
                                @if ($section['is_current'])
                                    <span style="font-size:10px; background:{{ $section['color'] }}; color:#fff; border-radius:4px; padding:1px 4px;">{{ app()->getLocale() === 'en' ? 'Current' : 'الحالية' }}</span>
                                @elseif ($section['filled_count'] > 0)
                                    <span style="font-size:10px; background:var(--line); color:var(--muted); border-radius:4px; padding:1px 4px;">{{ $section['filled_count'] }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    <!-- All Stages Stacked Cards -->
                    <div class="stage-panes-wrapper" id="stagePanesWrapper" style="display:grid; gap:16px;">
                        @foreach ($stageSections as $section)
                            <div
                                class="stage-pane stage-card-block {{ $section['id'] === $currentStageId ? 'is-current-stage' : '' }}"
                                id="stage-card-{{ $section['id'] }}"
                                data-stage-id="{{ $section['id'] }}"
                                data-is-current="{{ $section['is_current'] ? '1' : '0' }}"
                                style="border: 1px solid var(--line); border-radius: 12px; overflow: hidden; background: var(--card); transition: box-shadow 0.2s ease;"
                            >
                                <!-- Stage Header Card -->
                                <div class="stage-pane-header" style="border-inline-start: 5px solid {{ $section['color'] }}; padding: 12px 16px; background: {{ $section['is_current'] ? 'rgba(239, 68, 68, 0.04)' : 'var(--bg)' }}; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
                                    <div class="stage-pane-header-info" style="display:flex; align-items:center; gap:8px;">
                                        <span class="stage-pane-name" style="color: {{ $section['color'] }}; font-weight:800; font-size:14px; display:inline-flex; align-items:center; gap:6px;">
                                            <i class="bi bi-layers-half"></i> {{ $section['name'] }}
                                        </span>
                                        @if ($section['is_current'])
                                            <span class="badge active" style="font-size:11px; font-weight:700;">
                                                <i class="bi bi-check-circle-fill"></i> {{ __('crm.current_stage') }}{{ $section['current_status'] ? ': ' . $section['current_status'] : '' }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="stage-pane-header-meta" style="display:flex; align-items:center; gap:8px;">
                                        <span class="badge {{ $section['filled_count'] > 0 ? 'success' : '' }}" style="font-size: 11px; font-weight:700;">
                                            {{ $section['filled_count'] }} / {{ $section['fields_count'] }} {{ app()->getLocale() === 'ar' ? 'حقل مكتمل' : 'fields filled' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Fields List -->
                                @if (!empty($section['fields']))
                                    <div class="info-list" style="padding: 12px 16px; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 10px;">
                                        @foreach ($section['fields'] as $field)
                                            <div class="info-row" style="background: var(--bg); padding: 8px 12px; border-radius: 8px; border: 1px solid var(--line); display: flex; flex-direction: column; gap: 4px;">
                                                <span class="info-label" style="display:flex; align-items:center; gap:4px; font-size:11.5px; color:var(--muted); font-weight:700;">
                                                    <i class="bi bi-dot" style="font-size:18px; color:{{ $section['color'] }}; line-height:0.5;"></i>
                                                    {{ $field['label'] }}
                                                </span>
                                                <span class="info-value {{ $field['has_value'] ? '' : 'text-muted' }}" style="font-size:13px; color:{{ $field['has_value'] ? 'var(--dark)' : 'var(--muted)' }}; font-weight:{{ $field['has_value'] ? '700' : 'normal' }};">
                                                    {{ $field['value'] }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="stage-empty-box" style="padding: 16px; text-align: center; color: var(--muted); font-size: 12.5px;">
                                        <i class="bi bi-inbox"></i>
                                        <span>{{ app()->getLocale() === 'ar' ? 'لا توجد حقول معرفة لهذه المرحلة' : 'No fields defined for this stage' }}</span>
                                    </div>
                                @endif

                                <!-- Stage History / Past Submissions if any -->
                                @if ($section['history']->isNotEmpty())
                                    <div class="stage-history-subpanel" style="border-top: 1px dashed var(--line); padding: 12px 16px; background: rgba(0,0,0,0.015);">
                                        <div class="stage-history-subhead" style="font-size: 12px; font-weight: 800; color: var(--muted); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                            <i class="bi bi-clock-history"></i>
                                            <span>{{ app()->getLocale() === 'ar' ? 'سجل الإدخالات السابقة في مرحلة ' . $section['name'] : 'Past submissions in ' . $section['name'] }}</span>
                                        </div>
                                        <div class="stage-history-list" style="display: grid; gap: 8px;">
                                            @foreach ($section['history'] as $historyGroup)
                                                <div class="stage-history-card" style="background: var(--card); border: 1px solid var(--line); border-radius: 8px; padding: 8px 12px;">
                                                    <div class="stage-history-top" style="display:flex; justify-content:space-between; font-size:11.5px; color:var(--muted); margin-bottom:6px;">
                                                        <span class="stage-history-actor">
                                                            <i class="bi bi-person"></i> {{ $historyGroup['actor'] }}
                                                        </span>
                                                        <span class="stage-history-date">
                                                            <i class="bi bi-calendar3"></i> {{ $historyGroup['date'] ? $historyGroup['date']->format('Y-m-d h:i A') : '—' }}
                                                        </span>
                                                    </div>
                                                    @if (!empty($historyGroup['values']))
                                                        <div class="stage-history-values" style="display:flex; flex-wrap:wrap; gap:6px;">
                                                            @foreach ($historyGroup['values'] as $hVal)
                                                                <span class="stage-history-pill" style="font-size: 11px; padding: 2px 8px; border-radius: 4px; background: var(--bg); border: 1px solid var(--line);">
                                                                    <b>{{ $hVal['label'] }}:</b> {{ $hVal['value'] }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
                @endif

                <!-- PHONE NUMBERS & CONTACTS -->
                <section class="panel">
                    <div class="panel-head">
                        <h2><i class="bi bi-telephone"></i> {{ __('crm.lead_phone_numbers') }}</h2>
                    </div>
                    <div class="info-list">
                        <div class="info-row is-primary-phone">
                            <div>
                                <span class="badge active" style="margin-inline-end:6px">{{ __('crm.primary_badge') }}</span>
                                @if ($lead->phone)
                                    @can('leads.followups.view')
                                        @if ($callPhone)
                                            <a
                                                class="js-call-followup"
                                                href="{{ route('v2.leads.followups.index', ['lead' => $lead, 'channel' => 'call']) }}"
                                                data-call-href="tel:{{ $callPhone }}"
                                                title="{{ __('crm.open_microsip_followup') }}"
                                                style="font-weight:900;color:inherit;text-decoration:none"
                                                dir="ltr"
                                            >
                                                {{ $lead->phone }}
                                            </a>
                                        @else
                                            <a href="tel:{{ $lead->phone }}" style="font-weight:900;color:inherit;text-decoration:none" dir="ltr">
                                                {{ $lead->phone }}
                                            </a>
                                        @endif
                                    @else
                                        <a href="tel:{{ $lead->phone }}" style="font-weight:900;color:inherit;text-decoration:none" dir="ltr">
                                            {{ $lead->phone }}
                                        </a>
                                    @endcan
                                @else
                                    <span style="color:var(--muted)">—</span>
                                @endif
                            </div>
                            <div style="display:flex;gap:6px">
                                @if ($callPhone)
                                    @can('leads.followups.view')
                                        <a
                                            class="btn small soft js-call-followup"
                                            href="{{ route('v2.leads.followups.index', ['lead' => $lead, 'channel' => 'call']) }}"
                                            data-call-href="tel:{{ $callPhone }}"
                                            title="{{ __('crm.open_microsip_followup') }}"
                                        >
                                            <i class="bi bi-telephone"></i>
                                        </a>
                                    @else
                                        <a class="btn small soft" href="tel:{{ $callPhone }}" title="{{ __('crm.call_action') }}">
                                            <i class="bi bi-telephone"></i>
                                        </a>
                                    @endcan
                                @endif
                                @if ($whatsappPhone)
                                    <a href="https://wa.me/{{ $whatsappPhone }}" target="_blank" rel="noopener noreferrer" class="btn small success" title="{{ __('crm.whatsapp') }}">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                @endif
                            </div>
                        </div>

                        @if ($lead->email)
                            <div class="info-row">
                                <div>
                                    <span class="badge" style="margin-inline-end:6px">{{ __('crm.email') }}</span>
                                    <span style="font-weight:700">{{ $lead->email }}</span>
                                </div>
                                <a href="mailto:{{ $lead->email }}" class="btn small soft" title="إرسال بريد">
                                    <i class="bi bi-envelope"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </section>

                <!-- DOCUMENTS & QUOTATIONS SECTION -->
                @php
                    $leadDocs = $lead->relationLoaded('documents') ? $lead->documents : $lead->documents()->with('stage', 'uploader')->get();
                    $quotationDocs = $leadDocs->where('category', \App\Models\LeadDocument::CATEGORY_QUOTATION);
                    $otherDocs = $leadDocs->where('category', '!=', \App\Models\LeadDocument::CATEGORY_QUOTATION);
                    $canViewQuotations = auth()->user()?->can('quotations.view');
                @endphp

                @if (($hasQuotationFile || $quotationDocs->isNotEmpty()) && $canViewQuotations)
                    <section class="panel">
                        <div class="panel-head">
                            <h2><i class="bi bi-file-earmark-pdf"></i> {{ __('crm.price_quotation') }}</h2>
                            <span class="badge active">{{ $lead->quotation_sent ? 'تم الإرسال' : 'جاهز' }}</span>
                        </div>
                        <div class="info-list">
                            @if ($quotationDocs->isNotEmpty())
                                @foreach ($quotationDocs as $qDoc)
                                    <div class="info-row">
                                        <div>
                                            <strong style="display:block;font-size:13px">{{ $qDoc->original_name }}</strong>
                                            <small style="color:var(--muted)">
                                                {{ $qDoc->formattedSize() }} • {{ $qDoc->created_at?->format('Y-m-d H:i') }}
                                                @if ($qDoc->stage) • مرحلة: {{ $qDoc->stage->localizedName() }} @endif
                                                @if ($qDoc->uploader) • بواسطة: {{ $qDoc->uploader->name }} @endif
                                            </small>
                                        </div>
                                        <div style="display:flex;gap:6px">
                                            <a class="btn small soft" href="{{ route('v2.leads.documents.preview', [$lead, $qDoc]) }}" target="_blank" rel="noopener noreferrer" title="معاينة الملف">
                                                <i class="bi bi-eye"></i> معاينة
                                            </a>
                                            <a class="btn small soft" href="{{ route('v2.leads.documents.download', [$lead, $qDoc]) }}" title="تحميل الملف">
                                                <i class="bi bi-download"></i> تحميل
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            @elseif ($hasQuotationFile)
                                <div class="info-row">
                                    <div>
                                        <strong style="display:block;font-size:13px">{{ $quotationFileName ?: 'ملف عرض السعر' }}</strong>
                                        <small style="color:var(--muted)">ملف عرض السعر الرسمي المرفق للعميل</small>
                                    </div>
                                    <div style="display:flex;gap:6px">
                                        <a class="btn small soft" href="{{ route('v2.leads.quotation.preview', $lead) }}" target="_blank" rel="noopener noreferrer" title="معاينة الملف">
                                            <i class="bi bi-eye"></i> معاينة
                                        </a>
                                        <a class="btn small soft" href="{{ route('v2.leads.quotation.download', $lead) }}" title="تحميل الملف">
                                            <i class="bi bi-download"></i> تحميل
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>
                @endif

                @if ($otherDocs->isNotEmpty())
                    <section class="panel">
                        <div class="panel-head">
                            <h2><i class="bi bi-folder2-open"></i> المستندات والمرفقات</h2>
                            <span class="badge">{{ $otherDocs->count() }}</span>
                        </div>
                        <div class="info-list">
                            @foreach ($otherDocs as $oDoc)
                                <div class="info-row">
                                    <div>
                                        <strong style="display:block;font-size:13px">{{ $oDoc->original_name }}</strong>
                                        <small style="color:var(--muted)">
                                            {{ $oDoc->formattedSize() }} • {{ $oDoc->created_at?->format('Y-m-d H:i') }}
                                            @if ($oDoc->stage) • مرحلة: {{ $oDoc->stage->localizedName() }} @endif
                                            @if ($oDoc->uploader) • بواسطة: {{ $oDoc->uploader->name }} @endif
                                        </small>
                                    </div>
                                    <div style="display:flex;gap:6px">
                                        <a class="btn small soft" href="{{ route('v2.leads.documents.preview', [$lead, $oDoc]) }}" target="_blank" rel="noopener noreferrer" title="معاينة الملف">
                                            <i class="bi bi-eye"></i> معاينة
                                        </a>
                                        <a class="btn small soft" href="{{ route('v2.leads.documents.download', [$lead, $oDoc]) }}" title="تحميل الملف">
                                            <i class="bi bi-download"></i> تحميل
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($lead->notes)
                    <section class="panel">
                        <div class="panel-head">
                            <h2><i class="bi bi-chat-left-text"></i> {{ __('crm.notes') }}</h2>
                        </div>
                        <div style="background:var(--bg); border:1px solid var(--line); border-radius:10px; padding:14px 16px; line-height:1.7; color:var(--dark); white-space:pre-line;">
                            {{ $lead->notes }}
                        </div>
                    </section>
                @endif
            </div>

            <!-- RIGHT COLUMN: VOIP, STAGE ANSWERS & TIMELINE -->
            <div>
                @can('voip.view')
                    <section class="panel" id="leadCallInsights" data-endpoint="{{ route('v2.leads.calls', $lead) }}">
                        <div class="panel-head">
                            <div>
                                <h2><i class="bi bi-soundwave"></i> {{ __('crm.lead_call_insights') }}</h2>
                                <small style="color:var(--muted)">مكالمات مسجلة ومربوطة مباشرة من خادم الاتصالات VoIP</small>
                            </div>
                            <span class="badge active">{{ __('crm.live_from_voip') }}</span>
                        </div>
                        <form class="call-filters" id="leadCallFilters">
                            <div><label for="callStartDate">{{ __('crm.from_date') }}</label><input id="callStartDate" name="start_date" type="date"></div>
                            <div><label for="callEndDate">{{ __('crm.to_date') }}</label><input id="callEndDate" name="end_date" type="date"></div>
                            <div><label for="callDirection">{{ __('crm.direction') }}</label><select id="callDirection" name="direction"><option value="">{{ __('crm.all_directions') }}</option><option value="inbound">{{ __('crm.incoming') }}</option><option value="outbound">{{ __('crm.outgoing') }}</option><option value="internal">{{ __('crm.internal') }}</option></select></div>
                            <button class="btn soft" type="submit"><i class="bi bi-funnel"></i> {{ __('crm.apply_filter') }}</button>
                        </form>
                        <div class="call-metrics" hidden data-call-metrics>
                            <div class="call-metric"><span>{{ __('crm.total_calls') }}</span><strong data-metric="total_calls">0</strong></div>
                            <div class="call-metric"><span>{{ __('crm.answer_rate') }}</span><strong data-metric="answer_rate_percent">0%</strong></div>
                            <div class="call-metric"><span>{{ __('crm.total_talk_time') }}</span><strong data-metric="total_talk_seconds">0:00</strong></div>
                            <div class="call-metric"><span>{{ __('crm.missed_calls') }}</span><strong data-metric="missed_calls">0</strong></div>
                        </div>
                        <div class="call-bars" hidden data-call-bars>
                            @foreach ([['inbound', __('crm.incoming'), '#16a34a'], ['outbound', __('crm.outgoing'), '#0284c7'], ['internal', __('crm.internal'), '#64748b']] as [$key, $label, $color])
                                <div class="call-bar">
                                    <span>{{ $label }}</span>
                                    <span class="call-bar-track"><span class="call-bar-fill" data-direction="{{ $key }}" style="width:0;background:{{ $color }}"></span></span>
                                    <b data-direction-count="{{ $key }}">0</b>
                                </div>
                            @endforeach
                        </div>
                        <div class="call-state" data-call-state><i class="bi bi-arrow-repeat"></i>{{ __('crm.loading_call_history') }}</div>
                        <div class="call-list" hidden data-call-list></div>
                        <div class="call-pagination" hidden data-call-pagination></div>
                    </section>
                @endcan


                <!-- CUSTOMER ACTIVITY TIMELINE -->
                <section class="panel">
                    <div class="panel-head">
                        <div>
                            <h2><i class="bi bi-clock-history"></i> {{ __('crm.lead_activity_timeline') }}</h2>
                            <small style="color:var(--muted)">سجل زمني لجميع المتابعات وتغييرات الحالات</small>
                        </div>
                        @can('leads.followups.view')
                            <a
                             href="{{ route('v2.leads.followups.index', $lead) }}"
                             class="btn primary small"
                            >
                                <i class="bi bi-plus-lg"></i> {{ __('crm.add_followup') }}
                            </a>
                        @endcan
                    </div>

                    @if ($timelineEvents->isNotEmpty())
                        <div class="timeline">
                            @foreach ($timelineEvents as $event)
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-card">
                                        <div class="timeline-head">
                                            <div>
                                                <span class="timeline-employee">
                                                    <i class="bi bi-person"></i> {{ $event['employee'] }}
                                                </span>
                                                @if ($event['type'] === 'followup')
                                                    @php
                                                        $commType = $event['communication_type'] ?? 'other';
                                                        $commLabel = $followupCommunicationTypes[$commType] ?? $commType;
                                                    @endphp
                                                    <span class="badge" style="background:#e0f2fe; color:#0369a1; margin-inline-start:6px">
                                                        <i class="bi bi-telephone"></i> {{ $commLabel }}
                                                    </span>
                                                    @if (!empty($event['call_attempt_number']) && $commType === 'call')
                                                        <span class="badge" style="background:#fef3c7; color:#b45309; margin-inline-start:4px">
                                                            المحاولة #{{ $event['call_attempt_number'] }}
                                                        </span>
                                                    @endif
                                                    @if (!empty($event['call_status']))
                                                        @php
                                                            $csLbl = $callStatuses[$event['call_status']] ?? $event['call_status'];
                                                        @endphp
                                                        <span class="badge" style="background:#e0e7ff; color:#4338ca; margin-inline-start:4px">
                                                            {{ $csLbl }}
                                                        </span>
                                                    @endif
                                                    @if (!empty($event['outcome_category']))
                                                        @php
                                                            $ocLbl = $outcomeCategories[$event['outcome_category']] ?? $event['outcome_category'];
                                                        @endphp
                                                        <span class="badge" style="background:#dcfce7; color:#15803d; margin-inline-start:4px">
                                                            {{ $ocLbl }}
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="badge" style="background:#fef3c7; color:#92400e; margin-inline-start:6px">
                                                        <i class="bi bi-arrow-left-right"></i> {{ __('crm.status_change') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="timeline-date">
                                                {{ $event['timestamp'] ? $event['timestamp']->format('Y-m-d h:i A') : '—' }}
                                            </span>
                                        </div>

                                        @if ($event['from_status'] && $event['to_status'] && $event['from_status'] !== $event['to_status'])
                                            <div style="margin-bottom:8px; font-size:12px; font-weight:800; color:var(--muted)">
                                                {{ __('crm.status_changed_to') }} <span style="color:#64748b">{{ $event['from_status'] }}</span> {{ app()->getLocale() === 'ar' ? '←' : '→' }} <strong style="color:var(--dark)">{{ $event['to_status'] }}</strong>
                                            </div>
                                        @elseif ($event['to_status'])
                                            <div style="margin-bottom:8px; font-size:12px; font-weight:800; color:var(--muted)">
                                                {{ __('crm.status') }} <strong style="color:var(--dark)">{{ $event['to_status'] }}</strong>
                                            </div>
                                        @endif

                                        @if ($event['details'])
                                            <div class="timeline-body">
                                                {{ $event['details'] }}
                                            </div>
                                        @endif

                                        @if (!empty($event['field_changes']))
                                            <div class="followup-field-changes">
                                                <strong>التعديلات التي قام بها الموظف</strong>
                                                <ul class="followup-change-list">
                                                    @foreach ($event['field_changes'] as $change)
                                                        <li class="followup-change-item">
                                                            <span class="followup-change-label">{{ $change['label'] ?? 'تعديل' }}</span>
                                                            <div>
                                                                <span class="followup-change-old">{{ $change['old'] ?? '----' }}</span>
                                                                →
                                                                <span class="followup-change-new">{{ $change['new'] ?? '----' }}</span>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        @if (!empty($event['stage_values']))
                                            <div style="margin-top:10px; padding:10px 12px; background:var(--card); border:1px solid var(--line); border-radius:8px;">
                                                <span style="display:block; font-size:11px; font-weight:800; color:#64748b; margin-bottom:6px;">
                                                    <i class="bi bi-ui-checks"></i> إجابات أسئلة المرحلة
                                                </span>
                                                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:6px 12px;">
                                                    @foreach ($event['stage_values'] as $sVal)
                                                        <div style="font-size:12px;">
                                                            <span style="color:var(--muted)">{{ $sVal['label'] }}:</span>
                                                            <strong style="color:var(--dark)">{{ $sVal['value'] }}</strong>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if ($event['next_follow_up'])
                                            <div style="margin-top:10px; padding-top:8px; border-top:1px dashed var(--line); font-size:12px; color:var(--muted)">
                                                <i class="bi bi-calendar-event"></i> المتابعة القادمة: <strong>{{ $event['next_follow_up']->format('Y-m-d h:i A') }}</strong>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="text-align:center; padding:30px 20px; color:var(--muted); background:var(--bg); border-radius:12px;">
                            <i class="bi bi-chat-square-dots" style="font-size:30px; display:block; margin-bottom:8px"></i>
                            <p style="margin:0; font-weight:700">لا توجد متابعات أو نشاطات مسجلة لهذا العميل حتى الآن.</p>
                            @can('leads.followups.view')
                                <a
                                 href="{{ route('v2.leads.followups.index', $lead) }}"
                                 class="btn primary small"
                                 style="margin-top:12px;"
                                >
                                    تسجيل أول متابعة الآن
                                </a>
                            @endcan
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </main>
</div>

@can('voip.view')
<script>
    (() => {
        const panel = document.getElementById('leadCallInsights');
        if (!panel) return;

        const form = document.getElementById('leadCallFilters');
        const state = panel.querySelector('[data-call-state]');
        const list = panel.querySelector('[data-call-list]');
        const pagination = panel.querySelector('[data-call-pagination]');
        const metrics = panel.querySelector('[data-call-metrics]');
        const bars = panel.querySelector('[data-call-bars]');
        const copy = {
            loading: @json(__('crm.loading_call_history')),
            empty: 'لا توجد مكالمات مسجلة تطابق التصفية.',
            error: 'تعذر جلب سجل المكالمات من خادم VoIP.',
            unassigned: @json(__('crm.unassigned')),
            extension: 'تحويلة',
            recording: 'تسجيل صوتي',
            directions: {
                inbound: @json(__('crm.incoming')),
                outbound: @json(__('crm.outgoing')),
                internal: @json(__('crm.internal')),
                unknown: 'غير محدد',
            },
            statuses: {
                answered: 'تم الرد',
                missed: 'لم يُرد عليها',
                failed: 'فشلت',
                busy: 'مشغول',
                no_answer: 'لا يوجد رد',
                unknown: 'غير معروف',
            },
            prev: 'السابق',
            next: 'التالي',
            showing: 'عرض :from - :to من أصل :total مكالمة',
            download: 'تحميل التسجيل الصوتي',
            speed: 'سرعة التشغيل',
        };
        const showState = (message, icon = 'bi-info-circle') => {
            const symbol = document.createElement('i');
            symbol.className = `bi ${icon}`;
            state.replaceChildren(symbol, document.createTextNode(message));
            state.hidden = false;
        };

        const formatDuration = (seconds) => {
            const total = Math.max(0, Number(seconds) || 0);
            return `${Math.floor(total / 60)}:${String(total % 60).padStart(2, '0')}`;
        };

        let allCalls = [];
        let currentPage = 1;
        const pageSize = 5;
        let activeAudio = null;

        const createAudioPlayer = (recordingUrl, callDurationSec) => {
            const player = document.createElement('div');
            player.className = 'crm-audio-player';

            const audio = document.createElement('audio');
            audio.preload = 'none';
            audio.src = recordingUrl;

            const playBtn = document.createElement('button');
            playBtn.type = 'button';
            playBtn.className = 'audio-play-btn';
            playBtn.title = 'تشغيل / إيقاف';
            playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';

            const trackWrap = document.createElement('div');
            trackWrap.className = 'audio-track-wrap';

            const seek = document.createElement('input');
            seek.type = 'range';
            seek.className = 'audio-seek';
            seek.min = '0';
            seek.max = '100';
            seek.value = '0';

            const timeRow = document.createElement('div');
            timeRow.className = 'audio-time-row';

            const currTime = document.createElement('span');
            currTime.textContent = '0:00';
            const durTime = document.createElement('span');
            durTime.textContent = callDurationSec ? formatDuration(callDurationSec) : '0:00';

            timeRow.appendChild(currTime);
            timeRow.appendChild(durTime);
            trackWrap.appendChild(seek);
            trackWrap.appendChild(timeRow);

            const speedBtn = document.createElement('button');
            speedBtn.type = 'button';
            speedBtn.className = 'audio-speed-btn';
            speedBtn.textContent = '1x';
            speedBtn.title = copy.speed;
            const speeds = [1, 1.25, 1.5, 2];
            let speedIdx = 0;
            speedBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                speedIdx = (speedIdx + 1) % speeds.length;
                audio.playbackRate = speeds[speedIdx];
                speedBtn.textContent = `${speeds[speedIdx]}x`;
            });

            const dlBtn = document.createElement('a');
            dlBtn.href = recordingUrl;
            dlBtn.download = '';
            dlBtn.className = 'audio-download-btn';
            dlBtn.title = copy.download;
            dlBtn.innerHTML = '<i class="bi bi-download"></i>';

            playBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (audio.paused) {
                    if (activeAudio && activeAudio !== audio) {
                        activeAudio.pause();
                    }
                    audio.play();
                } else {
                    audio.pause();
                }
            });

            audio.addEventListener('play', () => {
                activeAudio = audio;
                playBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
                player.classList.add('is-playing');
            });

            audio.addEventListener('pause', () => {
                playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
                player.classList.remove('is-playing');
            });

            audio.addEventListener('ended', () => {
                playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
                player.classList.remove('is-playing');
                seek.value = '0';
                currTime.textContent = '0:00';
            });

            audio.addEventListener('timeupdate', () => {
                if (!audio.duration || isNaN(audio.duration)) return;
                const pct = (audio.currentTime / audio.duration) * 100;
                seek.value = String(pct);
                currTime.textContent = formatDuration(audio.currentTime);
            });

            audio.addEventListener('loadedmetadata', () => {
                if (audio.duration && !isNaN(audio.duration)) {
                    durTime.textContent = formatDuration(audio.duration);
                }
            });

            seek.addEventListener('input', () => {
                if (!audio.duration || isNaN(audio.duration)) return;
                audio.currentTime = (Number(seek.value) / 100) * audio.duration;
            });

            player.appendChild(playBtn);
            player.appendChild(trackWrap);
            player.appendChild(speedBtn);
            player.appendChild(dlBtn);
            player.appendChild(audio);

            return player;
        };

        const renderCallsPage = () => {
            list.replaceChildren();
            const total = allCalls.length;
            const totalPages = Math.ceil(total / pageSize) || 1;
            currentPage = Math.min(Math.max(1, currentPage), totalPages);

            const startIdx = (currentPage - 1) * pageSize;
            const pageCalls = allCalls.slice(startIdx, startIdx + pageSize);

            pageCalls.forEach((call) => {
                const row = document.createElement('article');
                row.className = 'call-row';

                const dir = call.direction || 'unknown';
                const dirLabel = copy.directions[dir] || dir;

                let dirColor = '#64748b';
                let dirIcon = 'bi-arrow-left-right';
                if (dir === 'inbound') {
                    dirColor = '#16a34a';
                    dirIcon = 'bi-telephone-inbound-fill';
                } else if (dir === 'outbound') {
                    dirColor = '#0284c7';
                    dirIcon = 'bi-telephone-outbound-fill';
                }

                const partyCol = document.createElement('div');
                partyCol.innerHTML = `<strong style="display:flex;align-items:center;gap:5px"><i class="bi ${dirIcon}" style="color:${dirColor}"></i> ${dirLabel}</strong><small>${call.call_date || '—'}</small>`;

                const agentCol = document.createElement('div');
                const agentText = call.agent_name ? `${call.agent_name}` : (call.agent_extension ? `تحويلة ${call.agent_extension}` : '—');
                agentCol.innerHTML = `<strong>${agentText}</strong><small dir="ltr">${call.customer_number || call.src || call.dst || ''}</small>`;

                const durCol = document.createElement('div');
                durCol.innerHTML = `<strong>${call.duration_formatted || '0 ثانية'}</strong>`;

                const statusCol = document.createElement('div');
                const disp = (call.disposition || '').toLowerCase();
                const isAnswered = disp.includes('answer') || disp.includes('up');
                statusCol.innerHTML = `<span class="badge ${isAnswered ? 'active' : 'inactive'}">${isAnswered ? 'تم الرد' : 'لم يرد'}</span>`;

                row.appendChild(partyCol);
                row.appendChild(agentCol);
                row.appendChild(durCol);
                row.appendChild(statusCol);

                if (call.has_recording && call.media_id) {
                    const recUrl = `/voip/recordings/${encodeURIComponent(call.media_id)}`;
                    const player = createAudioPlayer(recUrl, call.duration_seconds || call.billsec || 0);
                    row.appendChild(player);
                }

                list.appendChild(row);
            });

            pagination.replaceChildren();
            if (totalPages > 1) {
                const info = document.createElement('span');
                info.className = 'call-pagination-info';
                info.textContent = copy.showing
                    .replace(':from', String(startIdx + 1))
                    .replace(':to', String(Math.min(startIdx + pageSize, total)))
                    .replace(':total', String(total));

                const controls = document.createElement('div');
                controls.className = 'call-pagination-controls';

                const prevBtn = document.createElement('button');
                prevBtn.type = 'button';
                prevBtn.className = 'btn-call-page';
                prevBtn.textContent = '‹';
                prevBtn.title = copy.prev;
                prevBtn.disabled = currentPage <= 1;
                prevBtn.addEventListener('click', () => {
                    currentPage--;
                    renderCallsPage();
                });
                controls.appendChild(prevBtn);

                for (let i = 1; i <= totalPages; i++) {
                    const pageBtn = document.createElement('button');
                    pageBtn.type = 'button';
                    pageBtn.className = `btn-call-page ${i === currentPage ? 'active' : ''}`;
                    pageBtn.textContent = String(i);
                    pageBtn.addEventListener('click', () => {
                        currentPage = i;
                        renderCallsPage();
                    });
                    controls.appendChild(pageBtn);
                }

                const nextBtn = document.createElement('button');
                nextBtn.type = 'button';
                nextBtn.className = 'btn-call-page';
                nextBtn.textContent = '›';
                nextBtn.title = copy.next;
                nextBtn.disabled = currentPage >= totalPages;
                nextBtn.addEventListener('click', () => {
                    currentPage++;
                    renderCallsPage();
                });
                controls.appendChild(nextBtn);

                pagination.appendChild(info);
                pagination.appendChild(controls);
                pagination.hidden = false;
            } else {
                pagination.hidden = true;
            }
        };

        const render = (payload) => {
            allCalls = payload.calls || payload.data || [];
            currentPage = 1;

            const total = allCalls.length;
            let answered = 0;
            let totalSeconds = 0;
            const counts = { inbound: 0, outbound: 0, internal: 0 };

            allCalls.forEach((call) => {
                const disp = (call.disposition || '').toLowerCase();
                const isAns = disp.includes('answer') || disp.includes('up');
                if (isAns) answered++;
                const sec = Number(call.duration_seconds || call.billsec || 0);
                totalSeconds += sec;

                const dir = call.direction || 'unknown';
                if (counts[dir] !== undefined) counts[dir]++;
            });

            const answerRate = total > 0 ? Math.round((answered / total) * 100) : 0;
            const missed = total - answered;

            panel.querySelector('[data-metric="total_calls"]').textContent = String(total);
            panel.querySelector('[data-metric="answer_rate_percent"]').textContent = `${answerRate}%`;
            panel.querySelector('[data-metric="total_talk_seconds"]').textContent = formatDuration(totalSeconds);
            panel.querySelector('[data-metric="missed_calls"]').textContent = String(missed);
            metrics.hidden = false;

            Object.entries(counts).forEach(([dir, count]) => {
                const fill = bars.querySelector(`[data-direction="${dir}"]`);
                const cnt = bars.querySelector(`[data-direction-count="${dir}"]`);
                if (fill) fill.style.width = total > 0 ? `${(count / total) * 100}%` : '0%';
                if (cnt) cnt.textContent = String(count);
            });
            bars.hidden = false;

            if (allCalls.length === 0) {
                list.hidden = true;
                pagination.hidden = true;
                showState(copy.empty);
            } else {
                state.hidden = true;
                list.hidden = false;
                renderCallsPage();
            }
        };

        const loadCalls = async () => {
            showState(copy.loading, 'bi-arrow-repeat');
            list.hidden = true;
            pagination.hidden = true;
            metrics.hidden = true;
            bars.hidden = true;

            const query = new URLSearchParams();
            new FormData(form).forEach((value, key) => {
                if (value) query.set(key, String(value));
            });
            query.set('limit', '100');

            try {
                const response = await fetch(`${panel.dataset.endpoint}?${query}`, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                const payload = await response.json();
                if (!response.ok || payload.success === false) throw new Error(payload.error || copy.error);
                render(payload);
            } catch (error) {
                showState(error instanceof Error ? error.message : copy.error, 'bi-exclamation-triangle');
            }
        };
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            loadCalls();
        });
        loadCalls();
    })();
</script>
@endcan

<script>
(() => {
 document
  .querySelectorAll('.js-call-followup')
  .forEach((link) => {
   link.addEventListener(
    'click',
    () => {
     const callHref = link.dataset.callHref;
     if (!callHref || link.target !== '_blank') {
      return;
     }
     window.setTimeout(() => {
       window.location.href = callHref;
     }, 120);
    }
   );
  });
})();
</script>
<script>
(() => {
    const filterBtns = document.querySelectorAll('[data-stage-filter]');
    const jumpPills = document.querySelectorAll('[data-jump-to-stage]');
    const stageCards = document.querySelectorAll('.stage-card-block');

    function applyFilter(filterMode, activeStageId = null) {
        filterBtns.forEach(btn => {
            const isActive = btn.dataset.stageFilter === filterMode;
            btn.classList.toggle('is-active', isActive);
            btn.style.background = isActive ? 'var(--card)' : 'transparent';
            btn.style.color = isActive ? 'var(--dark)' : 'var(--muted)';
        });

        stageCards.forEach(card => {
            if (filterMode === 'all') {
                card.style.display = '';
            } else if (filterMode === 'current') {
                card.style.display = card.dataset.isCurrent === '1' ? '' : 'none';
            } else if (filterMode === 'single' && activeStageId) {
                card.style.display = card.dataset.stageId === String(activeStageId) ? '' : 'none';
            }
        });
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            applyFilter(btn.dataset.stageFilter);
        });
    });

    jumpPills.forEach(pill => {
        pill.addEventListener('click', () => {
            const stageId = pill.dataset.jumpToStage;
            applyFilter('all');
            const targetCard = document.getElementById(`stage-card-${stageId}`);
            if (targetCard) {
                targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                targetCard.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.35)';
                setTimeout(() => {
                    targetCard.style.boxShadow = '';
                }, 1500);
            }
        });
    });
})();
</script>

<!-- CREATE GUARDIAN MODAL -->
<div id="createGuardianModal" class="crm-body-modal-shell" onclick="if(event.target.id === 'createGuardianModal') closeCreateGuardianModal()">
    <div class="crm-body-modal-dialog" style="max-width: 500px;" onclick="event.stopPropagation()">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid var(--line); padding-bottom:10px;">
            <h3 style="margin:0; font-size:16px; font-weight:800; display:flex; align-items:center; gap:8px; color:var(--dark);">
                <i class="bi bi-person-plus-fill" style="color:#4f46e5;"></i> {{ __('تسجيل ولي أمر جديد') }}
            </h3>
            <button type="button" onclick="closeCreateGuardianModal()" style="background:none; border:none; font-size:20px; cursor:pointer; color:var(--muted);">&times;</button>
        </div>

        <form id="createGuardianForm" onsubmit="submitCreateGuardian(event)">
            <div style="margin-bottom:14px;">
                <label style="display:block; margin-bottom:4px; font-weight:700; font-size:12.5px;">{{ __('اسم ولي الأمر بالكامل') }} <span style="color:var(--red)">*</span></label>
                <input type="text" id="newGuardianName" required placeholder="مثال: محمد أحمد علي"
                       style="width:100%; height:38px; padding:0 12px; border:1px solid var(--line); border-radius:8px; font-size:13px; background:var(--bg); color:var(--dark);">
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:14px;">
                <div>
                    <label style="display:block; margin-bottom:4px; font-weight:700; font-size:12.5px;">{{ __('رقم الهاتف الأساسي') }}</label>
                    <input type="tel" id="newGuardianPhone" placeholder="05xxxxxxxx"
                           style="width:100%; height:38px; padding:0 12px; border:1px solid var(--line); border-radius:8px; font-size:13px; background:var(--bg); color:var(--dark); direction:ltr; text-align:start;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:4px; font-weight:700; font-size:12.5px;">{{ __('صلة القرابة') }}</label>
                    <select id="newGuardianRelationship" style="width:100%; height:38px; padding:0 10px; border:1px solid var(--line); border-radius:8px; font-size:13px; background:var(--bg); color:var(--dark);">
                        <option value="أب">أب (Father)</option>
                        <option value="أم">أم (Mother)</option>
                        <option value="ولي أمر">ولي أمر (Guardian)</option>
                        <option value="أخ / أخت">أخ / أخت (Sibling)</option>
                        <option value="اللاعب نفسه">اللاعب نفسه (Self)</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block; margin-bottom:4px; font-weight:700; font-size:12.5px;">{{ __('ملاحظات الأسرة (اختياري)') }}</label>
                <textarea id="newGuardianNotes" rows="2" placeholder="أي تفاصيل خاصة بالتواصل أو العائلة..."
                          style="width:100%; padding:8px 12px; border:1px solid var(--line); border-radius:8px; font-size:13px; background:var(--bg); color:var(--dark); resize:vertical;"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--line); padding-top:12px;">
                <button type="button" class="btn small light" onclick="closeCreateGuardianModal()">{{ __('crm.cancel') }}</button>
                <button type="submit" id="saveGuardianBtn" class="btn small primary">{{ __('حفظ وربط باللاعب') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- CREATE REFERRAL MODAL -->
<div id="createReferralModal" class="crm-body-modal-shell" onclick="if(event.target.id === 'createReferralModal') closeReferralModal()">
    <div class="crm-body-modal-dialog" style="max-width: 500px;" onclick="event.stopPropagation()">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid var(--line); padding-bottom:10px;">
            <h3 style="margin:0; font-size:16px; font-weight:800; display:flex; align-items:center; gap:8px; color:var(--dark);">
                <i class="bi bi-person-plus-fill" style="color:#059669;"></i> {{ __('إحالة صديق / عميل جديد') }}
            </h3>
            <button type="button" onclick="closeReferralModal()" style="background:none; border:none; font-size:20px; cursor:pointer; color:var(--muted);">&times;</button>
        </div>

        <div style="background:#ecfdf5; border:1px solid #a7f3d0; border-radius:8px; padding:10px 12px; margin-bottom:14px; font-size:12px; color:#065f46;">
            <i class="bi bi-info-circle-fill"></i>
            سيتم إنشاء عميل محتمل جديد بمصدر <strong>(إحالة / Referral)</strong> وربطه تلقائيًا بالمشترك <strong>{{ $lead->name }}</strong>.
        </div>

        <form id="createReferralForm" onsubmit="submitCreateReferral(event)">
            <div style="margin-bottom:14px;">
                <label style="display:block; margin-bottom:4px; font-weight:700; font-size:12.5px;">{{ __('اسم اللاعب أو الصديق المُحال') }} <span style="color:var(--red)">*</span></label>
                <input type="text" id="newReferralName" required placeholder="مثال: يوسف خالد"
                       style="width:100%; height:38px; padding:0 12px; border:1px solid var(--line); border-radius:8px; font-size:13px; background:var(--bg); color:var(--dark);">
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:14px;">
                <div>
                    <label style="display:block; margin-bottom:4px; font-weight:700; font-size:12.5px;">{{ __('رقم الهاتف / واتساب') }} <span style="color:var(--red)">*</span></label>
                    <input type="tel" id="newReferralPhone" required placeholder="05xxxxxxxx"
                           style="width:100%; height:38px; padding:0 12px; border:1px solid var(--line); border-radius:8px; font-size:13px; background:var(--bg); color:var(--dark); direction:ltr; text-align:start;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:4px; font-weight:700; font-size:12.5px;">{{ __('النشاط المهتم به') }}</label>
                    <input type="text" id="newReferralActivity" placeholder="مثال: كرة قدم / جمباز" value="{{ $lead->activity }}"
                           style="width:100%; height:38px; padding:0 12px; border:1px solid var(--line); border-radius:8px; font-size:13px; background:var(--bg); color:var(--dark);">
                </div>
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block; margin-bottom:4px; font-weight:700; font-size:12.5px;">{{ __('ملاحظات الإحالة (اختياري)') }}</label>
                <textarea id="newReferralNotes" rows="2" placeholder="أي تفاصيل عن اللاعب أو ولي أمره أو معرفته بالمشترك..."
                          style="width:100%; padding:8px 12px; border:1px solid var(--line); border-radius:8px; font-size:13px; background:var(--bg); color:var(--dark); resize:vertical;"></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--line); padding-top:12px;">
                <button type="button" class="btn small soft" onclick="closeReferralModal()">{{ __('إلغاء') }}</button>
                <button type="submit" id="saveReferralBtn" class="btn small primary" style="background:#059669; border-color:#059669;">{{ __('تسجيل الإحالة الآن') }}</button>
            </div>
        </form>
    </div>
</div>
<script>
const leadId = {{ $lead->id }};
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

function openCreateGuardianModal() {
    const modal = document.getElementById('createGuardianModal');
    modal.classList.add('is-open');
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
    setTimeout(() => document.getElementById('newGuardianName')?.focus(), 50);
}

function closeCreateGuardianModal() {
    const modal = document.getElementById('createGuardianModal');
    modal.classList.remove('is-open');
    modal.style.display = 'none';
    document.body.classList.remove('modal-open');
}

function openReferralModal() {
    const modal = document.getElementById('createReferralModal');
    modal.classList.add('is-open');
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
    setTimeout(() => document.getElementById('newReferralName')?.focus(), 50);
}

function closeReferralModal() {
    const modal = document.getElementById('createReferralModal');
    modal.classList.remove('is-open');
    modal.style.display = 'none';
    document.body.classList.remove('modal-open');
}

async function submitCreateReferral(e) {
    e.preventDefault();
    const btn = document.getElementById('saveReferralBtn');
    btn.disabled = true;
    btn.textContent = 'جاري التسجيل...';

    const payload = {
        name: document.getElementById('newReferralName').value.trim(),
        phone: document.getElementById('newReferralPhone').value.trim(),
        activity: document.getElementById('newReferralActivity').value.trim(),
        notes: document.getElementById('newReferralNotes').value.trim(),
    };

    try {
        const res = await fetch(`/leads/${leadId}/referrals`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const data = await res.json();
        if (res.ok && data.success) {
            closeReferralModal();
            document.getElementById('createReferralForm').reset();
            alert(data.message || 'تم تسجيل الإحالة بنجاح!');
            window.location.reload();
        } else {
            alert(data.message || 'حدث خطأ أثناء حفظ الإحالة. تحقق من البيانات.');
        }
    } catch (err) {
        alert('تعذر الاتصال بالخادم. حاول مرة أخرى.');
    } finally {
        btn.disabled = false;
        btn.textContent = 'تسجيل الإحالة الآن';
    }
}
let guardianSearchTimeout = null;
const guardianSearchInput = document.getElementById('guardianSearchInput');
const guardianSearchResults = document.getElementById('guardianSearchResults');

if (guardianSearchInput) {
    guardianSearchInput.addEventListener('input', () => {
        clearTimeout(guardianSearchTimeout);
        const q = guardianSearchInput.value.trim();
        if (q.length === 0) {
            guardianSearchResults.style.display = 'none';
            guardianSearchResults.innerHTML = '';
            return;
        }
        guardianSearchTimeout = setTimeout(() => {
            fetch(`{{ route('v2.guardians.search') }}?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.guardians || data.guardians.length === 0) {
                        guardianSearchResults.innerHTML = '<div style="padding:10px 12px; font-size:12px; color:var(--muted); text-align:center;">لم يتم العثور على أولياء أمور مطابقين.</div>';
                    } else {
                        guardianSearchResults.innerHTML = data.guardians.map(g => `
                            <div class="guardian-search-item" onclick="linkExistingGuardian(${g.id})" 
                                 style="padding:8px 12px; border-bottom:1px solid var(--line); cursor:pointer; font-size:12.5px; display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <strong style="color:var(--dark); display:block;">${g.name} <small style="color:#6366f1;">(#${g.id})</small></strong>
                                    <small style="color:var(--muted);">${g.phone || 'بدون هاتف'} • ${g.relationship || 'ولي أمر'}</small>
                                </div>
                                <button type="button" class="btn small soft" style="font-size:11px; padding:2px 8px;">ربط</button>
                            </div>
                        `).join('');
                    }
                    guardianSearchResults.style.display = 'block';
                });
        }, 250);
    });

    document.addEventListener('click', (e) => {
        if (!guardianSearchInput.contains(e.target) && !guardianSearchResults.contains(e.target)) {
            guardianSearchResults.style.display = 'none';
        }
    });
}

function renderLinkedGuardian(guardian, siblings = []) {
    const display = document.getElementById('linkedGuardianDisplay');
    const controls = document.getElementById('unlinkedGuardianControls');
    const actionBtns = document.getElementById('guardianActionBtns');
    const siblingsWrap = document.getElementById('guardianSiblingsWrap');
    const siblingsList = document.getElementById('siblingsList');

    display.innerHTML = `
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <span class="badge" style="background:#eef2ff; color:#4338ca; border:1px solid #c7d2fe; font-size:13px; font-weight:700; padding:6px 12px; border-radius:8px; display:inline-flex; align-items:center; gap:6px;">
                <i class="bi bi-person-check-fill"></i>
                <span>${guardian.name}</span>
                <small style="color:#6366f1; font-weight:600;">(#ID ${guardian.id})</small>
                ${guardian.relationship ? `<span style="opacity:0.7;">• ${guardian.relationship}</span>` : ''}
                ${guardian.phone ? `<span style="direction:ltr; font-family:var(--font-mono); font-size:12px;">• ${guardian.phone}</span>` : ''}
            </span>
        </div>
        ${guardian.notes ? `
            <div id="guardianNotesWrap" style="display:flex; align-items:flex-start; gap:6px; margin-top:6px; font-size:12px; color:var(--dark); background:#fff; border:1px solid var(--line); border-radius:8px; padding:6px 10px;">
                <i class="bi bi-card-text" style="color:#4f46e5; margin-top:2px;"></i>
                <div>
                    <strong style="color:var(--muted); font-size:11px; display:block;">ملاحظات الأسرة:</strong>
                    <span id="guardianNotesText">${guardian.notes}</span>
                </div>
            </div>
        ` : ''}
    `;
    display.style.display = 'block';
    controls.style.display = 'none';
    actionBtns.innerHTML = `
        <button type="button" class="btn small soft" onclick="unlinkGuardian()" style="color:#dc2626; padding:3px 8px; font-size:11px;" title="فك ارتباط ولي الأمر">
            <i class="bi bi-x-circle"></i> إلغاء الربط
        </button>
    `;

    if (siblings && siblings.length > 0) {
        siblingsList.innerHTML = siblings.map(s => `
            <a href="/leads/${s.id}" class="badge" style="background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; font-size:11.5px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:6px;">
                <i class="bi bi-person"></i> #${s.id} ${s.name}
                <small style="color:#64748b;">(${s.stage})</small>
            </a>
        `).join('');
        siblingsWrap.style.display = 'block';
    } else {
        siblingsWrap.style.display = 'none';
    }
}

function linkExistingGuardian(guardianId) {
    fetch(`{{ url('leads') }}/${leadId}/guardian/link`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ guardian_id: guardianId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            renderLinkedGuardian(data.guardian, data.siblings);
            guardianSearchResults.style.display = 'none';
            guardianSearchInput.value = '';
        }
    });
}

function unlinkGuardian() {
    if (!confirm('هل أنت متأكد من فك ارتباط ولي الأمر عن هذا اللاعب؟')) return;
    fetch(`{{ url('leads') }}/${leadId}/guardian/unlink`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('linkedGuardianDisplay').style.display = 'none';
            document.getElementById('unlinkedGuardianControls').style.display = 'block';
            document.getElementById('guardianActionBtns').innerHTML = '';
            document.getElementById('guardianSiblingsWrap').style.display = 'none';
        }
    });
}

function submitCreateGuardian(e) {
    e.preventDefault();
    const btn = document.getElementById('saveGuardianBtn');
    btn.disabled = true;

    const payload = {
        name: document.getElementById('newGuardianName').value,
        phone: document.getElementById('newGuardianPhone').value,
        relationship: document.getElementById('newGuardianRelationship').value,
        notes: document.getElementById('newGuardianNotes').value,
        lead_id: leadId,
    };

    fetch(`{{ route('v2.guardians.store') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            closeCreateGuardianModal();
            renderLinkedGuardian(data.guardian, data.siblings);
            document.getElementById('createGuardianForm').reset();
        } else {
            alert(data.message || 'حدث خطأ أثناء حفظ ولي الأمر.');
        }
    })
    .catch(() => {
        btn.disabled = false;
        alert('حدث خطأ في الاتصال بالخادم.');
    });
}
</script>
<script src="{{ asset('crm-notifications.js') }}?v=1.0.0"></script>
</body>
</html>
