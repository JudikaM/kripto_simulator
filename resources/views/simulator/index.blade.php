<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kripto Simulator | NakamotoX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="//unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        /* Base Variables - SaaS Dashboard V2 */
        :root {
            --bg-base: #0F172A;
            --text-main: #F8FAFC;
            --text-muted: #94A3B8;
            --border-glass: #334155;
            --bg-glass: #1E293B;
            --radius: 8px;
            --font-main: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            --transition-speed: 0.3s;
        }

        /* ChaCha20 Theme (Indigo) */
        body.theme-chacha20 {
            --primary: #6366F1;
            --primary-hover: #4F46E5;
            --primary-rgb: 99, 102, 241;
        }

        /* Caesar Theme (Teal) */
        body.theme-caesar {
            --primary: #0D9488;
            --primary-hover: #0F766E;
            --primary-rgb: 13, 148, 136;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            background-color: var(--bg-base);
            color: var(--text-main);
            font-family: var(--font-main);
            font-size: 14px;
            min-height: 100vh;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
            position: relative;
            transition: --primary var(--transition-speed), --primary-hover var(--transition-speed);
        }

        /* Nav & Hero */
        .navbar {
            padding: 24px 4%;
            display: flex; justify-content: space-between; align-items: center;
            background: linear-gradient(to bottom, rgba(0,0,0,0.6) 0%, transparent 100%);
        }
        .logo { color: var(--text-main); font-size: 26px; font-weight: 800; letter-spacing: -1px; text-decoration: none; text-shadow: 0 0 10px rgba(255,255,255,0.3);}
        .logo span { color: var(--primary); transition: color var(--transition-speed); }
        
        .hero { padding: 20px 4% 40px; transition: all var(--transition-speed); }
        .hero h1 { font-size: 3rem; font-weight: 800; margin-bottom: 12px; letter-spacing: -1.5px; }
        .hero h1 span { color: var(--primary); transition: color var(--transition-speed); }
        .hero p { font-size: 1.15rem; color: var(--text-muted); max-width: 700px; font-weight: 300; }

        .status-badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 16px; border-radius: 20px;
            background: rgba(255,255,255,0.05); border: 1px solid var(--border-glass);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            font-size: 13px; font-weight: 500;
        }

        .container { padding: 0 2% 80px; max-width: 1800px; margin: 0 auto; }

        /* Pipeline Layout Grid */
        .pipeline-layout {
            display: grid; grid-template-columns: 1fr; gap: 24px; align-items: start;
        }
        @media (min-width: 1024px) {
            .pipeline-layout { grid-template-columns: 1.2fr 1fr; }
        }
        .column-block { display: flex; flex-direction: column; min-width: 0; gap: 24px; }

        /* Solid Card */
        .card {
            background-color: var(--bg-glass);
            border-radius: var(--radius); padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border-glass);
        }
        .card.h-full { flex: 1; }
        
        .card-header-banner {
            border-bottom: 2px solid var(--primary);
            padding-bottom: 12px; margin-bottom: 20px;
            font-size: 1.1rem; font-weight: 600; letter-spacing: 0.5px;
            display: flex; align-items: center; gap: 10px; color: var(--text-main);
        }
        .card-header-banner.muted { border-bottom-color: var(--border-glass); }
        .card-header-icon {
            display: inline-flex; justify-content: center; align-items: center;
            width: 24px; height: 24px; background: rgba(255,255,255,0.1); border-radius: 6px;
            font-size: 12px; font-weight: bold; color: var(--text-muted);
        }

        /* Form */
        .mode-switcher {
            display: flex; width: 100%; border: 1px solid var(--border-glass); border-radius: 8px;
            overflow: hidden; margin-bottom: 24px; background: rgba(0,0,0,0.2);
        }
        .mode-tab {
            flex: 1; text-align: center; padding: 12px 0; font-weight: 500; font-size: 14px;
            cursor: pointer; color: var(--text-muted); transition: all 0.2s; border-right: 1px solid var(--border-glass);
        }
        .mode-tab:last-child { border-right: none; }
        .mode-tab:hover { background: rgba(255,255,255,0.1); color: var(--text-main); }
        .mode-tab.active { background-color: var(--primary); color: white; }

        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 14px; font-weight: 600; color: var(--text-main); margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;}
        .educational-text { display: block; font-size: 13px; color: var(--text-muted); margin-bottom: 8px; font-weight: 400; line-height: 1.4; }
        
        input[type=text], textarea, select {
            width: 100%; padding: 14px; background: rgba(0,0,0,0.4); border: 1px solid var(--border-glass);
            color: var(--text-main); font-size: 14px; border-radius: 6px;
            transition: all 0.2s; font-family: var(--font-main);
        }
        select option { background: var(--bg-base); color: var(--text-main); }
        input.mono-font, textarea.mono-font { font-family: 'JetBrains Mono', monospace; }
        textarea { resize: vertical; min-height: 120px; line-height: 1.6; }
        textarea.full-height { min-height: calc(100% - 100px); height: 300px; }
        input[type=text]:focus, textarea:focus, select:focus { outline: none; border-color: var(--primary); background: rgba(0,0,0,0.6); box-shadow: 0 0 15px rgba(var(--primary-rgb), 0.2); }
        
        .key-row { display: flex; gap: 8px; align-items: stretch; }
        .key-row .form-group { flex: 1; margin-bottom: 0; }
        .key-row .btn { white-space: nowrap; }

        /* Button */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 14px 20px; font-size: 15px; font-family: inherit; font-weight: 600; 
            cursor: pointer; transition: all .2s; border-radius: 6px; border: none;
        }
        .btn:disabled { opacity: .6; cursor: not-allowed; }
        .btn-primary { background: var(--primary); color: white; width: 100%; box-shadow: 0 4px 15px rgba(var(--primary-rgb), 0.4); }
        .btn-primary:not(:disabled):hover { background: var(--primary-hover); transform: translateY(-1px); }
        .btn-outline { background: transparent; border: 1px solid var(--border-glass); color: var(--text-main); }
        .btn-outline:not(:disabled):hover { background: rgba(255,255,255,0.1); }
        .btn-sm { padding: 6px 12px; font-size: 13px; }

        /* Result Area */
        .result-box {
            font-size: 14px; font-family: 'JetBrains Mono', monospace;
            background: rgba(0,0,0,0.6); border: 1px solid var(--border-glass); border-radius: 6px;
            padding: 16px; word-break: break-all; white-space: pre-wrap; color: var(--text-main);
            min-height: 100px; line-height: 1.5; margin-bottom: 16px;
            user-select: all;
        }
        .result-box.error { border-color: var(--primary); background: rgba(var(--primary-rgb), 0.1); color: #ff6b6b; min-height: auto; }
        
        .empty-state {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            height: 100%; color: var(--text-muted); text-align: center; padding: 40px 20px;
        }
        .empty-state svg { width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.5; }

        /* ==================================================
           CHACHA20 SPECIFIC STYLES
           ================================================== */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.85); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            display: flex; justify-content: center; align-items: center;
            z-index: 1000; padding: 20px;
            opacity: 0; pointer-events: none; transition: opacity 0.3s;
        }
        .modal-overlay.show { opacity: 1; pointer-events: auto; }
        .modal-content {
            background: rgba(20, 20, 20, 0.7); border: 1px solid var(--border-glass);
            border-radius: 16px; width: 100%; max-width: 1200px; max-height: 90vh;
            display: flex; flex-direction: column; overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.8), inset 0 1px 0 rgba(255,255,255,0.1);
            transform: scale(0.95); transition: transform 0.3s;
        }
        .modal-overlay.show .modal-content { transform: scale(1); }
        .modal-header { padding: 20px 30px; border-bottom: 1px solid var(--border-glass); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.3); }
        .modal-title { font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 12px; }
        .modal-close { background: transparent; border: none; color: var(--text-muted); font-size: 24px; cursor: pointer; transition: color 0.2s; }
        .modal-close:hover { color: white; }
        .modal-body { display: grid; grid-template-columns: 1fr; gap: 30px; padding: 30px; overflow-y: auto; }
        @media (min-width: 900px) { .modal-body { grid-template-columns: 1.5fr 1fr; } }
        .matrix-container { background: rgba(0,0,0,0.4); border-radius: 12px; padding: 24px; border: 1px solid var(--border-glass); }
        .state-matrix { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 24px; }
        .state-cell {
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 6px;
            padding: 24px 8px 16px; text-align: center; color: var(--text-main);
            font-family: 'JetBrains Mono', monospace; font-size: 14px; font-weight: bold;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative;
        }
        .state-cell.changed {
            background: rgba(229, 9, 20, 0.85); border-color: var(--primary-hover);
            transform: scale(1.08); box-shadow: 0 0 20px rgba(var(--primary-rgb), 0.6); z-index: 2; color: white;
        }
        .state-cell .index { color: var(--text-muted); font-size: 11px; display: block; margin-bottom: 6px; font-family: var(--font-main); font-weight: normal; }
        .legend-constant { border-bottom: 3px solid #888; }
        .legend-key { border-bottom: 3px solid #e50914; }
        .legend-counter { border-bottom: 3px solid #3b82f6; }
        .legend-nonce { border-bottom: 3px solid #46d369; }
        .color-dots { display: flex; gap: 4px; justify-content: center; position: absolute; top: 6px; left: 0; right: 0; }
        .dot { width: 6px; height: 6px; border-radius: 50%; box-shadow: 0 0 4px rgba(0,0,0,0.5); }
        .dot-constant { background: #888; }
        .dot-key { background: #e50914; box-shadow: 0 0 6px #e50914; }
        .dot-counter { background: #3b82f6; box-shadow: 0 0 6px #3b82f6; }
        .dot-nonce { background: #46d369; box-shadow: 0 0 6px #46d369; }
        .narration-panel { background: rgba(255,255,255,0.02); border-radius: 12px; padding: 24px; border: 1px solid var(--border-glass); display: flex; flex-direction: column; }
        .story-title { font-size: 1.2rem; font-weight: 700; color: var(--primary); margin-bottom: 16px; }
        .story-text { font-size: 1rem; color: var(--text-main); line-height: 1.6; margin-bottom: 24px; font-weight: 300; }
        .arx-box { background: rgba(0,0,0,0.5); border-radius: 8px; padding: 16px; margin-bottom: 20px; border-left: 4px solid var(--primary); }
        .arx-title { font-weight: 600; margin-bottom: 8px; font-size: 14px; }
        .arx-desc { font-size: 13px; color: var(--text-muted); }
        .arx-micro { background: rgba(0,0,0,0.5); border-radius: 10px; padding: 16px; margin-bottom: 16px; border: 1px solid var(--border-glass); }
        .arx-micro-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .arx-micro-nav { display: flex; gap: 6px; align-items: center; }
        .arx-micro-nav button { background: rgba(255,255,255,0.08); border: 1px solid var(--border-glass); color: var(--text-muted); border-radius: 4px; padding: 4px 10px; cursor: pointer; font-size: 12px; transition: all .2s; }
        .arx-micro-nav button:hover:not(:disabled) { background: rgba(255,255,255,0.15); color: white; }
        .arx-micro-nav button:disabled { opacity: .3; cursor: not-allowed; }
        .arx-step-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; }
        .arx-step-badge.add { background: rgba(0,200,255,0.15); color: #00c8ff; border: 1px solid rgba(0,200,255,0.3); }
        .arx-step-badge.xor { background: rgba(255,100,200,0.15); color: #ff64c8; border: 1px solid rgba(255,100,200,0.3); }
        .arx-step-badge.rot { background: rgba(255,180,0,0.15); color: #ffb400; border: 1px solid rgba(255,180,0,0.3); }
        .arx-op-desc { font-size: 13px; font-weight: 600; color: white; font-family: 'JetBrains Mono', monospace; margin-bottom: 10px; }
        .arx-row { display: flex; flex-direction: column; gap: 4px; margin-bottom: 6px; }
        .arx-row-label { font-size: 11px; color: var(--text-muted); font-weight: 500; }
        .arx-hex-val { font-family: 'JetBrains Mono', monospace; font-size: 13px; color: white; font-weight: 600; }
        .arx-bin-row { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 1px; word-break: break-all; padding: 4px 6px; border-radius: 4px; background: rgba(0,0,0,0.4); }
        .arx-bin-row.src { color: #888; }
        .arx-bin-row.result { color: #4ade80; }
        .arx-separator { text-align: center; font-size: 16px; font-weight: 700; padding: 2px 0; }
        .arx-separator.add-color { color: #00c8ff; }
        .arx-separator.xor-color { color: #ff64c8; }
        .arx-separator.rot-color { color: #ffb400; }
        .arx-result-line { border-top: 1px dashed rgba(255,255,255,0.15); padding-top: 6px; margin-top: 2px; }
        .round-nav-modal { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; }
        .round-dot { width: 32px; height: 32px; display:flex; align-items:center; justify-content:center; border-radius: 50%; font-size: 12px; font-weight: 600; background: rgba(255,255,255,0.05); color: var(--text-muted); cursor: pointer; transition: all 0.2s; border: 2px solid transparent; }
        .round-dot:hover { background: rgba(255,255,255,0.2); color: white; }
        .round-dot.active { background: white; color: black; border-color: white; transform: scale(1.1); }
        .spinner { width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: white; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }
        
        .drop-zone { border: 2px dashed rgba(255,255,255,0.15); border-radius: 12px; padding: 40px 20px; text-align: center; cursor: pointer; transition: all 0.3s; background: rgba(0,0,0,0.2); display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 200px; position: relative; }
        .drop-zone:hover { border-color: var(--primary); background: rgba(var(--primary-rgb),0.05); }
        .drop-zone.dragover { border-color: var(--primary); background: rgba(var(--primary-rgb),0.1); transform: scale(1.02); }
        .drop-zone-icon { font-size: 48px; margin-bottom: 12px; opacity: 0.6; }
        .drop-zone-text { color: var(--text-muted); font-size: 14px; }
        .drop-zone-text strong { color: var(--primary); }
        .drop-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .file-info-card { background: rgba(255,255,255,0.05); border: 1px solid var(--border-glass); border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; gap: 16px; }
        .file-info-icon { font-size: 36px; }
        .file-info-details { flex: 1; }
        .file-info-name { font-weight: 600; font-size: 15px; word-break: break-all; }
        .file-info-meta { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
        .file-remove { background: none; border: none; color: #ff6b6b; cursor: pointer; font-size: 20px; padding: 4px 8px; transition: opacity .2s; }
        .file-remove:hover { opacity: 0.7; }
        .download-card { background: rgba(var(--primary-rgb),0.08); border: 1px solid rgba(var(--primary-rgb),0.3); border-radius: 12px; padding: 24px; text-align: center; }
        .download-card .download-icon { font-size: 48px; margin-bottom: 12px; }
        .download-card .download-filename { font-weight: 600; font-size: 16px; margin-bottom: 4px; word-break: break-all; }
        .download-card .download-size { font-size: 13px; color: var(--text-muted); margin-bottom: 16px; }
        .btn-download { background: var(--primary); color: white; border: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 15px; cursor: pointer; transition: all .2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-download:hover { background: var(--primary-hover); transform: translateY(-1px); }
        .key-display { background: rgba(0,0,0,0.4); border: 1px solid var(--border-glass); border-radius: 8px; padding: 12px 16px; margin-top: 16px; text-align: left; }
        .key-display label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 2px; }
        .key-display .key-value { font-family: 'JetBrains Mono', monospace; font-size: 12px; word-break: break-all; color: #fbbf24; }
        .file-sub-mode { display: flex; gap: 8px; margin-bottom: 16px; }
        .file-sub-tab { flex: 1; text-align: center; padding: 8px; font-size: 13px; font-weight: 500; border-radius: 6px; cursor: pointer; transition: all .2s; background: rgba(255,255,255,0.05); color: var(--text-muted); border: 1px solid transparent; }
        .file-sub-tab:hover { color: white; background: rgba(255,255,255,0.1); }
        .file-sub-tab.active { background: rgba(var(--primary-rgb),0.2); color: white; border-color: var(--primary); }

        /* ==================================================
           CAESAR SPECIFIC STYLES
           ================================================== */
        .shift-slider{display:flex;align-items:center;gap:12px;margin-bottom:8px}
        .shift-slider input[type=range]{flex:1;-webkit-appearance:none;height:6px;background:rgba(255,255,255,.1);border-radius:3px;outline:none}
        .shift-slider input[type=range]::-webkit-slider-thumb{-webkit-appearance:none;width:22px;height:22px;border-radius:50%;background:var(--primary);cursor:pointer;box-shadow:0 0 10px rgba(var(--primary-rgb),.5)}
        .shift-value{background:var(--primary);color:white;padding:6px 14px;border-radius:6px;font-weight:700;font-size:18px;min-width:48px;text-align:center}
        .alpha-table{display:flex;flex-direction:column;gap:2px;margin-top:12px;overflow-x:auto}
        .alpha-row{display:flex;gap:2px}
        .alpha-row-label{width:60px;min-width:60px;padding:6px 8px;font-size:11px;font-weight:600;display:flex;align-items:center;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted)}
        .alpha-cell{width:32px;min-width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-family:'Courier New',monospace;font-size:13px;font-weight:600;border-radius:4px;transition:all .3s}
        .alpha-cell.original{background:rgba(255,255,255,.05);color:var(--text-muted)}
        .alpha-cell.shifted{background:rgba(var(--primary-rgb),.15);color:var(--primary);border:1px solid rgba(var(--primary-rgb),.3)}
        .bf-table{max-height:400px;overflow-y:auto;border-radius:8px;border:1px solid var(--border-glass)}
        .bf-row{display:flex;align-items:center;padding:10px 16px;border-bottom:1px solid rgba(255,255,255,.05);transition:background .2s;gap:12px}
        .bf-row:hover{background:rgba(255,255,255,.05)}
        .bf-shift{font-weight:700;color:var(--primary);min-width:60px;font-size:13px}
        .bf-text{font-family:'Courier New',monospace;font-size:13px;word-break:break-all;flex:1}
        .bf-row.match{background:rgba(var(--primary-rgb),.1);border-left:3px solid var(--primary)}
        .step-log{display:flex;flex-wrap:wrap;gap:4px;margin-top:12px}
        .step-char{display:flex;flex-direction:column;align-items:center;padding:8px 6px;border-radius:6px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.05);min-width:36px;transition:all .2s}
        .step-char:hover{border-color:var(--primary);background:rgba(var(--primary-rgb),.1)}
        .step-orig{font-size:16px;font-weight:600;color:var(--text-muted);font-family:'Courier New',monospace}
        .step-arrow{font-size:10px;color:rgba(255,255,255,.3);margin:2px 0}
        .step-result{font-size:16px;font-weight:700;color:var(--primary);font-family:'Courier New',monospace}
        .step-char.unchanged .step-result{color:var(--text-muted)}

        /* ==================================================
           RESPONSIVE MOBILE STYLES
           ================================================== */
        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 16px; padding: 16px 4%; }
            .navbar > div { flex-wrap: wrap; justify-content: center; }
            .hero { padding-bottom: 20px; text-align: center; }
            .hero h1 { font-size: 2.2rem; }
            .mode-switcher { flex-wrap: wrap; }
            .mode-tab { flex: 1 1 40%; min-width: 120px; border-bottom: 1px solid var(--border-glass); }
            .card { padding: 16px; }
            .card-header-banner { margin: -16px -16px 16px -16px; }
            .key-row { flex-direction: column; }
            textarea.full-height { height: 150px; min-height: 150px; }
            .pipeline-layout { gap: 12px; }
            .modal-header { padding: 16px; flex-direction: column; gap: 12px; text-align: center; }
            .modal-body { padding: 16px; gap: 16px; }
            .state-matrix { gap: 4px; margin-bottom: 16px; }
            .state-cell { padding: 12px 2px 8px; font-size: 11px; }
            .state-cell .index { font-size: 9px; margin-bottom: 2px; }
            .arx-micro-header { flex-direction: column; gap: 12px; align-items: flex-start; }
            .color-dots { display: none; }
        }
        /* ==================================================
           LIGHT MODE OVERRIDES
           ================================================== */
        body.light-mode {
            --bg-base: #F8FAFC;
            --text-main: #0F172A;
            --text-muted: #475569;
            --border-glass: #E2E8F0;
            --bg-glass: #FFFFFF;
        }
        body.light-mode .status-badge { background: #F1F5F9; border-color: #E2E8F0; }
        body.light-mode .mode-switcher { background: #F1F5F9; border-color: #E2E8F0; }
        body.light-mode .mode-tab { border-color: #E2E8F0; }
        body.light-mode .mode-tab:hover { background: #E2E8F0; color: var(--text-main); }
        body.light-mode input[type=text], body.light-mode textarea, body.light-mode select { background: #FFFFFF; color: var(--text-main); border-color: #CBD5E1; }
        body.light-mode input[type=text]:focus, body.light-mode textarea:focus, body.light-mode select:focus { background: #fff; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.2); }
        body.light-mode .result-box { background: #F8FAFC; border-color: #E2E8F0; }
        body.light-mode .result-box.error { background: rgba(var(--primary-rgb), 0.05); }
        body.light-mode .drop-zone { background: #F8FAFC; border-color: #CBD5E1; }
        body.light-mode .drop-zone:hover, body.light-mode .drop-zone.dragover { background: rgba(var(--primary-rgb),0.05); border-color: var(--primary); }
        body.light-mode .file-info-card { background: #FFFFFF; border-color: #E2E8F0; }
        body.light-mode .key-display { background: #F8FAFC; border-color: #E2E8F0; }
        body.light-mode .file-sub-tab { background: #F1F5F9; color: var(--text-muted); border-color: transparent; }
        body.light-mode .file-sub-tab:hover { background: #E2E8F0; color: var(--text-main); }
        body.light-mode .file-sub-tab.active { background: rgba(var(--primary-rgb),0.1); color: var(--primary); border-color: var(--primary); }
        body.light-mode .btn-outline { border-color: #CBD5E1; color: var(--text-main); }
        body.light-mode .btn-outline:not(:disabled):hover { background: #F1F5F9; }
        body.light-mode .modal-overlay { background: rgba(255,255,255,0.85); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); }
        body.light-mode .modal-content { background: #FFFFFF; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border-color: #E2E8F0; }
        body.light-mode .modal-header { background: #F8FAFC; border-bottom-color: #E2E8F0; }
        body.light-mode .modal-close { color: var(--text-main); opacity: 0.5; }
        body.light-mode .modal-close:hover { opacity: 1; }
        body.light-mode .matrix-container { background: #F8FAFC; border-color: #E2E8F0; }
        body.light-mode .state-cell { background: #FFFFFF; border-color: #E2E8F0; }
        body.light-mode .state-cell.changed { color: white; border-color: var(--primary); background-color: var(--primary); }
        body.light-mode .narration-panel { background: #F8FAFC; border-color: #E2E8F0; }
        body.light-mode .arx-box { background: #FFFFFF; border-color: #E2E8F0; border-left-color: var(--primary); }
        body.light-mode .arx-micro { background: #FFFFFF; border-color: #E2E8F0; }
        body.light-mode .arx-micro-nav button { background: #F1F5F9; border-color: #E2E8F0; color: var(--text-muted); }
        body.light-mode .arx-micro-nav button:hover:not(:disabled) { background: #E2E8F0; color: var(--text-main); }
        body.light-mode .arx-op-desc { color: var(--text-main); }
        body.light-mode .arx-hex-val { color: var(--text-main); }
        body.light-mode .arx-bin-row { background: #F1F5F9; color: #444; }
        body.light-mode .arx-bin-row.result { color: #059669; }
        body.light-mode .arx-row-label[style*="color: white"] { color: var(--text-main) !important; }
        body.light-mode .arx-result-line { border-top-color: #E2E8F0; }
        body.light-mode .round-dot { background: #F1F5F9; color: var(--text-main); }
        body.light-mode .round-dot:hover { background: #E2E8F0; }
        body.light-mode .round-dot.active { background: var(--text-main); color: #FFFFFF; border-color: var(--text-main); }
        body.light-mode .shift-slider input[type=range] { background: #E2E8F0; }
        body.light-mode .alpha-cell.original { background: #F1F5F9; color: var(--text-main); }
        body.light-mode .bf-row { border-bottom-color: #E2E8F0; }
        body.light-mode .bf-row:hover { background: #F8FAFC; }
        body.light-mode #brute-terminal { background: #FFFFFF; box-shadow: none; border-color: #E2E8F0; }
        body.light-mode .step-char { background: #FFFFFF; border-color: #E2E8F0; }
        body.light-mode .step-char:hover { background: rgba(var(--primary-rgb),0.05); border-color: var(--primary); }
        body.light-mode .step-arrow { color: #94A3B8; }
        /* Fix terminal texts */
        body.light-mode #brute-terminal div[style*="color: #e2e8f0"] { color: var(--text-main) !important; }
        body.light-mode #brute-terminal div[style*="color: white"] { color: var(--text-main) !important; }
        body.light-mode .empty-state p[style*="color: white"] { color: var(--text-main) !important; }
    </style>
</head>
<body x-data="simulatorApp()" x-init="init()" :class="[themeClass, isLightMode ? 'light-mode' : '']" :style="(showModal && algorithm === 'chacha20') ? 'overflow: hidden;' : ''">

    <!-- Navbar -->
    <nav class="navbar">
        <a href="/" class="logo"><span>Naka</span>motoX</a>
        <div style="display:flex; align-items:center; gap: 16px;">
            <button @click="toggleTheme()" class="btn btn-outline btn-sm" style="border-radius: 20px; padding: 6px 12px; font-size: 16px; border-color: var(--border-glass);" :title="isLightMode ? 'Switch to Dark Mode' : 'Switch to Light Mode'">
                <span x-show="!isLightMode">☀️</span>
                <span x-show="isLightMode">🌙</span>
            </button>
            <a href="{{ route('simulator.learn') }}" style="color:var(--text-main); text-decoration:none; font-weight:600; font-size:14px; display:flex; align-items:center; gap:6px; transition:color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-main)'">
                <span>📖</span> The Story of Kripto
            </a>
            <div class="status-badge" :class="serviceStatus === 'Online' ? 'online' : 'offline'">
                <span x-show="serviceStatus === 'Online'">🟢</span>
                <span x-show="serviceStatus !== 'Online'">🔴</span>
                Engine: <span x-text="serviceStatus">Checking...</span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <h1>
            <span x-show="algorithm === 'chacha20'">ChaCha20</span>
            <span x-show="algorithm === 'caesar'">Caesar Cipher</span>
            Simulator
        </h1>
        <p x-show="algorithm === 'chacha20'">Visualisasi interaktif algoritma <em>stream cipher</em> modern yang mengamankan internet saat ini. Eksplorasi setiap bit yang berubah pada matriks <em>state</em>.</p>
        <p x-show="algorithm === 'caesar'">Algoritma substitusi klasik. Setiap huruf digeser sebanyak N posisi dalam alfabet. Sederhana namun menjadi fondasi kriptografi modern.</p>
    </section>

    <!-- Main Container -->
    <main class="container">
        <div class="pipeline-layout">

            <!-- COLUMN 1: INPUT -->
            <div class="column-block">
                <div class="card h-full">
                    <div class="card-header-banner muted">
                        <span class="card-header-icon">1</span>
                        <span>INPUT DATA</span>
                    </div>

                    <div class="form-group" x-show="mode === 'encrypt' || mode === 'steps'" style="height: 100%;">
                        <label>Plaintext</label>
                        <span class="educational-text">Masukkan teks rahasia yang ingin diproses.</span>
                        <textarea class="full-height" x-model="plaintext" placeholder="Contoh: The quick brown fox jumps over the lazy dog."></textarea>
                    </div>

                    <div class="form-group" x-show="mode === 'decrypt' || (mode === 'brute' && algorithm === 'caesar')" style="height: 100%;">
                        <label>Ciphertext <span x-show="algorithm === 'chacha20'">(Hex)</span></label>
                        <span class="educational-text" x-text="(mode === 'brute') ? 'Masukkan teks terenkripsi untuk di-crack.' : 'Masukkan data yang sudah terenkripsi.'"></span>
                        <textarea class="mono-font full-height" x-model="ciphertextInput" placeholder="Contoh: a1b2c3d4e5f6..."></textarea>
                    </div>

                    <!-- File Mode: Drop Zone (Only for ChaCha20 currently) -->
                    <div x-show="mode === 'file' && algorithm === 'chacha20'" style="height: 100%; display: flex; flex-direction: column;">
                        <label x-text="fileMode === 'encrypt' ? 'File untuk Dienkripsi' : 'File Terenkripsi untuk Didekripsi'"></label>
                        <span class="educational-text" x-text="fileMode === 'encrypt' ? 'Drag & drop atau pilih file (maks 5 MB).' : 'Upload file terenkripsi yang ingin didekripsi.'"></span>

                        <div x-show="!selectedFile" class="drop-zone" :class="{'dragover': isDragging}"
                             @dragover.prevent="isDragging = true"
                             @dragleave.prevent="isDragging = false"
                             @drop.prevent="isDragging = false; handleFileDrop($event)">
                            <div class="drop-zone-icon">📁</div>
                            <div class="drop-zone-text">Drag & drop file di sini<br>atau <strong>klik untuk browse</strong></div>
                            <div style="font-size:12px; color:var(--text-muted); margin-top:8px;">Maks. 5 MB</div>
                            <input type="file" @change="handleFileSelect($event)">
                        </div>

                        <div x-show="selectedFile" class="file-info-card">
                            <div class="file-info-icon">📄</div>
                            <div class="file-info-details">
                                <div class="file-info-name" x-text="selectedFile?.name"></div>
                                <div class="file-info-meta" x-text="formatFileSize(selectedFile?.size) + ' • ' + (selectedFile?.type || 'unknown type')"></div>
                            </div>
                            <button class="file-remove" @click="removeFile()" title="Hapus file">✕</button>
                        </div>
                    </div>
                </div>
                <div class="card h-full">
                    <div class="card-header-banner">
                        <span class="card-header-icon">2</span>
                        <span>KONFIGURASI</span>
                    </div>

                    <div class="form-group">
                        <label>Algorithm Selection</label>
                        <select x-model="algorithm" @change="onAlgorithmChange()" style="font-weight: 600; font-size: 15px; color: var(--primary); background: rgba(0,0,0,0.6);">
                            <option value="chacha20">🔐 ChaCha20 (Stream Cipher)</option>
                            <option value="caesar">🏛️ Caesar Cipher (Substitution)</option>
                        </select>
                    </div>

                    <div class="mode-switcher">
                        <div class="mode-tab" :class="{ active: mode === 'encrypt' }" @click="mode = 'encrypt'; resetResult()">Encrypt</div>
                        <div class="mode-tab" :class="{ active: mode === 'decrypt' }" @click="mode = 'decrypt'; resetResult()">Decrypt</div>
                        
                        <div x-show="algorithm === 'chacha20'" class="mode-tab" :class="{ active: mode === 'steps' }" @click="mode = 'steps'; resetResult()">Visualize</div>
                        <div x-show="algorithm === 'chacha20'" class="mode-tab" :class="{ active: mode === 'file' }" @click="mode = 'file'; resetResult()">📁 File</div>
                        
                        <div x-show="algorithm === 'caesar'" class="mode-tab" :class="{ active: mode === 'brute' }" @click="mode = 'brute'; resetResult()">🔓 Brute Force</div>
                    </div>

                    <!-- File sub-mode (encrypt/decrypt) -->
                    <div x-show="mode === 'file' && algorithm === 'chacha20'" class="file-sub-mode">
                        <div class="file-sub-tab" :class="{active: fileMode === 'encrypt'}" @click="fileMode = 'encrypt'; resetResult(); removeFile()">🔒 Encrypt File</div>
                        <div class="file-sub-tab" :class="{active: fileMode === 'decrypt'}" @click="fileMode = 'decrypt'; resetResult(); removeFile()">🔓 Decrypt File</div>
                    </div>

                    <!-- ChaCha20 Specific Config -->
                    <template x-if="algorithm === 'chacha20' && mode !== 'file'">
                        <div>
                            <div class="form-group">
                                <label>Secret Key (256-bit)</label>
                                <span class="educational-text">Kunci utama untuk enkripsi/dekripsi. Harus tepat 64 karakter hex.</span>
                                <div class="key-row">
                                    <div class="form-group">
                                        <input type="text" class="mono-font" x-model="key" placeholder="64 hex chars..." maxlength="64">
                                        <p x-show="keyError" x-text="keyError" style="color:#ff6b6b; font-size:12px; margin-top:4px;"></p>
                                    </div>
                                    <button class="btn btn-outline" @click="generateKey()" :disabled="loading" title="Generate Random Key & Nonce">
                                        🎲 <span class="hide-mobile">Generate</span>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Nonce (96-bit)</label>
                                <span class="educational-text">Nilai acak unik (24 hex chars) agar enkripsi tidak menghasilkan pola berulang.</span>
                                <input type="text" class="mono-font" x-model="nonce" placeholder="24 hex chars..." maxlength="24">
                                <p x-show="nonceError" x-text="nonceError" style="color:#ff6b6b; font-size:12px; margin-top:4px;"></p>
                            </div>

                            <div class="form-group">
                                <label>Initial Counter</label>
                                <span class="educational-text">Penanda urutan blok data. Mencegah tabrakan pola.</span>
                                <input type="text" x-model="counter" placeholder="1" style="font-family: var(--font-main);">
                            </div>
                        </div>
                    </template>

                    <!-- Caesar Specific Config -->
                    <template x-if="algorithm === 'caesar'">
                        <div>
                            <div class="form-group" x-show="mode !== 'brute'">
                                <label>Shift Key (0-25)</label>
                                <span class="educational-text">Jumlah pergeseran huruf. Contoh klasik: 3 (digunakan Julius Caesar).</span>
                                <div class="shift-slider">
                                    <input type="range" min="0" max="25" x-model.number="shift">
                                    <div class="shift-value" x-text="shift"></div>
                                </div>
                            </div>

                            <!-- Mini Alphabet Preview -->
                            <div class="form-group" x-show="mode !== 'brute'">
                                <label>Tabel Substitusi</label>
                                <span class="educational-text">Pemetaan huruf asli → huruf terenkripsi.</span>
                                <div class="alpha-table">
                                    <div class="alpha-row">
                                        <div class="alpha-row-label">Asli</div>
                                        <template x-for="(letter, i) in 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('')" :key="'o'+i">
                                            <div class="alpha-cell original" x-text="letter"></div>
                                        </template>
                                    </div>
                                    <div class="alpha-row">
                                        <div class="alpha-row-label">Sandi</div>
                                        <template x-for="(letter, i) in shiftedAlphabet" :key="'s'+i">
                                            <div class="alpha-cell shifted" x-text="letter"></div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div x-show="mode === 'brute'" class="form-group">
                                <label>Mode Brute Force</label>
                                <span class="educational-text">Mencoba semua 26 kemungkinan shift untuk memecahkan ciphertext tanpa mengetahui kunci.</span>
                                <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);border-radius:8px;padding:16px;margin-top:8px">
                                    <div style="font-size:28px;text-align:center;margin-bottom:8px">🔓</div>
                                    <div style="text-align:center;font-size:13px;color:var(--text-muted)">Caesar cipher hanya memiliki <strong style="color:var(--primary)">26 kemungkinan kunci</strong>, sehingga mudah dipecahkan dengan mencoba semuanya.</div>
                                </div>
                            </div>

                            <div class="form-group" x-show="mode === 'encrypt' || mode === 'decrypt'">
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;text-transform:none;letter-spacing:0">
                                    <input type="checkbox" x-model="showStepsCaesar" style="width:auto;accent-color:var(--primary)">
                                    Tampilkan Visualisasi Step-by-Step
                                </label>
                            </div>
                        </div>
                    </template>

                    <div style="flex-grow: 1;"></div>

                    <button class="btn btn-primary" style="margin-top: 24px; padding: 18px;" @click="run()" :disabled="loading">
                        <span x-show="loading" class="spinner"></span>
                        <span x-show="!loading && mode === 'encrypt'">Execute Pipeline →</span>
                        <span x-show="!loading && mode === 'decrypt'">Execute Pipeline →</span>
                        <span x-show="!loading && mode === 'steps' && algorithm === 'chacha20'">Buka Visualizer Edukatif 🎥</span>
                        <span x-show="!loading && mode === 'file' && fileMode === 'encrypt' && algorithm === 'chacha20'">🔒 Encrypt File →</span>
                        <span x-show="!loading && mode === 'file' && fileMode === 'decrypt' && algorithm === 'chacha20'">🔓 Decrypt File →</span>
                        <span x-show="!loading && mode === 'brute' && algorithm === 'caesar'">🔓 Crack Semua Shift →</span>
                    </button>
                </div>
            </div>

            <!-- RIGHT COLUMN: OUTPUT -->
            <div class="column-block" style="position: sticky; top: 100px; align-self: start;">
                <div class="card h-full" style="display: flex; flex-direction: column;">
                    <div class="card-header-banner muted">
                        <span class="card-header-icon">3</span>
                        <span>OUTPUT RESULT</span>
                    </div>

                    <div x-show="error" class="result-box error"><strong>Error:</strong> <span x-text="error"></span></div>

                    <div x-show="!result && !bruteResult && !stepsData && !fileResult && !error && !isBruteCracking" class="empty-state">
                        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M19,3H5C3.89,3 3,3.89 3,5V19C3,20.1 3.9,21 5,21H19C20.1,21 21,20.1 21,19V5C21,3.89 20.1,3 19,3M19,19H5V5H19V19M17,17H7V15H17V17M17,13H7V11H17V13M17,9H7V7H17V9Z" /></svg>
                        <p x-show="mode !== 'steps' && mode !== 'file'">Menunggu eksekusi *pipeline*.<br>Hasil akan ditampilkan di sini.</p>
                        <p x-show="mode === 'steps'">Klik tombol di kolom tengah untuk membuka jendela visualizer pop-up.</p>
                        <p x-show="mode === 'file'">Upload file dan klik tombol eksekusi.<br>Hasil download akan muncul di sini.</p>
                    </div>

                    <!-- Text Results -->
                    <div x-show="result && mode !== 'steps' && mode !== 'file' && mode !== 'brute'" style="flex-grow: 1;">
                        <template x-if="mode === 'encrypt' && result">
                            <div>
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                    <label style="margin-bottom:0;">Ciphertext <span x-show="algorithm === 'chacha20'">(Hex)</span></label>
                                    <button class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 11px;" @click="copyText(algorithm === 'chacha20' ? result.ciphertext_hex : result.ciphertext)">📋 Salin</button>
                                </div>
                                <div class="result-box" x-text="algorithm === 'chacha20' ? result.ciphertext_hex : result.ciphertext" title="Klik untuk menyeleksi semua teks"></div>
                                
                                <template x-if="algorithm === 'chacha20'">
                                    <div>
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                            <label style="margin-bottom:0;">Ciphertext (Base64)</label>
                                            <button class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 11px;" @click="copyText(result.ciphertext_base64)">📋 Salin</button>
                                        </div>
                                        <div class="result-box" x-text="result.ciphertext_base64" title="Klik untuk menyeleksi semua teks"></div>
                                    </div>
                                </template>

                                <template x-if="algorithm === 'caesar'">
                                    <div style="display:flex;gap:8px">
                                        <button class="btn btn-outline btn-sm" style="flex:1" @click="copyText(result.ciphertext)">📋 Salin</button>
                                        <button class="btn btn-outline btn-sm" style="flex:1" @click="useForDecrypt()">🔄 Decrypt Ini</button>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="mode === 'decrypt' && result">
                            <div>
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                    <label style="margin-bottom:0;">Plaintext Asli</label>
                                    <button class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 11px;" @click="copyText(result.plaintext)">📋 Salin</button>
                                </div>
                                <div class="result-box" x-text="result.plaintext" style="font-family: var(--font-main); font-size: 16px;" title="Klik untuk menyeleksi semua teks"></div>
                                <template x-if="algorithm === 'chacha20'">
                                    <div>
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px; margin-top:16px;">
                                            <label style="margin-bottom:0;">Plaintext (Hex)</label>
                                            <button class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 11px;" @click="copyText(result.plaintext_hex)">📋 Salin</button>
                                        </div>
                                        <div class="result-box" x-text="result.plaintext_hex" title="Klik untuk menyeleksi semua teks"></div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Caesar Step-by-Step Visualization -->
                        <template x-if="algorithm === 'caesar' && result && result.step_logs && result.step_logs.length > 0">
                            <div style="margin-top:20px">
                                <label>Visualisasi Substitusi</label>
                                <span class="educational-text">Setiap huruf diganti sesuai tabel substitusi. Karakter non-huruf tidak berubah.</span>
                                <div class="step-log">
                                    <template x-for="(step, i) in result.step_logs" :key="i">
                                        <div class="step-char" :class="{'unchanged': !step.is_letter}">
                                            <span class="step-orig" x-text="step.original_char"></span>
                                            <span class="step-arrow" x-text="step.is_letter ? '↓' : '·'"></span>
                                            <span class="step-result" x-text="step.shifted_char"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- ChaCha20 Steps Result -->
                    <div x-show="mode === 'steps' && stepsData && algorithm === 'chacha20'" class="empty-state">
                        <div style="font-size: 40px; margin-bottom: 16px;">🎓</div>
                        <p style="color: white; font-weight: 500;">Visualizer Berhasil Di-generate!</p>
                        <p style="font-size: 13px; margin-top: 8px;">Jendela pop-up sedang terbuka. Jika Anda tidak sengaja menutupnya, Anda bisa membukanya kembali.</p>
                        <button class="btn btn-outline" style="margin-top: 20px;" @click="showModal = true">Buka Kembali Visualizer</button>
                    </div>

                    <!-- File Result: Download Card (ChaCha20) -->
                    <div x-show="mode === 'file' && fileResult && algorithm === 'chacha20'" style="flex-grow: 1;">
                        <div class="download-card">
                            <div class="download-icon">✅</div>
                            <div class="download-filename" x-text="fileResult?.result_filename"></div>
                            <div class="download-size" x-text="formatFileSize(fileResult?.content_length)"></div>
                            <button class="btn-download" @click="downloadFileResult()">
                                ⬇️ Download File
                            </button>
                        </div>

                        <template x-if="fileResult?.key_hex">
                            <div>
                                <div class="key-display">
                                    <label>⚠️ SIMPAN KEY & NONCE INI — diperlukan untuk dekripsi!</label>
                                </div>
                                <div class="key-display">
                                    <label>Secret Key (256-bit)</label>
                                    <div class="key-value" x-text="fileResult.key_hex"></div>
                                </div>
                                <div class="key-display" style="margin-top: 8px;">
                                    <label>Nonce (96-bit)</label>
                                    <div class="key-value" x-text="fileResult.nonce_hex"></div>
                                </div>
                                <button class="btn btn-outline btn-sm" style="margin-top: 12px; width: 100%;" @click="copyText(`Key: ${fileResult.key_hex}\nNonce: ${fileResult.nonce_hex}`)">📋 Salin Key & Nonce</button>
                            </div>
                        </template>
                    </div>

                    <!-- Caesar Brute Force Result (Terminal Style) -->
                    <div x-show="mode === 'brute' && (bruteResult || isBruteCracking) && algorithm === 'caesar'" style="flex-grow:1; display:flex; flex-direction:column;">
                        <label x-show="isBruteCracking" style="color: #f59e0b;">📡 System is Cracking...</label>
                        <label x-show="!isBruteCracking" style="color: #4ade80;">✅ 26 Kemungkinan Ditemukan!</label>
                        <span class="educational-text">Terminal Log (Brute Force)</span>
                        
                        <div id="brute-terminal" style="flex-grow: 1; background: rgba(0,0,0,0.8); padding: 16px; border-radius: 8px; border: 1px solid var(--border-glass); font-family: 'JetBrains Mono', monospace; overflow-y: auto; max-height: 400px; box-shadow: inset 0 0 20px rgba(0,0,0,1);">
                            <div style="color: #4ade80; margin-bottom: 12px; font-weight: bold;">$ initiating brute-force sequence...</div>
                            <template x-for="(line, index) in bruteLines" :key="index">
                                <div style="display: flex; gap: 12px; margin-bottom: 6px; font-size: 13px; padding: 4px; border-radius: 4px; cursor: pointer; transition: background 0.2s;"
                                     :style="selectedBfShift === line.shift ? 'background: rgba(245,158,11,0.2); border-left: 3px solid #f59e0b;' : 'border-left: 3px solid transparent;'"
                                     @click="selectedBfShift = line.shift">
                                    <span style="color: #f59e0b; font-weight: bold; width: 75px; flex-shrink: 0;" x-text="'[SHIFT ' + line.shift.toString().padStart(2, '0') + ']'"></span>
                                    <span style="color: #e2e8f0; word-break: break-all;" x-text="line.text"></span>
                                </div>
                            </template>
                            <div x-show="!isBruteCracking && bruteLines.length > 0" style="color: #4ade80; margin-top: 16px; font-weight: bold; border-top: 1px dashed rgba(74,222,128,0.3); padding-top: 12px;">
                                $ cracking complete. analyze the output above. <br>
                                <span style="font-size: 11px; color: var(--text-muted); font-weight: normal; margin-top: 6px; display: block;">(Klik pada baris yang memiliki makna untuk menandainya)</span>
                            </div>
                            <div x-show="isBruteCracking" style="color: white; font-weight: bold; font-size: 16px; margin-top: 8px;">_</div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <!-- FLOATING MODAL VISUALIZER (Only for ChaCha20) -->
    <div class="modal-overlay" :class="{ 'show': showModal && algorithm === 'chacha20' }" @click.self="showModal = false">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <span>🔍</span>
                    <div>
                        <div style="font-size: 18px;">ChaCha20 State Visualizer</div>
                        <div style="font-size: 13px; color: var(--text-muted); font-weight: normal; margin-top: 4px;" x-text="currentRoundLabel"></div>
                    </div>
                </div>
                <button class="modal-close" @click="showModal = false" title="Tutup">&times;</button>
            </div>

            <div class="modal-body">
                <!-- Kiri: Matrix Visualizer -->
                <div>
                    <div class="matrix-container">
                        <div class="state-matrix">
                            <template x-for="(cell, index) in currentStateWords" :key="'cell-'+index+'-'+currentRoundIndex+'-'+microStepIdx">
                                <div class="state-cell" :class="{ 'changed': changedIndices.includes(index) }">
                                    <div class="color-dots">
                                        <template x-for="c in currentCellColorsArray[index]" :key="c">
                                            <div class="dot" :class="'dot-' + c"></div>
                                        </template>
                                    </div>
                                    <span class="index" x-text="'[' + index + ']'"></span>
                                    <span x-text="cell.replace('0x', '')"></span>
                                </div>
                            </template>
                        </div>
                        <div style="display: flex; gap: 16px; justify-content: center; font-size: 12px; font-weight: 500; color: var(--text-muted);">
                            <span class="legend-constant">Constant</span>
                            <span class="legend-key">Key</span>
                            <span class="legend-counter">Counter</span>
                            <span class="legend-nonce">Nonce</span>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Narasi Edukasi & ARX -->
                <div>
                    <div class="narration-panel" style="height: 100%;">
                        <div class="story-title">Apa yang sedang terjadi?</div>
                        <div class="story-text" x-html="getNarrationText()"></div>

                        <template x-if="currentMicroSteps && currentMicroSteps.length > 0">
                            <div class="arx-micro">
                                <div class="arx-micro-header">
                                    <div class="arx-title" style="margin:0;">⚡ Micro-Step (ARX)</div>
                                    <div class="arx-micro-nav">
                                        <button @click="prevMicroStep()" :disabled="microStepIdx <= 0">← Mundur</button>
                                        <span style="font-size:12px; color:white;" x-text="(microStepIdx + 1) + '/' + currentMicroSteps.length"></span>
                                        <button @click="nextMicroStep()" :disabled="microStepIdx >= currentMicroSteps.length - 1">Maju →</button>
                                    </div>
                                </div>

                                <template x-if="currentMicroSteps[microStepIdx]">
                                    <div style="animation: fadeIn 0.3s;">
                                        <div style="margin-bottom: 12px;">
                                            <span class="arx-step-badge" :class="currentMicroSteps[microStepIdx].op.toLowerCase()">
                                                <span x-text="currentMicroSteps[microStepIdx].op"></span>
                                            </span>
                                        </div>
                                        <div class="arx-op-desc" x-text="currentMicroSteps[microStepIdx].description"></div>

                                        <div class="arx-row">
                                            <span class="arx-row-label" x-text="currentMicroSteps[microStepIdx].op === 'ADD' ? 'Nilai Awal (Target)' : (currentMicroSteps[microStepIdx].op === 'XOR' ? 'Nilai Awal (Target)' : 'Nilai Awal')"></span>
                                            <div class="arx-hex-val" x-text="currentMicroSteps[microStepIdx].operand1_hex"></div>
                                            <div class="arx-bin-row src" x-text="currentMicroSteps[microStepIdx].operand1_bin"></div>
                                        </div>

                                        <div class="arx-separator" :class="currentMicroSteps[microStepIdx].op.toLowerCase() + '-color'" x-text="currentMicroSteps[microStepIdx].symbol"></div>

                                        <div class="arx-row" x-show="currentMicroSteps[microStepIdx].op !== 'ROT'">
                                            <span class="arx-row-label">Source Value</span>
                                            <div class="arx-hex-val" x-text="currentMicroSteps[microStepIdx].operand2_hex"></div>
                                            <div class="arx-bin-row src" x-text="currentMicroSteps[microStepIdx].operand2_bin"></div>
                                        </div>
                                        <div class="arx-row" x-show="currentMicroSteps[microStepIdx].op === 'ROT'">
                                            <span class="arx-row-label" style="color: #ffb400;">Rotasi bit ke kiri sebanyak <span x-text="currentMicroSteps[microStepIdx].shift"></span> posisi</span>
                                        </div>

                                        <div class="arx-result-line"></div>

                                        <div class="arx-row" style="margin-top: 8px;">
                                            <span class="arx-row-label" style="color: white;">Hasil Akhir</span>
                                            <div class="arx-hex-val" style="color: #4ade80;" x-text="currentMicroSteps[microStepIdx].result_hex"></div>
                                            <div class="arx-bin-row result" x-text="currentMicroSteps[microStepIdx].result_bin"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <div class="arx-box" x-show="currentMicroSteps.length === 0 && currentRound >= 0 && currentRound !== 'final'">
                            <div class="arx-title">⚡ Operasi Inti: ARX</div>
                            <div class="arx-desc">
                                Ronde ini terdiri dari 4 Quarter Round.<br>
                                Klik pada Quarter Round individual (tombol Maju) untuk melihat detail setiap operasi ARX.
                            </div>
                        </div>

                        <div style="flex-grow:1"></div>

                        <label style="font-size: 12px; color: var(--text-muted); margin-bottom: 8px; display:block;">Lompat Cepat ke Ronde:</label>
                        <div class="round-nav-modal">
                            <div class="round-dot" :class="{active: currentRound === -1}" @click="goToRound(-1)" title="Initial State">In</div>
                            <template x-if="stepsData && stepsData.round_summaries">
                                <template x-for="summary in stepsData.round_summaries" :key="summary.round">
                                    <div class="round-dot"
                                         :class="{active: currentRound === summary.round}"
                                         @click="goToRound(summary.round)"
                                         x-text="summary.round">
                                    </div>
                                </template>
                            </template>
                            <div class="round-dot" :class="{active: currentRound === 'final'}" @click="goToRound('final')" title="Final State">Fn</div>
                        </div>

                        <div style="display:flex; gap: 10px; margin-top: 20px;">
                            <button @click="currentRoundIndex--; updateVisualizer()" :disabled="currentRoundIndex <= 0" class="btn btn-outline" style="flex:1;">Prev Step</button>
                            <button @click="currentRoundIndex++; updateVisualizer()" :disabled="currentRoundIndex >= allRounds.length - 1" class="btn btn-primary" style="flex:1;">Next Step</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
function simulatorApp() {
    return {
        algorithm: 'chacha20',
        get themeClass() { return 'theme-' + this.algorithm; },

        apiUrl: '{{ config("app.url") }}',
        csrfToken: document.querySelector('meta[name=csrf-token]').content,
        mode: 'encrypt', // encrypt, decrypt, steps, file, brute
        loading: false,
        serviceStatus: 'Loading...',

        // Unified input
        plaintext: '',
        ciphertextInput: '',
        
        // ChaCha20 State
        key: '',
        nonce: '',
        counter: 1,
        keyError: null,
        nonceError: null,
        showModal: false,
        isLightMode: false,
        
        // ChaCha20 File State
        fileMode: 'encrypt',
        selectedFile: null,
        fileResult: null,
        isDragging: false,
        
        // Caesar State
        shift: 3,
        showStepsCaesar: false,
        bruteResult: null,
        selectedBfShift: -1,
        bruteLines: [],
        isBruteCracking: false,

        // Common Result
        error: null,
        result: null,
        
        // Visualizer State (ChaCha20)
        stepsData: null,
        allRounds: [],
        currentRoundIndex: 0,
        currentRound: -1,
        currentStateWords: [],
        currentRoundLabel: '',
        changedIndices: [],
        currentCellColorsArray: [],
        currentMicroSteps: [],
        microStepIdx: 0,

        get shiftedAlphabet() {
            const alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            const s = this.shift % 26;
            return (alpha.slice(s) + alpha.slice(0, s)).split('');
        },

        async init() {
            if (localStorage.getItem('theme') === 'light') {
                this.isLightMode = true;
            }
            await this.checkService();
        },

        toggleTheme() {
            this.isLightMode = !this.isLightMode;
            localStorage.setItem('theme', this.isLightMode ? 'light' : 'dark');
        },

        onAlgorithmChange() {
            this.resetResult();
            // Adjust mode if switching away from unsupported mode
            if (this.algorithm === 'caesar' && (this.mode === 'steps' || this.mode === 'file')) {
                this.mode = 'encrypt';
            }
            if (this.algorithm === 'chacha20' && this.mode === 'brute') {
                this.mode = 'encrypt';
            }
        },

        async checkService() {
            try {
                const res = await fetch('{{ route("chacha20.keygen") }}');
                this.serviceStatus = res.ok ? 'Online' : 'Offline';
            } catch {
                this.serviceStatus = 'Offline';
            }
        },

        resetResult() {
            this.result = null;
            this.stepsData = null;
            this.fileResult = null;
            this.bruteResult = null;
            this.bruteLines = [];
            this.isBruteCracking = false;
            this.error = null;
        },

        validate() {
            this.keyError = null;
            this.nonceError = null;
            if (this.algorithm === 'chacha20' && this.mode !== 'file') {
                if (this.key && !/^[0-9a-fA-F]{64}$/.test(this.key)) {
                    this.keyError = 'Key harus tepat 64 karakter hex.';
                    return false;
                }
                if (this.nonce && !/^[0-9a-fA-F]{24}$/.test(this.nonce)) {
                    this.nonceError = 'Nonce harus tepat 24 karakter hex.';
                    return false;
                }
            }
            return true;
        },

        async run() {
            if (!this.validate()) return;
            this.loading = true;
            this.resetResult();

            try {
                if (this.algorithm === 'chacha20') {
                    if (this.mode === 'encrypt') await this.doChaChaEncrypt();
                    else if (this.mode === 'decrypt') await this.doChaChaDecrypt();
                    else if (this.mode === 'file') {
                        if (this.fileMode === 'encrypt') await this.doFileEncrypt();
                        else await this.doFileDecrypt();
                    } else if (this.mode === 'steps') {
                        await this.doChaChaSteps();
                        this.showModal = true;
                    }
                } else if (this.algorithm === 'caesar') {
                    if (this.mode === 'encrypt') await this.doCaesarEncrypt();
                    else if (this.mode === 'decrypt') await this.doCaesarDecrypt();
                    else if (this.mode === 'brute') await this.doCaesarBrute();
                }
            } catch (err) {
                this.error = err.message || 'Terjadi error tidak terduga.';
            } finally {
                this.loading = false;
            }
        },

        // --- Caesar Methods ---
        async doCaesarEncrypt() {
            if (!this.plaintext) throw new Error('Plaintext tidak boleh kosong.');
            const data = await this.apiPost('{{ route("caesar.encrypt") }}', {
                plaintext: this.plaintext, shift: this.shift, show_steps: this.showStepsCaesar,
            });
            this.result = data;
        },

        async doCaesarDecrypt() {
            if (!this.ciphertextInput) throw new Error('Ciphertext tidak boleh kosong.');
            const data = await this.apiPost('{{ route("caesar.decrypt") }}', {
                ciphertext: this.ciphertextInput, shift: this.shift, show_steps: this.showStepsCaesar,
            });
            this.result = data;
        },

        async doCaesarBrute() {
            if (!this.ciphertextInput) throw new Error('Ciphertext tidak boleh kosong.');
            
            this.isBruteCracking = true;
            this.bruteLines = [];
            this.bruteResult = null;
            this.error = null;

            const data = await this.apiPost('{{ route("caesar.brute-force") }}', {
                ciphertext: this.ciphertextInput,
            });

            // Simulate terminal printing
            for (let i = 0; i < data.results.length; i++) {
                this.bruteLines.push({
                    shift: data.results[i].shift,
                    text: data.results[i].plaintext
                });
                
                // Auto scroll to bottom
                this.$nextTick(() => {
                    const term = document.getElementById('brute-terminal');
                    if (term) term.scrollTop = term.scrollHeight;
                });
                
                // Add a small delay for dramatic effect
                await new Promise(r => setTimeout(r, 60)); 
            }

            this.isBruteCracking = false;
            this.bruteResult = data;
        },

        useForDecrypt() {
            if (!this.result?.ciphertext) return;
            this.ciphertextInput = this.result.ciphertext;
            this.mode = 'decrypt';
            this.result = null;
        },

        // --- ChaCha20 Methods ---
        async generateKey() {
            this.loading = true;
            try {
                const res = await fetch('{{ route("chacha20.keygen") }}');
                const data = await res.json();
                this.key = data.key_hex;
                this.nonce = data.nonce_hex;
                this.keyError = null;
                this.nonceError = null;
            } catch {
                this.error = 'Gagal men-generate key.';
            } finally {
                this.loading = false;
            }
        },

        async doChaChaEncrypt() {
            if (!this.plaintext) throw new Error('Plaintext tidak boleh kosong.');
            const data = await this.apiPost('{{ route("chacha20.encrypt") }}', {
                plaintext: this.plaintext,
                key: this.key || null,
                nonce: this.nonce || null,
                counter: parseInt(this.counter) || 1,
            });
            this.result = data;
            this.key = data.key_hex;
            this.nonce = data.nonce_hex;
        },

        async doChaChaDecrypt() {
            if (!this.ciphertextInput) throw new Error('Ciphertext tidak boleh kosong.');
            if (!this.key) throw new Error('Secret Key wajib diisi untuk dekripsi.');
            if (!this.nonce) throw new Error('Nonce wajib diisi untuk dekripsi.');
            
            // Clean up any whitespaces from the hex input
            const cleanHex = this.ciphertextInput.replace(/\s+/g, '');
            
            const data = await this.apiPost('{{ route("chacha20.decrypt") }}', {
                ciphertext_hex: cleanHex,
                key: this.key,
                nonce: this.nonce,
                counter: parseInt(this.counter) || 1,
            });
            this.result = data;
        },

        async doChaChaSteps() {
            if (!this.plaintext) throw new Error('Plaintext tidak boleh kosong.');
            const data = await this.apiPost('{{ route("chacha20.steps") }}', {
                plaintext: this.plaintext,
                key: this.key || null,
                nonce: this.nonce || null,
                counter: parseInt(this.counter) || 1,
            });
            this.stepsData = data;
            this.key = data.key_hex;
            this.nonce = data.nonce_hex;
            
            // --- DIFFUSION TRACKING LOGIC ---
            let currentCellColors = Array(16).fill().map((_, i) => {
                if (i < 4) return new Set(['constant']);
                if (i < 12) return new Set(['key']);
                if (i === 12) return new Set(['counter']);
                return new Set(['nonce']);
            });

            this.initialCellColors = currentCellColors.map(s => Array.from(s));

            this.allRounds = [];
            for (const log of data.round_logs) {
                let stateArr = log.state_words || (log.state_matrix ? log.state_matrix.flat() : []);
                let label = log.description || log.type;
                let rType = log.type;
                if (rType === 'quarter_round_detail') rType = 'quarter_round';

                // Hitung persebaran warna (diffusion) berdasarkan operasi ARX
                if (rType === 'quarter_round' && log.indices) {
                    let {a, b, c, d} = log.indices;
                    // a += b
                    currentCellColors[b].forEach(color => currentCellColors[a].add(color));
                    // d ^= a
                    currentCellColors[a].forEach(color => currentCellColors[d].add(color));
                    // c += d
                    currentCellColors[d].forEach(color => currentCellColors[c].add(color));
                    // b ^= c
                    currentCellColors[c].forEach(color => currentCellColors[b].add(color));
                }
                
                let snapColors = currentCellColors.map(s => Array.from(s));
                
                this.allRounds.push({
                    type: rType,
                    round: log.round === 0 ? -1 : log.round,
                    label: label,
                    state: stateArr,
                    indices: log.indices || null,
                    micro: log.arx_micro_steps || [],
                    cellColors: snapColors
                });
            }

            this.currentRoundIndex = 0;
            this.updateVisualizer();
        },

        updateVisualizer() {
            if (!this.allRounds.length) return;
            const entry = this.allRounds[this.currentRoundIndex];
            this.currentStateWords = entry.state;
            this.currentRoundLabel = entry.label;
            this.currentRound = entry.round;
            
            this.currentMicroSteps = entry.micro || [];
            this.microStepIdx = 0;

            if (this.currentRoundIndex > 0) {
                const prev = this.allRounds[this.currentRoundIndex - 1].state;
                this.changedIndices = this.currentStateWords
                    .map((w, i) => w !== prev[i] ? i : -1)
                    .filter(i => i >= 0);
            } else {
                this.changedIndices = [];
            }

            if (this.currentRound === -1) {
                this.currentCellColorsArray = this.initialCellColors || [];
            } else {
                this.currentCellColorsArray = entry.cellColors || [];
            }
        },

        goToRound(round) {
            let idx = 0;
            if (round === -1) idx = 0;
            else if (round === 'final') idx = this.allRounds.length - 1;
            else {
                // Find the FIRST entry for this round (which will be its first Quarter Round)
                idx = this.allRounds.findIndex(r => r.round === round);
            }
            if (idx !== -1) {
                this.currentRoundIndex = idx;
                this.updateVisualizer();
            }
        },

        nextMicroStep() {
            if (this.microStepIdx < this.currentMicroSteps.length - 1) this.microStepIdx++;
        },
        prevMicroStep() {
            if (this.microStepIdx > 0) this.microStepIdx--;
        },

        getNarrationText() {
            const entry = this.allRounds[this.currentRoundIndex];
            if (!entry) return '';
            if (entry.type === 'initial') {
                return `
                    <p>Ini adalah <strong>Initial State</strong> (Status Awal) dari matriks ChaCha20.</p>
                    <p style="margin-top: 10px;">Matriks 4x4 ini berisi 16 'kata' (masing-masing 32-bit):</p>
                    <ul style="margin-left: 20px; margin-top: 8px;">
                        <li><strong>[0-3] Konstanta:</strong> Teks ajaib "expand 32-byte k" untuk mencegah serangan tertentu.</li>
                        <li><strong>[4-11] Key:</strong> Kunci rahasia 256-bit milik Anda.</li>
                        <li><strong>[12] Counter:</strong> Nomor urut blok (sekarang ${this.counter}).</li>
                        <li><strong>[13-15] Nonce:</strong> Angka unik acak.</li>
                    </ul>
                `;
            }
            if (entry.type === 'final') {
                return `
                    <p><strong>Selesai!</strong> Kita telah mencapai State Akhir.</p>
                    <p style="margin-top: 10px;">Setelah 20 ronde pengacakan, hasilnya ditambahkan kembali dengan Initial State aslinya. Inilah yang membuat algoritma ini mustahil dibalikkan (*irreversible*).</p>
                    <p style="margin-top: 10px;">Langkah selanjutnya, matriks ini diubah menjadi aliran byte (Keystream) dan di-XOR dengan Plaintext Anda untuk menghasilkan Ciphertext!</p>
                `;
            }
            if (entry.type === 'quarter_round') {
                return `
                    <p>Quarter Round sedang berjalan pada sel: <strong>[${entry.indices.a}], [${entry.indices.b}], [${entry.indices.c}], [${entry.indices.d}]</strong>.</p>
                    <p style="margin-top: 10px;">Gunakan navigasi Micro-Step di bawah untuk melihat operasi ARX (Add, Rotate, XOR) secara mendetail.</p>
                `;
            }
            if (entry.label.includes('Column')) {
                return `
                    <p><strong>Column Round</strong> selesai dieksekusi.</p>
                    <p style="margin-top: 10px;">4 Quarter Round baru saja selesai mengacak kolom matriks secara vertikal.</p>
                `;
            }
            return `
                <p><strong>Diagonal Round</strong> selesai dieksekusi.</p>
                <p style="margin-top: 10px;">4 Quarter Round menyilang pengacakannya secara diagonal (miring) untuk menciptakan efek <em>Diffusion</em>.</p>
            `;
        },

        // --- File Handling (ChaCha20) ---
        handleFileDrop(e) {
            const files = e.dataTransfer?.files;
            if (files && files.length > 0) this.setFile(files[0]);
        },
        handleFileSelect(e) {
            const files = e.target?.files;
            if (files && files.length > 0) this.setFile(files[0]);
        },
        setFile(file) {
            const maxSize = 5 * 1024 * 1024;
            if (file.size > maxSize) {
                this.error = `File terlalu besar (${this.formatFileSize(file.size)}). Maksimal 5 MB.`;
                return;
            }
            this.selectedFile = file;
            this.error = null;
            this.fileResult = null;
        },
        removeFile() {
            this.selectedFile = null;
            this.fileResult = null;
        },
        async doFileEncrypt() {
            if (!this.selectedFile) throw new Error('Pilih file terlebih dahulu.');
            const formData = new FormData();
            formData.append('file', this.selectedFile);
            formData.append('counter', parseInt(this.counter) || 1);
            if (this.key) formData.append('key', this.key);
            if (this.nonce) formData.append('nonce', this.nonce);

            const res = await fetch('{{ route("chacha20.encrypt-file") }}', {
                method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }, body: formData,
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal mengenkripsi file.');

            this.fileResult = data;
            this.key = data.key_hex;
            this.nonce = data.nonce_hex;
        },
        async doFileDecrypt() {
            if (!this.selectedFile) throw new Error('Pilih file terlebih dahulu.');
            if (!this.key) throw new Error('Secret Key wajib diisi untuk dekripsi file.');
            if (!this.nonce) throw new Error('Nonce wajib diisi untuk dekripsi file.');
            const formData = new FormData();
            formData.append('file', this.selectedFile);
            formData.append('key', this.key);
            formData.append('nonce', this.nonce);
            formData.append('counter', parseInt(this.counter) || 1);

            const res = await fetch('{{ route("chacha20.decrypt-file") }}', {
                method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' }, body: formData,
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal mendekripsi file.');
            this.fileResult = data;
        },
        downloadFileResult() {
            if (!this.fileResult?.file_base64) return;
            const byteChars = atob(this.fileResult.file_base64);
            const byteArray = new Uint8Array(byteChars.length);
            for (let i = 0; i < byteChars.length; i++) byteArray[i] = byteChars.charCodeAt(i);
            const blob = new Blob([byteArray], { type: 'application/octet-stream' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = this.fileResult.result_filename || 'result';
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            URL.revokeObjectURL(url);
        },
        formatFileSize(bytes) {
            if (!bytes) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB'];
            let i = 0, size = bytes;
            while (size >= 1024 && i < units.length - 1) { size /= 1024; i++; }
            return size.toFixed(i > 0 ? 2 : 0) + ' ' + units[i];
        },
        
        // --- Utilities ---
        copyText(text) {
            navigator.clipboard.writeText(text).then(() => alert('Berhasil disalin!'));
        },
        async apiPost(url, body) {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(body),
            });
            const data = await res.json();
            if (!res.ok) {
                if (data.errors) throw new Error(Object.values(data.errors).flat().join(' '));
                throw new Error(data.message || data.error || 'API Error');
            }
            return data;
        },
    };
}
</script>
</body>
</html>
