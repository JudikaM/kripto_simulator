<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ChaCha20 Simulator | NakamotoX</title>
    <!-- Modern Sans-Serif Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="//unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        /* Netflix & Glassmorphism Variables */
        :root {
            --bg-base: #0B0B0C;
            --primary: #E50914;
            --primary-hover: #F40612;
            --text-main: #FFFFFF;
            --text-muted: #B3B3B3;
            --border-glass: rgba(255, 255, 255, 0.1);
            --bg-glass: rgba(20, 20, 20, 0.45);
            --radius: 12px;
            --font-main: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            --matrix-changed: rgba(229, 9, 20, 0.85);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            background-color: var(--bg-base);
            color: var(--text-main);
            font-family: var(--font-main);
            font-size: 15px;
            min-height: 100vh;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient Animated Background Orbs */
        .bg-orbs {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; overflow: hidden;
        }
        .orb {
            position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.4;
            animation: float 20s infinite ease-in-out alternate;
        }
        .orb-1 { width: 500px; height: 500px; background: #E50914; top: -100px; left: -100px; animation-delay: 0s; }
        .orb-2 { width: 400px; height: 400px; background: #660099; bottom: 10%; right: -50px; animation-delay: -5s; }
        .orb-3 { width: 300px; height: 300px; background: #0044ff; top: 40%; left: 40%; animation-delay: -10s; opacity: 0.2; }
        
        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(100px, 50px) scale(1.1); }
            100% { transform: translate(-50px, 100px) scale(0.9); }
        }

        /* Nav & Hero */
        .navbar {
            padding: 24px 4%;
            display: flex; justify-content: space-between; align-items: center;
            background: linear-gradient(to bottom, rgba(0,0,0,0.6) 0%, transparent 100%);
        }
        .logo { color: var(--text-main); font-size: 26px; font-weight: 800; letter-spacing: -1px; text-decoration: none; text-shadow: 0 0 10px rgba(255,255,255,0.3);}
        .logo span { color: var(--primary); }
        
        .hero { padding: 20px 4% 40px; }
        .hero h1 { font-size: 3rem; font-weight: 800; margin-bottom: 12px; letter-spacing: -1.5px; }
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
            display: grid; grid-template-columns: 1fr; gap: 20px; align-items: stretch;
        }
        @media (min-width: 1024px) {
            .pipeline-layout { grid-template-columns: 2.5fr 40px 3fr 40px 3.5fr; }
        }
        .column-block { display: flex; flex-direction: column; }

        /* Pipeline Divider */
        .pipeline-divider { display: none; align-items: center; justify-content: center; }
        @media (min-width: 1024px) { .pipeline-divider { display: flex; } }
        .chevron-right {
            width: 24px; height: 24px; border-top: 4px solid var(--primary); border-right: 4px solid var(--primary);
            transform: rotate(45deg); opacity: 0.6; margin-top: -60px;
        }

        /* Glass Card */
        .card {
            background-color: var(--bg-glass);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-radius: var(--radius); padding: 24px; margin-bottom: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.1);
            border: 1px solid var(--border-glass);
        }
        .card.h-full { flex: 1; margin-bottom: 0; }
        
        .card-header-banner {
            background: rgba(229, 9, 20, 0.85); color: white;
            padding: 12px 20px; margin: -24px -24px 24px -24px;
            border-top-left-radius: var(--radius); border-top-right-radius: var(--radius);
            font-size: 1.1rem; font-weight: 600; letter-spacing: 0.5px;
            display: flex; align-items: center; gap: 12px;
            box-shadow: 0 2px 10px rgba(229,9,20,0.3);
        }
        .card-header-banner.muted { background: rgba(40,40,40,0.8); color: var(--text-main); box-shadow: none; }
        .card-header-icon {
            display: inline-flex; justify-content: center; align-items: center;
            width: 24px; height: 24px; border: 2px solid currentColor; border-radius: 50%;
            font-size: 14px; font-weight: bold;
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
        
        input[type=text], textarea {
            width: 100%; padding: 14px; background: rgba(0,0,0,0.4); border: 1px solid var(--border-glass);
            color: var(--text-main); font-size: 14px; border-radius: 6px;
            transition: all 0.2s; font-family: var(--font-main);
        }
        input.mono-font, textarea.mono-font { font-family: 'Courier New', Courier, monospace; }
        textarea { resize: vertical; min-height: 120px; line-height: 1.6; }
        textarea.full-height { min-height: calc(100% - 100px); height: 300px; }
        input[type=text]:focus, textarea:focus { outline: none; border-color: var(--primary); background: rgba(0,0,0,0.6); box-shadow: 0 0 15px rgba(229,9,20,0.2); }
        
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
        .btn-primary { background: var(--primary); color: white; width: 100%; box-shadow: 0 4px 15px rgba(229,9,20,0.4); }
        .btn-primary:not(:disabled):hover { background: var(--primary-hover); transform: translateY(-1px); }
        .btn-outline { background: transparent; border: 1px solid var(--border-glass); color: var(--text-main); }
        .btn-outline:not(:disabled):hover { background: rgba(255,255,255,0.1); }
        .btn-sm { padding: 6px 12px; font-size: 13px; }

        /* Result Area */
        .result-box {
            font-size: 14px; font-family: 'Courier New', Courier, monospace;
            background: rgba(0,0,0,0.6); border: 1px solid var(--border-glass); border-radius: 6px;
            padding: 16px; word-break: break-all; white-space: pre-wrap; color: var(--text-main);
            min-height: 100px; line-height: 1.5; margin-bottom: 16px;
        }
        .result-box.error { border-color: var(--primary); background: rgba(229,9,20,0.1); color: #ff6b6b; min-height: auto; }
        
        .empty-state {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            height: 100%; color: var(--text-muted); text-align: center; padding: 40px 20px;
        }
        .empty-state svg { width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.5; }

        /* ==================================================
           FLOATING MODAL / VISUALIZER POPUP 
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

        .modal-header {
            padding: 20px 30px; border-bottom: 1px solid var(--border-glass);
            display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.3);
        }
        .modal-title { font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 12px; }
        .modal-close {
            background: transparent; border: none; color: var(--text-muted); font-size: 24px;
            cursor: pointer; transition: color 0.2s;
        }
        .modal-close:hover { color: white; }

        .modal-body {
            display: grid; grid-template-columns: 1fr; gap: 30px; padding: 30px; overflow-y: auto;
        }
        @media (min-width: 900px) { .modal-body { grid-template-columns: 1.5fr 1fr; } }

        /* Matrix Elements inside Modal */
        .matrix-container { background: rgba(0,0,0,0.4); border-radius: 12px; padding: 24px; border: 1px solid var(--border-glass); }
        .state-matrix { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 24px; }
        .state-cell {
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 6px;
            padding: 24px 8px 16px; text-align: center; color: var(--text-main);
            font-family: 'Courier New', Courier, monospace; font-size: 14px; font-weight: bold;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative;
        }
        .state-cell.changed {
            background: var(--matrix-changed); border-color: var(--primary-hover);
            transform: scale(1.08); box-shadow: 0 0 20px rgba(229,9,20,0.6); z-index: 2; color: white;
        }
        .state-cell .index { color: var(--text-muted); font-size: 11px; display: block; margin-bottom: 6px; font-family: var(--font-main); font-weight: normal; }

        /* Legend Colors */
        .legend-constant { border-bottom: 3px solid #888; }
        .legend-key { border-bottom: 3px solid #e50914; }
        .legend-counter { border-bottom: 3px solid #3b82f6; }
        .legend-nonce { border-bottom: 3px solid #46d369; }

        /* Diffusion Dots */
        .color-dots { display: flex; gap: 4px; justify-content: center; position: absolute; top: 6px; left: 0; right: 0; }
        .dot { width: 6px; height: 6px; border-radius: 50%; box-shadow: 0 0 4px rgba(0,0,0,0.5); }
        .dot-constant { background: #888; }
        .dot-key { background: #e50914; box-shadow: 0 0 6px #e50914; }
        .dot-counter { background: #3b82f6; box-shadow: 0 0 6px #3b82f6; }
        .dot-nonce { background: #46d369; box-shadow: 0 0 6px #46d369; }

        /* Interactive Narration Panel */
        .narration-panel {
            background: rgba(255,255,255,0.02); border-radius: 12px; padding: 24px;
            border: 1px solid var(--border-glass); display: flex; flex-direction: column;
        }
        .story-title { font-size: 1.2rem; font-weight: 700; color: var(--primary); margin-bottom: 16px; }
        .story-text { font-size: 1rem; color: var(--text-main); line-height: 1.6; margin-bottom: 24px; font-weight: 300; }
        
        .arx-box { background: rgba(0,0,0,0.5); border-radius: 8px; padding: 16px; margin-bottom: 20px; border-left: 4px solid var(--primary); }
        .arx-title { font-weight: 600; margin-bottom: 8px; font-size: 14px; }
        .arx-desc { font-size: 13px; color: var(--text-muted); }

        /* ARX Micro-Step Visualizer */
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
        .arx-op-desc { font-size: 13px; font-weight: 600; color: white; font-family: 'Courier New', monospace; margin-bottom: 10px; }
        .arx-row { display: flex; flex-direction: column; gap: 4px; margin-bottom: 6px; }
        .arx-row-label { font-size: 11px; color: var(--text-muted); font-weight: 500; }
        .arx-hex-val { font-family: 'Courier New', monospace; font-size: 13px; color: white; font-weight: 600; }
        .arx-bin-row { font-family: 'Courier New', monospace; font-size: 11px; letter-spacing: 1px; word-break: break-all; padding: 4px 6px; border-radius: 4px; background: rgba(0,0,0,0.4); }
        .arx-bin-row.src { color: #888; }
        .arx-bin-row.result { color: #4ade80; }
        .arx-separator { text-align: center; font-size: 16px; font-weight: 700; padding: 2px 0; }
        .arx-separator.add-color { color: #00c8ff; }
        .arx-separator.xor-color { color: #ff64c8; }
        .arx-separator.rot-color { color: #ffb400; }
        .arx-result-line { border-top: 1px dashed rgba(255,255,255,0.15); padding-top: 6px; margin-top: 2px; }

        .round-nav-modal { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; }
        .round-dot {
            width: 32px; height: 32px; display:flex; align-items:center; justify-content:center;
            border-radius: 50%; font-size: 12px; font-weight: 600; background: rgba(255,255,255,0.05); color: var(--text-muted);
            cursor: pointer; transition: all 0.2s; border: 2px solid transparent;
        }
        .round-dot:hover { background: rgba(255,255,255,0.2); color: white; }
        .round-dot.active { background: white; color: black; border-color: white; transform: scale(1.1); }

        .spinner {
            width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%; border-top-color: white; animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* File Upload Drop Zone */
        .drop-zone {
            border: 2px dashed rgba(255,255,255,0.15); border-radius: 12px;
            padding: 40px 20px; text-align: center; cursor: pointer;
            transition: all 0.3s; background: rgba(0,0,0,0.2);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            min-height: 200px; position: relative;
        }
        .drop-zone:hover { border-color: var(--primary); background: rgba(229,9,20,0.05); }
        .drop-zone.dragover { border-color: var(--primary); background: rgba(229,9,20,0.1); transform: scale(1.02); }
        .drop-zone-icon { font-size: 48px; margin-bottom: 12px; opacity: 0.6; }
        .drop-zone-text { color: var(--text-muted); font-size: 14px; }
        .drop-zone-text strong { color: var(--primary); }
        .drop-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }

        .file-info-card {
            background: rgba(255,255,255,0.05); border: 1px solid var(--border-glass);
            border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; gap: 16px;
        }
        .file-info-icon { font-size: 36px; }
        .file-info-details { flex: 1; }
        .file-info-name { font-weight: 600; font-size: 15px; word-break: break-all; }
        .file-info-meta { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
        .file-remove { background: none; border: none; color: #ff6b6b; cursor: pointer; font-size: 20px; padding: 4px 8px; transition: opacity .2s; }
        .file-remove:hover { opacity: 0.7; }

        .download-card {
            background: rgba(229,9,20,0.08); border: 1px solid rgba(229,9,20,0.3);
            border-radius: 12px; padding: 24px; text-align: center;
        }
        .download-card .download-icon { font-size: 48px; margin-bottom: 12px; }
        .download-card .download-filename { font-weight: 600; font-size: 16px; margin-bottom: 4px; word-break: break-all; }
        .download-card .download-size { font-size: 13px; color: var(--text-muted); margin-bottom: 16px; }
        .btn-download {
            background: var(--primary); color: white; border: none; padding: 14px 32px;
            border-radius: 8px; font-weight: 600; font-size: 15px; cursor: pointer;
            transition: all .2s; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-download:hover { background: var(--primary-hover); transform: translateY(-1px); }

        .key-display {
            background: rgba(0,0,0,0.4); border: 1px solid var(--border-glass); border-radius: 8px;
            padding: 12px 16px; margin-top: 16px; text-align: left;
        }
        .key-display label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 2px; }
        .key-display .key-value { font-family: 'Courier New', monospace; font-size: 12px; word-break: break-all; color: #fbbf24; }

        .file-sub-mode { display: flex; gap: 8px; margin-bottom: 16px; }
        .file-sub-tab {
            flex: 1; text-align: center; padding: 8px; font-size: 13px; font-weight: 500;
            border-radius: 6px; cursor: pointer; transition: all .2s;
            background: rgba(255,255,255,0.05); color: var(--text-muted); border: 1px solid transparent;
        }
        .file-sub-tab:hover { color: white; background: rgba(255,255,255,0.1); }
        .file-sub-tab.active { background: rgba(229,9,20,0.2); color: white; border-color: var(--primary); }
    </style>
</head>
<body x-data="chacha20App()" x-init="init()" :style="showModal ? 'overflow: hidden;' : ''">

    <!-- Ambient Background -->
    <div class="bg-orbs">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="{{ route('chacha20.index') }}" class="logo"><span>Naka</span>motoX</a>
        <div style="display:flex; align-items:center; gap: 16px;">
            <a href="{{ route('caesar.index') }}" style="color:var(--text-main); text-decoration:none; font-weight:600; font-size:14px; display:flex; align-items:center; gap:6px; transition:color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-main)'">
                <span>🏛️</span> Caesar Cipher
            </a>
            <a href="{{ route('chacha20.learn') }}" style="color:var(--text-main); text-decoration:none; font-weight:600; font-size:14px; display:flex; align-items:center; gap:6px; transition:color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-main)'">
                <span>📖</span> The Story of ChaCha20
            </a>
            <div class="status-badge" :class="serviceStatus === 'Online' ? 'online' : 'offline'">
                <span x-show="serviceStatus === 'Online'">🟢</span>
                <span x-show="serviceStatus !== 'Online'">🔴</span>
                Engine: <span x-text="serviceStatus">Checking...</span>
            </div>
        </div>
    </nav>

    <section class="hero">
        <h1>ChaCha20 Workspace</h1>
        <p>Alur enkripsi layaknya *pipeline* industri. Masukkan data di kiri, konfigurasi algoritma di tengah, dan lihat hasil olahannya di kanan.</p>
    </section>

    <!-- Pipeline Layout -->
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

                    <div class="form-group" x-show="mode === 'decrypt'" style="height: 100%;">
                        <label>Ciphertext (Hex)</label>
                        <span class="educational-text">Masukkan data yang sudah terenkripsi.</span>
                        <textarea class="mono-font full-height" x-model="ciphertextInput" placeholder="Contoh: a1b2c3d4e5f6..."></textarea>
                    </div>

                    <!-- File Mode: Drop Zone -->
                    <div x-show="mode === 'file'" style="height: 100%; display: flex; flex-direction: column;">
                        <label x-text="fileMode === 'encrypt' ? 'File untuk Dienkripsi' : 'File Terenkripsi untuk Didekripsi'"></label>
                        <span class="educational-text" x-text="fileMode === 'encrypt' ? 'Drag & drop atau pilih file (maks 5 MB).' : 'Upload file terenkripsi yang ingin didekripsi.'"></span>

                        <!-- Drop Zone (saat belum ada file) -->
                        <div x-show="!selectedFile" class="drop-zone" :class="{'dragover': isDragging}"
                             @dragover.prevent="isDragging = true"
                             @dragleave.prevent="isDragging = false"
                             @drop.prevent="isDragging = false; handleFileDrop($event)">
                            <div class="drop-zone-icon">📁</div>
                            <div class="drop-zone-text">Drag & drop file di sini<br>atau <strong>klik untuk browse</strong></div>
                            <div style="font-size:12px; color:var(--text-muted); margin-top:8px;">Maks. 5 MB</div>
                            <input type="file" @change="handleFileSelect($event)">
                        </div>

                        <!-- File Info (saat sudah ada file) -->
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
            </div>

            <!-- DIVIDER 1 -->
            <div class="pipeline-divider"><div class="chevron-right"></div></div>

            <!-- COLUMN 2: PARAMETERS & ACTION -->
            <div class="column-block">
                <div class="card h-full">
                    <div class="card-header-banner">
                        <span class="card-header-icon">2</span>
                        <span>ENGINE CONFIGURATION</span>
                    </div>

                    <!-- Mode Switcher -->
                    <div class="mode-switcher">
                        <div class="mode-tab" :class="{ active: mode === 'encrypt' }" @click="mode = 'encrypt'; resetResult()">Encrypt</div>
                        <div class="mode-tab" :class="{ active: mode === 'decrypt' }" @click="mode = 'decrypt'; resetResult()">Decrypt</div>
                        <div class="mode-tab" :class="{ active: mode === 'steps' }" @click="mode = 'steps'; resetResult()">Visualize</div>
                        <div class="mode-tab" :class="{ active: mode === 'file' }" @click="mode = 'file'; resetResult()">📁 File</div>
                    </div>

                    <!-- File sub-mode (encrypt/decrypt) -->
                    <div x-show="mode === 'file'" class="file-sub-mode">
                        <div class="file-sub-tab" :class="{active: fileMode === 'encrypt'}" @click="fileMode = 'encrypt'; resetResult(); removeFile()">🔒 Encrypt File</div>
                        <div class="file-sub-tab" :class="{active: fileMode === 'decrypt'}" @click="fileMode = 'decrypt'; resetResult(); removeFile()">🔓 Decrypt File</div>
                    </div>

                    <div class="form-group">
                        <label>Algorithm</label>
                        <input type="text" value="ChaCha20 Stream Cipher" disabled style="background: rgba(0,0,0,0.2); color: var(--text-muted); cursor: not-allowed; border-color: transparent;">
                    </div>

                    <div class="form-group">
                        <label>Secret Key (256-bit)</label>
                        <span class="educational-text">Kunci rahasia (64 hex chars). Kunci ini mengamankan seluruh proses matriks.</span>
                        <div class="key-row">
                            <input type="text" class="mono-font" x-model="key" placeholder="64 hex chars..." maxlength="64">
                            <button class="btn btn-outline" @click="generateKey()" :disabled="loading" title="Generate Random Key & Nonce">
                                <span x-show="loading" class="spinner"></span>
                                <span x-show="!loading">Gen</span>
                            </button>
                        </div>
                        <p x-show="keyError" x-text="keyError" style="color:#ff6b6b; font-size:12px; margin-top:4px;"></p>
                    </div>

                    <div class="form-group">
                        <label>Nonce (96-bit)</label>
                        <span class="educational-text">Nilai acak unik (24 hex chars) agar enkripsi tidak menghasilkan pola berulang.</span>
                        <input type="text" class="mono-font" x-model="nonce" placeholder="24 hex chars..." maxlength="24">
                        <p x-show="nonceError" x-text="nonceError" style="color:#ff6b6b; font-size:12px; margin-top:4px;"></p>
                    </div>

                    <div class="form-group">
                        <label>Initial Counter</label>
                        <span class="educational-text">Penanda urutan blok data. Mencegah tabrakan pola jika pesan sangat panjang (>64 byte).</span>
                        <input type="text" x-model="counter" placeholder="1" style="font-family: var(--font-main);">
                    </div>

                    <div style="flex-grow: 1;"></div>

                    <button class="btn btn-primary" style="margin-top: 24px; padding: 18px;" @click="run()" :disabled="loading">
                        <span x-show="loading" class="spinner"></span>
                        <span x-show="!loading && mode === 'encrypt'">Execute Pipeline →</span>
                        <span x-show="!loading && mode === 'decrypt'">Execute Pipeline →</span>
                        <span x-show="!loading && mode === 'steps'">Buka Visualizer Edukatif 🎥</span>
                        <span x-show="!loading && mode === 'file' && fileMode === 'encrypt'">🔒 Encrypt File →</span>
                        <span x-show="!loading && mode === 'file' && fileMode === 'decrypt'">🔓 Decrypt File →</span>
                    </button>
                </div>
            </div>

            <!-- DIVIDER 2 -->
            <div class="pipeline-divider"><div class="chevron-right"></div></div>

            <!-- COLUMN 3: OUTPUT -->
            <div class="column-block">
                <div class="card h-full" style="display: flex; flex-direction: column;">
                    <div class="card-header-banner muted">
                        <span class="card-header-icon">3</span>
                        <span>OUTPUT RESULT</span>
                    </div>

                    <div x-show="error" class="result-box error"><strong>Error:</strong> <span x-text="error"></span></div>

                    <div x-show="!result && !stepsData && !fileResult && !error" class="empty-state">
                        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M19,3H5C3.89,3 3,3.89 3,5V19C3,20.1 3.9,21 5,21H19C20.1,21 21,20.1 21,19V5C21,3.89 20.1,3 19,3M19,19H5V5H19V19M17,17H7V15H17V17M17,13H7V11H17V13M17,9H7V7H17V9Z" /></svg>
                        <p x-show="mode !== 'steps' && mode !== 'file'">Menunggu eksekusi *pipeline*.<br>Hasil akan ditampilkan di sini.</p>
                        <p x-show="mode === 'steps'">Klik tombol di kolom tengah untuk membuka jendela visualizer pop-up.</p>
                        <p x-show="mode === 'file'">Upload file dan klik tombol eksekusi.<br>Hasil download akan muncul di sini.</p>
                    </div>

                    <div x-show="result && mode !== 'steps' && mode !== 'file'" style="flex-grow: 1;">
                        <template x-if="mode === 'encrypt' && result">
                            <div>
                                <label>Ciphertext (Hex)</label>
                                <div class="result-box" x-text="result.ciphertext_hex"></div>
                                <label>Ciphertext (Base64)</label>
                                <div class="result-box" x-text="result.ciphertext_base64"></div>
                            </div>
                        </template>

                        <template x-if="mode === 'decrypt' && result">
                            <div>
                                <label>Plaintext Asli</label>
                                <div class="result-box" x-text="result.plaintext" style="font-family: var(--font-main); font-size: 16px;"></div>
                                <label>Plaintext (Hex)</label>
                                <div class="result-box" x-text="result.plaintext_hex"></div>
                            </div>
                        </template>
                    </div>
                    
                    <div x-show="mode === 'steps' && stepsData" class="empty-state">
                        <div style="font-size: 40px; margin-bottom: 16px;">🎓</div>
                        <p style="color: white; font-weight: 500;">Visualizer Berhasil Di-generate!</p>
                        <p style="font-size: 13px; margin-top: 8px;">Jendela pop-up sedang terbuka. Jika Anda tidak sengaja menutupnya, Anda bisa membukanya kembali.</p>
                        <button class="btn btn-outline" style="margin-top: 20px;" @click="showModal = true">Buka Kembali Visualizer</button>
                    </div>

                    <!-- File Result: Download Card -->
                    <div x-show="mode === 'file' && fileResult" style="flex-grow: 1;">
                        <div class="download-card">
                            <div class="download-icon">✅</div>
                            <div class="download-filename" x-text="fileResult?.result_filename"></div>
                            <div class="download-size" x-text="formatFileSize(fileResult?.content_length)"></div>
                            <button class="btn-download" @click="downloadFileResult()">
                                ⬇️ Download File
                            </button>
                        </div>

                        <!-- Key & Nonce Display (only for encrypt) -->
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
                                <button class="btn btn-outline btn-sm" style="margin-top: 12px; width: 100%;" @click="copyFileKeys()">📋 Salin Key & Nonce</button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- FLOATING MODAL VISUALIZER -->
    <div class="modal-overlay" :class="{'show': showModal}">
        <div class="modal-content" @click.stop>
            <div class="modal-header">
                <div class="modal-title">
                    <span style="color:var(--primary)">🎓</span> Pembelajaran Interaktif ChaCha20
                </div>
                <button class="modal-close" @click="showModal = false">✕</button>
            </div>

            <div class="modal-body" x-show="stepsData">
                
                <!-- KIRI: Matrix Viewer -->
                <div>
                    <div class="matrix-container">
                        <div style="font-size:16px; font-weight: 700; color:white; margin-bottom:16px; text-transform:uppercase; letter-spacing:1px;" x-text="currentRoundLabel"></div>

                        <!-- 4x4 Grid -->
                        <div class="state-matrix">
                            <template x-for="(word, idx) in currentStateWords" :key="idx">
                                <div class="state-cell" 
                                     :class="{
                                         'changed': changedIndices.includes(idx),
                                         'legend-constant': currentRound === -1 && idx >= 0 && idx <= 3,
                                         'legend-key': currentRound === -1 && idx >= 4 && idx <= 11,
                                         'legend-counter': currentRound === -1 && idx === 12,
                                         'legend-nonce': currentRound === -1 && idx >= 13 && idx <= 15
                                     }">
                                    <div class="color-dots" x-show="currentCellColorsArray[idx]">
                                        <template x-for="c in currentCellColorsArray[idx]" :key="c">
                                            <div class="dot" :class="'dot-' + c"></div>
                                        </template>
                                    </div>
                                    <span class="index" x-text="`[${idx}]`"></span>
                                    <span x-text="word"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Navigasi Bawah Matrix -->
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <button class="btn btn-outline btn-sm" @click="prevRound()" :disabled="currentRoundIndex <= 0">← Mundur</button>
                            <span style="font-size:13px; color:var(--text-muted); font-weight:600;" x-text="`Step ${currentRoundIndex + 1} / ${allRounds.length}`"></span>
                            <button class="btn btn-primary btn-sm" @click="nextRound()" :disabled="currentRoundIndex >= allRounds.length - 1" style="width:auto">Maju →</button>
                        </div>
                    </div>
                </div>

                <!-- KANAN: Penjelasan Edukatif & Navigasi -->
                <div class="narration-panel">
                    
                    <!-- Judul Cerita -->
                    <div class="story-title" x-text="getStoryTitle()"></div>
                    
                    <!-- Teks Penjelasan -->
                    <div class="story-text" x-html="getStoryText()"></div>

                    <!-- ARX Micro-Step Visualizer -->
                    <div x-show="currentMicroSteps.length > 0">
                        <div class="arx-micro">
                            <div class="arx-micro-header">
                                <div class="arx-step-badge" :class="currentMicroOp().toLowerCase()">
                                    <span x-text="currentMicroOp()"></span>
                                    <span x-text="getMicroStepGroupLabel()"></span>
                                </div>
                                <div class="arx-micro-nav">
                                    <button @click="prevMicroStep()" :disabled="microStepIdx <= 0">◀</button>
                                    <span style="font-size:11px;color:var(--text-muted)" x-text="`${microStepIdx+1}/${currentMicroSteps.length}`"></span>
                                    <button @click="nextMicroStep()" :disabled="microStepIdx >= currentMicroSteps.length - 1">▶</button>
                                </div>
                            </div>
                            <div class="arx-op-desc" x-text="currentMicroSteps[microStepIdx]?.description"></div>

                            <!-- ADD / XOR layout -->
                            <template x-if="currentMicroOp() !== 'ROT'">
                                <div>
                                    <div class="arx-row">
                                        <div class="arx-row-label">Operand 1 <span class="arx-hex-val" x-text="currentMicroSteps[microStepIdx]?.operand1_hex"></span></div>
                                        <div class="arx-bin-row src" x-text="currentMicroSteps[microStepIdx]?.operand1_bin"></div>
                                    </div>
                                    <div class="arx-separator" :class="currentMicroOp()==='ADD' ? 'add-color' : 'xor-color'" x-text="currentMicroSteps[microStepIdx]?.symbol"></div>
                                    <div class="arx-row">
                                        <div class="arx-row-label">Operand 2 <span class="arx-hex-val" x-text="currentMicroSteps[microStepIdx]?.operand2_hex"></span></div>
                                        <div class="arx-bin-row src" x-text="currentMicroSteps[microStepIdx]?.operand2_bin"></div>
                                    </div>
                                    <div class="arx-separator" style="color:var(--text-muted)">=</div>
                                    <div class="arx-row arx-result-line">
                                        <div class="arx-row-label">Hasil <span class="arx-hex-val" style="color:#4ade80" x-text="currentMicroSteps[microStepIdx]?.result_hex"></span></div>
                                        <div class="arx-bin-row result" x-text="currentMicroSteps[microStepIdx]?.result_bin"></div>
                                    </div>
                                </div>
                            </template>

                            <!-- ROT layout -->
                            <template x-if="currentMicroOp() === 'ROT'">
                                <div>
                                    <div class="arx-row">
                                        <div class="arx-row-label">Sebelum diputar <span class="arx-hex-val" x-text="currentMicroSteps[microStepIdx]?.operand1_hex"></span></div>
                                        <div class="arx-bin-row src" x-text="currentMicroSteps[microStepIdx]?.operand1_bin"></div>
                                    </div>
                                    <div class="arx-separator rot-color" x-text="'<<< ' + (currentMicroSteps[microStepIdx]?.shift ?? '')"></div>
                                    <div class="arx-row arx-result-line">
                                        <div class="arx-row-label">Setelah diputar <span class="arx-hex-val" style="color:#4ade80" x-text="currentMicroSteps[microStepIdx]?.result_hex"></span></div>
                                        <div class="arx-bin-row result" x-text="currentMicroSteps[microStepIdx]?.result_bin"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Static fallback for non-QR steps -->
                    <div class="arx-box" x-show="currentMicroSteps.length === 0 && currentRound >= 0 && currentRound !== 'final'">
                        <div class="arx-title">⚡ Operasi Inti: ARX</div>
                        <div class="arx-desc">
                            Ronde ini terdiri dari 4 Quarter Round.<br>
                            Klik pada Quarter Round individual (tombol Maju) untuk melihat detail setiap operasi ARX.
                        </div>
                    </div>

                    <div style="flex-grow:1"></div>

                    <!-- Navigasi Bulat Cepat -->
                    <label style="font-size: 12px; color: var(--text-muted); margin-bottom: 8px; display:block;">Lompat Cepat ke Ronde:</label>
                    <div class="round-nav-modal">
                        <div class="round-dot" :class="{active: currentRound === -1}" @click="goToRound(-1)" title="Initial State">In</div>
                        <template x-for="summary in stepsData.round_summaries" :key="summary.round">
                            <div class="round-dot"
                                 :class="{active: currentRound === summary.round}"
                                 @click="goToRound(summary.round)"
                                 x-text="summary.round">
                            </div>
                        </template>
                        <div class="round-dot" :class="{active: currentRound === 'final'}" @click="goToRound('final')" title="Final State">Fn</div>
                    </div>

                </div>

            </div>
        </div>
    </div>

<script>
function chacha20App() {
    return {
        apiUrl: '{{ $apiUrl }}',
        csrfToken: document.querySelector('meta[name=csrf-token]').content,
        mode: 'encrypt',
        loading: false,
        serviceStatus: 'Loading...',
        showModal: false, // Kontrol pop-up visualizer

        plaintext: '',
        ciphertextInput: '',
        key: '',
        nonce: '',
        counter: 1,

        error: null,
        keyError: null,
        nonceError: null,
        result: null,
        stepsData: null,

        // File mode state
        fileMode: 'encrypt',
        selectedFile: null,
        fileResult: null,
        isDragging: false,

        allRounds: [],
        currentRoundIndex: 0,
        currentRound: -1,
        currentStateWords: [],
        currentRoundLabel: '',
        changedIndices: [],
        currentCellColorsArray: [],
        currentMicroSteps: [],
        microStepIdx: 0,

        async init() {
            await this.checkService();
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
            this.error = null;
        },

        validate() {
            this.keyError = null;
            this.nonceError = null;
            if (this.key && !/^[0-9a-fA-F]{64}$/.test(this.key)) {
                this.keyError = 'Key harus tepat 64 karakter hex.';
                return false;
            }
            if (this.nonce && !/^[0-9a-fA-F]{24}$/.test(this.nonce)) {
                this.nonceError = 'Nonce harus tepat 24 karakter hex.';
                return false;
            }
            return true;
        },

        async run() {
            if (!this.validate()) return;
            this.loading = true;
            this.error = null;
            this.result = null;
            this.stepsData = null;
            this.fileResult = null;

            try {
                if (this.mode === 'encrypt') await this.doEncrypt();
                else if (this.mode === 'decrypt') await this.doDecrypt();
                else if (this.mode === 'file') {
                    if (this.fileMode === 'encrypt') await this.doFileEncrypt();
                    else await this.doFileDecrypt();
                } else {
                    await this.doSteps();
                    this.showModal = true;
                }
            } catch (err) {
                this.error = err.message || 'Terjadi error tidak terduga.';
            } finally {
                this.loading = false;
            }
        },

        async generateKey() {
            this.loading = true;
            try {
                const res = await fetch('{{ route("chacha20.keygen") }}');
                const data = await res.json();
                this.key = data.key_hex;
                this.nonce = data.nonce_hex;
            } catch {
                this.error = 'Gagal generate key. Pastikan microservice Python berjalan.';
            } finally {
                this.loading = false;
            }
        },

        async doEncrypt() {
            const body = { plaintext: this.plaintext, counter: parseInt(this.counter) || 1 };
            if (this.key) body.key = this.key;
            if (this.nonce) body.nonce = this.nonce;

            const data = await this.apiPost('{{ route("chacha20.encrypt") }}', body);
            this.result = data;
            this.key = data.key_hex;
            this.nonce = data.nonce_hex;
        },

        async doDecrypt() {
            if (!this.ciphertextInput) throw new Error('Silakan masukkan data Ciphertext.');
            if (!this.key) throw new Error('Secret Key wajib diisi.');
            if (!this.nonce) throw new Error('Nonce wajib diisi.');

            const data = await this.apiPost('{{ route("chacha20.decrypt") }}', {
                ciphertext_hex: this.ciphertextInput.trim(), key: this.key, nonce: this.nonce,
                counter: parseInt(this.counter) || 1,
            });
            this.result = data;
        },

        async doSteps() {
            if (!this.plaintext) throw new Error('Pesan Rahasia (Plaintext) diperlukan.');
            const body = { plaintext: this.plaintext, counter: parseInt(this.counter) || 1 };
            if (this.key) body.key = this.key;
            if (this.nonce) body.nonce = this.nonce;

            const data = await this.apiPost('{{ route("chacha20.steps") }}', body);
            this.stepsData = data;
            this.key = data.key_hex;
            this.nonce = data.nonce_hex;
            this.buildRoundList(data);
        },

        buildRoundList(data) {
            // Gunakan semua round_logs agar proses ARX (quarter round) bisa dilihat step-by-step
            
            // --- DIFFUSION TRACKING LOGIC ---
            let currentCellColors = Array(16).fill().map((_, i) => {
                if (i < 4) return new Set(['constant']);
                if (i < 12) return new Set(['key']);
                if (i === 12) return new Set(['counter']);
                return new Set(['nonce']);
            });

            this.initialCellColors = currentCellColors.map(s => Array.from(s));

            this.allRounds = data.round_logs.map(log => {
                if (!log.state_words && log.state_matrix) {
                    log.state_words = log.state_matrix.flat();
                }
                
                // Hitung persebaran warna (diffusion) berdasarkan operasi ARX
                if (log.type === 'quarter_round_detail' && log.indices) {
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

                // Simpan snapshot warna untuk ronde ini
                log.cellColors = currentCellColors.map(s => Array.from(s));

                return log;
            });
            this.currentRoundIndex = 0;
            this.displayRound(0);
        },

        displayRound(idx) {
            const entry = this.allRounds[idx];
            if (!entry) return;

            this.currentRound = entry.round;
            this.currentStateWords = entry.state_words ?? [];
            this.currentRoundLabel = entry.description ?? 'State Initialization';

            if (this.currentRound === -1) {
                this.currentCellColorsArray = this.initialCellColors || [];
            } else {
                this.currentCellColorsArray = entry.cellColors || [];
            }

            // Populate ARX micro-steps for quarter_round_detail entries
            this.currentMicroSteps = entry.arx_micro_steps || [];
            this.microStepIdx = 0;

            if (idx > 0) {
                const prev = this.allRounds[idx - 1].state_words ?? [];
                this.changedIndices = this.currentStateWords
                    .map((w, i) => w !== prev[i] ? i : -1)
                    .filter(i => i >= 0);
            } else {
                this.changedIndices = [];
            }
        },

        goToRound(round) {
            const idx = this.allRounds.findIndex(r => r?.round === round);
            if (idx >= 0) { this.currentRoundIndex = idx; this.displayRound(idx); }
        },
        prevRound() {
            if (this.currentRoundIndex > 0) { this.currentRoundIndex--; this.displayRound(this.currentRoundIndex); }
        },
        nextRound() {
            if (this.currentRoundIndex < this.allRounds.length - 1) { this.currentRoundIndex++; this.displayRound(this.currentRoundIndex); }
        },

        // --- ARX Micro-Step Methods ---
        currentMicroOp() {
            return this.currentMicroSteps[this.microStepIdx]?.op || 'ADD';
        },
        getMicroStepGroupLabel() {
            const idx = this.microStepIdx;
            const group = Math.floor(idx / 3) + 1;
            return `(Grup ${group}/4)`;
        },
        prevMicroStep() {
            if (this.microStepIdx > 0) this.microStepIdx--;
        },
        nextMicroStep() {
            if (this.microStepIdx < this.currentMicroSteps.length - 1) this.microStepIdx++;
        },

        // --- Educational Storytelling Logic ---
        getStoryTitle() {
            if (this.currentRound === -1) return "Langkah 1: Inisialisasi Matrix";
            if (this.currentRound === 'final') return "Langkah Terakhir: Penjumlahan Akhir";
            const type = this.allRounds[this.currentRoundIndex]?.type;
            if (type === 'quarter_round_detail') return `Operasi ARX (Ronde ${this.currentRound})`;
            if (type === 'column') return `Ronde ${this.currentRound}: Selesai Column Round`;
            return `Ronde ${this.currentRound}: Selesai Diagonal Round`;
        },

        getStoryText() {
            if (this.currentRound === -1) {
                return `
                    <p>Matriks 4x4 (16 kata, 32-bit) ini adalah inti dari ChaCha20. Perhatikan susunannya:</p>
                    <ul style="margin-left: 20px; margin-top: 10px;">
                        <li><span style="color:#888; font-weight:bold;">Baris 1 (Garis Abu-abu):</span> Konstanta statis <em>"expand 32-byte k"</em>.</li>
                        <li><span style="color:#e50914; font-weight:bold;">Baris 2 & 3 (Garis Merah):</span> Secret Key Anda (256-bit).</li>
                        <li><span style="color:#3b82f6; font-weight:bold;">Sel 12 (Garis Biru):</span> Block Counter.</li>
                        <li><span style="color:#46d369; font-weight:bold;">Sel 13-15 (Garis Hijau):</span> Nonce (96-bit).</li>
                    </ul>
                    <p style="margin-top: 10px;">Tekan tombol <strong>Maju</strong> untuk mulai mengacak matriks ini!</p>
                `;
            }
            if (this.currentRound === 'final') {
                return `
                    <p>Setelah 20 ronde pengacakan selesai, matriks hasil acakan ditambahkan (ditambah secara matematika) dengan <strong>matriks awal (Initial State)</strong> tadi.</p>
                    <p style="margin-top: 10px;">Hasil akhir ini kemudian diubah menjadi byte stream (keystream), lalu di-XOR dengan teks asli Anda untuk menghasilkan <strong>Ciphertext (Pesan Terenkripsi)</strong> yang benar-benar acak dan aman.</p>
                `;
            }
            const type = this.allRounds[this.currentRoundIndex]?.type;
            
            if (type === 'quarter_round_detail') {
                const entry = this.allRounds[this.currentRoundIndex];
                const indices = entry.indices;
                return `
                    <p>Quarter Round sedang berjalan secara spesifik pada 4 sel: <strong>[${indices.a}], [${indices.b}], [${indices.c}], [${indices.d}]</strong>.</p>
                    <p style="margin-top: 10px;">Perhatikan keempat sel yang menyala terang di sebelah kiri! Angka di dalamnya sedang ditambahkan (Add), digeser bit-nya (Rotate), dan di-XOR secara silang sehingga nilainya berubah drastis.</p>
                `;
            }
            if (type === 'column') {
                return `
                    <p><strong>Column Round</strong> selesai dieksekusi.</p>
                    <p style="margin-top: 10px;">Sebanyak 4 operasi Quarter Round baru saja selesai mengacak nilai-nilai secara vertikal pada kolom matriks.</p>
                `;
            }
            return `
                <p><strong>Diagonal Round</strong> selesai dieksekusi.</p>
                <p style="margin-top: 10px;">Sebanyak 4 operasi Quarter Round baru saja selesai menyilang pengacakannya secara diagonal (miring) untuk menciptakan efek <em>Diffusion</em>.</p>
            `;
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

        // ── File Handling Methods ──

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
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                body: formData,
            });
            const data = await res.json();
            if (!res.ok) {
                if (data.errors) throw new Error(Object.values(data.errors).flat().join(' '));
                throw new Error(data.message || 'Gagal mengenkripsi file.');
            }

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
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                body: formData,
            });
            const data = await res.json();
            if (!res.ok) {
                if (data.errors) throw new Error(Object.values(data.errors).flat().join(' '));
                throw new Error(data.message || 'Gagal mendekripsi file.');
            }

            this.fileResult = data;
        },

        downloadFileResult() {
            if (!this.fileResult?.file_base64) return;

            const byteChars = atob(this.fileResult.file_base64);
            const byteArray = new Uint8Array(byteChars.length);
            for (let i = 0; i < byteChars.length; i++) {
                byteArray[i] = byteChars.charCodeAt(i);
            }

            const blob = new Blob([byteArray], { type: 'application/octet-stream' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = this.fileResult.result_filename || 'result';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        },

        copyFileKeys() {
            if (!this.fileResult) return;
            const text = `Key: ${this.fileResult.key_hex}\nNonce: ${this.fileResult.nonce_hex}`;
            navigator.clipboard.writeText(text).then(() => {
                alert('Key & Nonce berhasil disalin ke clipboard!');
            });
        },

        formatFileSize(bytes) {
            if (!bytes) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB'];
            let i = 0;
            let size = bytes;
            while (size >= 1024 && i < units.length - 1) { size /= 1024; i++; }
            return size.toFixed(i > 0 ? 2 : 0) + ' ' + units[i];
        },
    };
}
</script>
</body>
</html>
