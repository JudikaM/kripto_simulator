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
            padding: 16px 8px; text-align: center; color: var(--text-main);
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
        .legend-key { border-bottom: 3px solid #46d369; }
        .legend-counter { border-bottom: 3px solid #3b82f6; }
        .legend-nonce { border-bottom: 3px solid #f59e0b; }

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
        <a href="#" class="logo"><span>Naka</span>motoX</a>
        <div class="status-badge" :class="serviceStatus === 'Online' ? 'online' : 'offline'">
            <span x-show="serviceStatus === 'Online'">🟢</span>
            <span x-show="serviceStatus !== 'Online'">🔴</span>
            Engine: <span x-text="serviceStatus">Checking...</span>
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

                    <div class="form-group" x-show="mode !== 'decrypt'" style="height: 100%;">
                        <label>Plaintext</label>
                        <span class="educational-text">Masukkan teks rahasia yang ingin diproses.</span>
                        <textarea class="full-height" x-model="plaintext" placeholder="Contoh: The quick brown fox jumps over the lazy dog."></textarea>
                    </div>

                    <div class="form-group" x-show="mode === 'decrypt'" style="height: 100%;">
                        <label>Ciphertext (Hex)</label>
                        <span class="educational-text">Masukkan data yang sudah terenkripsi.</span>
                        <textarea class="mono-font full-height" x-model="ciphertextInput" placeholder="Contoh: a1b2c3d4e5f6..."></textarea>
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

                    <div x-show="!result && !stepsData && !error" class="empty-state">
                        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M19,3H5C3.89,3 3,3.89 3,5V19C3,20.1 3.9,21 5,21H19C20.1,21 21,20.1 21,19V5C21,3.89 20.1,3 19,3M19,19H5V5H19V19M17,17H7V15H17V17M17,13H7V11H17V13M17,9H7V7H17V9Z" /></svg>
                        <p x-show="mode !== 'steps'">Menunggu eksekusi *pipeline*.<br>Hasil akan ditampilkan di sini.</p>
                        <p x-show="mode === 'steps'">Klik tombol di kolom tengah untuk membuka jendela visualizer pop-up.</p>
                    </div>

                    <div x-show="result && mode !== 'steps'" style="flex-grow: 1;">
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

                    <!-- Info Box Khusus -->
                    <div class="arx-box" x-show="currentRound >= 0 && currentRound !== 'final'">
                        <div class="arx-title">⚡ Operasi Inti: ARX</div>
                        <div class="arx-desc">
                            Sel yang <strong>menyala merah</strong> di sebelah kiri sedang dimodifikasi dengan operasi:<br>
                            1. <strong>Addition</strong> (Penambahan)<br>
                            2. <strong>XOR</strong> (Exclusive OR)<br>
                            3. <strong>Rotation</strong> (Pergeseran bit)
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

        allRounds: [],
        currentRoundIndex: 0,
        currentRound: -1,
        currentStateWords: [],
        currentRoundLabel: '',
        changedIndices: [],

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

            try {
                if (this.mode === 'encrypt') await this.doEncrypt();
                else if (this.mode === 'decrypt') await this.doDecrypt();
                else {
                    await this.doSteps();
                    this.showModal = true; // Otomatis buka pop-up jika sukses
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
            this.allRounds = [ data.initial_state, ...data.round_summaries, data.final_state ].filter(Boolean);
            this.currentRoundIndex = 0;
            this.displayRound(0);
        },

        displayRound(idx) {
            const entry = this.allRounds[idx];
            if (!entry) return;

            this.currentRound = entry.round;
            this.currentStateWords = entry.state_words ?? [];
            this.currentRoundLabel = entry.description ?? 'State Initialization';

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

        // --- Educational Storytelling Logic ---
        getStoryTitle() {
            if (this.currentRound === -1) return "Langkah 1: Inisialisasi Matrix";
            if (this.currentRound === 'final') return "Langkah Terakhir: Penjumlahan Akhir";
            const type = this.allRounds[this.currentRoundIndex]?.type;
            if (type === 'column') return `Ronde ${this.currentRound}: Column Round`;
            return `Ronde ${this.currentRound}: Diagonal Round`;
        },

        getStoryText() {
            if (this.currentRound === -1) {
                return `
                    <p>Matriks 4x4 (16 kata, 32-bit) ini adalah inti dari ChaCha20. Perhatikan susunannya:</p>
                    <ul style="margin-left: 20px; margin-top: 10px;">
                        <li><span style="color:#888; font-weight:bold;">Baris 1 (Garis Abu-abu):</span> Konstanta statis <em>"expand 32-byte k"</em>.</li>
                        <li><span style="color:#46d369; font-weight:bold;">Baris 2 & 3 (Garis Hijau):</span> Secret Key Anda (256-bit).</li>
                        <li><span style="color:#3b82f6; font-weight:bold;">Sel 12 (Garis Biru):</span> Block Counter.</li>
                        <li><span style="color:#f59e0b; font-weight:bold;">Sel 13-15 (Garis Oranye):</span> Nonce (96-bit).</li>
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
            if (type === 'column') {
                return `
                    <p>Dalam <strong>Column Round</strong>, algoritma mengacak nilai-nilai secara vertikal.</p>
                    <p style="margin-top: 10px;">Kolom 1, 2, 3, dan 4 diproses satu per satu secara independen menggunakan rumus ARX untuk menyebarkan bit-bit kunci ke seluruh matriks.</p>
                `;
            }
            return `
                <p>Dalam <strong>Diagonal Round</strong>, algoritma menyilang pengacakannya secara diagonal (miring).</p>
                <p style="margin-top: 10px;">Ini memastikan bahwa perubahan pada satu kolom akan segera memengaruhi kolom-kolom lainnya, mencapai apa yang disebut efek <em>Diffusion</em> (penyebaran) yang kuat dalam kriptografi.</p>
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
    };
}
</script>
</body>
</html>
