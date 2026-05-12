<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Caesar Cipher Simulator | NakamotoX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="//unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        :root {
            --bg-base: #0B0B0C; --primary: #f59e0b; --primary-hover: #fbbf24;
            --text-main: #FFFFFF; --text-muted: #B3B3B3;
            --border-glass: rgba(255,255,255,0.1); --bg-glass: rgba(20,20,20,0.45);
            --radius: 12px; --font-main: 'Inter', sans-serif;
        }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{background:var(--bg-base);color:var(--text-main);font-family:var(--font-main);font-size:15px;min-height:100vh;line-height:1.5;-webkit-font-smoothing:antialiased;overflow-x:hidden}
        .bg-orbs{position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:-1;overflow:hidden}
        .orb{position:absolute;border-radius:50%;filter:blur(80px);opacity:.4;animation:float 20s infinite ease-in-out alternate}
        .orb-1{width:500px;height:500px;background:#f59e0b;top:-100px;left:-100px}
        .orb-2{width:400px;height:400px;background:#7c3aed;bottom:10%;right:-50px;animation-delay:-5s}
        .orb-3{width:300px;height:300px;background:#06b6d4;top:40%;left:40%;animation-delay:-10s;opacity:.2}
        @keyframes float{0%{transform:translate(0,0) scale(1)}50%{transform:translate(100px,50px) scale(1.1)}100%{transform:translate(-50px,100px) scale(.9)}}
        .navbar{padding:24px 4%;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(to bottom,rgba(0,0,0,.6) 0%,transparent 100%)}
        .logo{color:var(--text-main);font-size:26px;font-weight:800;letter-spacing:-1px;text-decoration:none;text-shadow:0 0 10px rgba(255,255,255,.3)}
        .logo span{color:var(--primary)}
        .nav-links{display:flex;align-items:center;gap:16px}
        .nav-links a{color:var(--text-main);text-decoration:none;font-weight:600;font-size:14px;display:flex;align-items:center;gap:6px;transition:color .2s}
        .nav-links a:hover{color:var(--primary)}
        .hero{padding:20px 4% 40px}
        .hero h1{font-size:3rem;font-weight:800;margin-bottom:12px;letter-spacing:-1.5px}
        .hero h1 span{color:var(--primary)}
        .hero p{font-size:1.15rem;color:var(--text-muted);max-width:700px;font-weight:300}
        .container{padding:0 2% 80px;max-width:1800px;margin:0 auto}
        .pipeline-layout{display:grid;grid-template-columns:1fr;gap:20px;align-items:stretch}
        @media(min-width:1024px){.pipeline-layout{grid-template-columns:2.5fr 40px 3fr 40px 3.5fr}}
        .column-block{display:flex;flex-direction:column}
        .pipeline-divider{display:none;align-items:center;justify-content:center}
        @media(min-width:1024px){.pipeline-divider{display:flex}}
        .chevron-right{width:24px;height:24px;border-top:4px solid var(--primary);border-right:4px solid var(--primary);transform:rotate(45deg);opacity:.6;margin-top:-60px}
        .card{background-color:var(--bg-glass);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-radius:var(--radius);padding:24px;margin-bottom:24px;box-shadow:0 10px 40px rgba(0,0,0,.5),inset 0 1px 0 rgba(255,255,255,.1);border:1px solid var(--border-glass)}
        .card.h-full{flex:1;margin-bottom:0}
        .card-header-banner{background:rgba(245,158,11,.85);color:white;padding:12px 20px;margin:-24px -24px 24px -24px;border-top-left-radius:var(--radius);border-top-right-radius:var(--radius);font-size:1.1rem;font-weight:600;letter-spacing:.5px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 10px rgba(245,158,11,.3)}
        .card-header-banner.muted{background:rgba(40,40,40,.8);color:var(--text-main);box-shadow:none}
        .card-header-icon{display:inline-flex;justify-content:center;align-items:center;width:24px;height:24px;border:2px solid currentColor;border-radius:50%;font-size:14px;font-weight:bold}
        .mode-switcher{display:flex;width:100%;border:1px solid var(--border-glass);border-radius:8px;overflow:hidden;margin-bottom:24px;background:rgba(0,0,0,.2)}
        .mode-tab{flex:1;text-align:center;padding:12px 0;font-weight:500;font-size:14px;cursor:pointer;color:var(--text-muted);transition:all .2s;border-right:1px solid var(--border-glass)}
        .mode-tab:last-child{border-right:none}
        .mode-tab:hover{background:rgba(255,255,255,.1);color:var(--text-main)}
        .mode-tab.active{background-color:var(--primary);color:white}
        .form-group{margin-bottom:20px}
        label{display:block;font-size:14px;font-weight:600;color:var(--text-main);margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px}
        .edu-text{display:block;font-size:13px;color:var(--text-muted);margin-bottom:8px;font-weight:400;line-height:1.4}
        input[type=text],input[type=number],textarea{width:100%;padding:14px;background:rgba(0,0,0,.4);border:1px solid var(--border-glass);color:var(--text-main);font-size:14px;border-radius:6px;transition:all .2s;font-family:var(--font-main)}
        textarea{resize:vertical;min-height:120px;line-height:1.6}
        textarea.full-height{min-height:calc(100% - 100px);height:300px}
        input:focus,textarea:focus{outline:none;border-color:var(--primary);background:rgba(0,0,0,.6);box-shadow:0 0 15px rgba(245,158,11,.2)}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:14px 20px;font-size:15px;font-family:inherit;font-weight:600;cursor:pointer;transition:all .2s;border-radius:6px;border:none}
        .btn:disabled{opacity:.6;cursor:not-allowed}
        .btn-primary{background:var(--primary);color:white;width:100%;box-shadow:0 4px 15px rgba(245,158,11,.4)}
        .btn-primary:not(:disabled):hover{background:var(--primary-hover);transform:translateY(-1px)}
        .btn-outline{background:transparent;border:1px solid var(--border-glass);color:var(--text-main)}
        .btn-outline:not(:disabled):hover{background:rgba(255,255,255,.1)}
        .btn-sm{padding:6px 12px;font-size:13px}
        .result-box{font-size:14px;font-family:'Courier New',monospace;background:rgba(0,0,0,.6);border:1px solid var(--border-glass);border-radius:6px;padding:16px;word-break:break-all;white-space:pre-wrap;color:var(--text-main);min-height:100px;line-height:1.5;margin-bottom:16px}
        .result-box.error{border-color:var(--primary);background:rgba(245,158,11,.1);color:#ff6b6b;min-height:auto}
        .empty-state{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:var(--text-muted);text-align:center;padding:40px 20px}
        .empty-state svg{width:48px;height:48px;margin-bottom:16px;opacity:.5}
        .spinner{width:18px;height:18px;border:2px solid rgba(255,255,255,.3);border-radius:50%;border-top-color:white;animation:spin 1s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}

        /* Shift Slider */
        .shift-slider{display:flex;align-items:center;gap:12px;margin-bottom:8px}
        .shift-slider input[type=range]{flex:1;-webkit-appearance:none;height:6px;background:rgba(255,255,255,.1);border-radius:3px;outline:none}
        .shift-slider input[type=range]::-webkit-slider-thumb{-webkit-appearance:none;width:22px;height:22px;border-radius:50%;background:var(--primary);cursor:pointer;box-shadow:0 0 10px rgba(245,158,11,.5)}
        .shift-value{background:var(--primary);color:white;padding:6px 14px;border-radius:6px;font-weight:700;font-size:18px;min-width:48px;text-align:center}

        /* Alphabet Table */
        .alpha-table{display:flex;flex-direction:column;gap:2px;margin-top:12px;overflow-x:auto}
        .alpha-row{display:flex;gap:2px}
        .alpha-row-label{width:60px;min-width:60px;padding:6px 8px;font-size:11px;font-weight:600;display:flex;align-items:center;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted)}
        .alpha-cell{width:32px;min-width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-family:'Courier New',monospace;font-size:13px;font-weight:600;border-radius:4px;transition:all .3s}
        .alpha-cell.original{background:rgba(255,255,255,.05);color:var(--text-muted)}
        .alpha-cell.shifted{background:rgba(245,158,11,.15);color:var(--primary);border:1px solid rgba(245,158,11,.3)}
        .alpha-cell.highlight{background:var(--primary)!important;color:white!important;transform:scale(1.15);box-shadow:0 0 12px rgba(245,158,11,.5)}

        /* Brute Force Table */
        .bf-table{max-height:400px;overflow-y:auto;border-radius:8px;border:1px solid var(--border-glass)}
        .bf-row{display:flex;align-items:center;padding:10px 16px;border-bottom:1px solid rgba(255,255,255,.05);transition:background .2s;gap:12px}
        .bf-row:hover{background:rgba(255,255,255,.05)}
        .bf-shift{font-weight:700;color:var(--primary);min-width:60px;font-size:13px}
        .bf-text{font-family:'Courier New',monospace;font-size:13px;word-break:break-all;flex:1}
        .bf-row.match{background:rgba(245,158,11,.1);border-left:3px solid var(--primary)}

        /* Step Log */
        .step-log{display:flex;flex-wrap:wrap;gap:4px;margin-top:12px}
        .step-char{display:flex;flex-direction:column;align-items:center;padding:8px 6px;border-radius:6px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.05);min-width:36px;transition:all .2s}
        .step-char:hover{border-color:var(--primary);background:rgba(245,158,11,.1)}
        .step-orig{font-size:16px;font-weight:600;color:var(--text-muted);font-family:'Courier New',monospace}
        .step-arrow{font-size:10px;color:rgba(255,255,255,.3);margin:2px 0}
        .step-result{font-size:16px;font-weight:700;color:var(--primary);font-family:'Courier New',monospace}
        .step-char.unchanged .step-result{color:var(--text-muted)}
    </style>
</head>
<body x-data="caesarApp()" x-init="init()">

    <div class="bg-orbs"><div class="orb orb-1"></div><div class="orb orb-2"></div><div class="orb orb-3"></div></div>

    <nav class="navbar">
        <a href="{{ route('caesar.index') }}" class="logo"><span>Naka</span>motoX</a>
        <div class="nav-links">
            <a href="{{ route('chacha20.index') }}">🔐 ChaCha20</a>
            <a href="{{ route('caesar.index') }}" style="color:var(--primary)">🏛️ Caesar</a>
        </div>
    </nav>

    <section class="hero">
        <h1>Caesar <span>Cipher</span></h1>
        <p>Algoritma substitusi klasik. Setiap huruf digeser sebanyak N posisi dalam alfabet. Sederhana namun menjadi fondasi kriptografi modern.</p>
    </section>

    <main class="container">
        <div class="pipeline-layout">

            <!-- COLUMN 1: INPUT -->
            <div class="column-block">
                <div class="card h-full">
                    <div class="card-header-banner muted">
                        <span class="card-header-icon">1</span>
                        <span>INPUT DATA</span>
                    </div>
                    <div class="form-group" x-show="mode === 'encrypt'" style="height:100%">
                        <label>Plaintext</label>
                        <span class="edu-text">Masukkan teks yang ingin dienkripsi.</span>
                        <textarea class="full-height" x-model="plaintext" placeholder="Contoh: Hello, World!"></textarea>
                    </div>
                    <div class="form-group" x-show="mode === 'decrypt' || mode === 'brute'" style="height:100%">
                        <label>Ciphertext</label>
                        <span class="edu-text" x-text="mode === 'brute' ? 'Masukkan teks terenkripsi untuk di-crack.' : 'Masukkan teks yang sudah dienkripsi.'"></span>
                        <textarea class="full-height" x-model="ciphertextInput" placeholder="Contoh: Khoor, Zruog!"></textarea>
                    </div>
                </div>
            </div>

            <div class="pipeline-divider"><div class="chevron-right"></div></div>

            <!-- COLUMN 2: CONFIG -->
            <div class="column-block">
                <div class="card h-full">
                    <div class="card-header-banner">
                        <span class="card-header-icon">2</span>
                        <span>KONFIGURASI</span>
                    </div>

                    <div class="mode-switcher">
                        <div class="mode-tab" :class="{active: mode==='encrypt'}" @click="mode='encrypt'; resetResult()">Encrypt</div>
                        <div class="mode-tab" :class="{active: mode==='decrypt'}" @click="mode='decrypt'; resetResult()">Decrypt</div>
                        <div class="mode-tab" :class="{active: mode==='brute'}" @click="mode='brute'; resetResult()">🔓 Brute Force</div>
                    </div>

                    <div class="form-group">
                        <label>Algorithm</label>
                        <input type="text" value="Caesar Cipher (Substitution)" disabled style="background:rgba(0,0,0,.2);color:var(--text-muted);cursor:not-allowed;border-color:transparent">
                    </div>

                    <div class="form-group" x-show="mode !== 'brute'">
                        <label>Shift Key (0-25)</label>
                        <span class="edu-text">Jumlah pergeseran huruf. Contoh klasik: 3 (digunakan Julius Caesar).</span>
                        <div class="shift-slider">
                            <input type="range" min="0" max="25" x-model.number="shift">
                            <div class="shift-value" x-text="shift"></div>
                        </div>
                    </div>

                    <!-- Mini Alphabet Preview -->
                    <div class="form-group" x-show="mode !== 'brute'">
                        <label>Tabel Substitusi</label>
                        <span class="edu-text">Pemetaan huruf asli → huruf terenkripsi.</span>
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
                        <span class="edu-text">Mencoba semua 26 kemungkinan shift untuk memecahkan ciphertext tanpa mengetahui kunci.</span>
                        <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);border-radius:8px;padding:16px;margin-top:8px">
                            <div style="font-size:28px;text-align:center;margin-bottom:8px">🔓</div>
                            <div style="text-align:center;font-size:13px;color:var(--text-muted)">Caesar cipher hanya memiliki <strong style="color:var(--primary)">26 kemungkinan kunci</strong>, sehingga mudah dipecahkan dengan mencoba semuanya.</div>
                        </div>
                    </div>

                    <div style="flex-grow:1"></div>

                    <div class="form-group" x-show="mode === 'encrypt' || mode === 'decrypt'">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;text-transform:none;letter-spacing:0">
                            <input type="checkbox" x-model="showSteps" style="width:auto;accent-color:var(--primary)">
                            Tampilkan Visualisasi Step-by-Step
                        </label>
                    </div>

                    <button class="btn btn-primary" style="margin-top:12px;padding:18px" @click="run()" :disabled="loading">
                        <span x-show="loading" class="spinner"></span>
                        <span x-show="!loading && mode==='encrypt'">Encrypt →</span>
                        <span x-show="!loading && mode==='decrypt'">Decrypt →</span>
                        <span x-show="!loading && mode==='brute'">🔓 Crack Semua Shift →</span>
                    </button>
                </div>
            </div>

            <div class="pipeline-divider"><div class="chevron-right"></div></div>

            <!-- COLUMN 3: OUTPUT -->
            <div class="column-block">
                <div class="card h-full" style="display:flex;flex-direction:column">
                    <div class="card-header-banner muted">
                        <span class="card-header-icon">3</span>
                        <span>OUTPUT RESULT</span>
                    </div>

                    <div x-show="error" class="result-box error"><strong>Error:</strong> <span x-text="error"></span></div>

                    <div x-show="!result && !bruteResult && !error" class="empty-state">
                        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M19,3H5C3.89,3 3,3.89 3,5V19C3,20.1 3.9,21 5,21H19C20.1,21 21,20.1 21,19V5C21,3.89 20.1,3 19,3M19,19H5V5H19V19M17,17H7V15H17V17M17,13H7V11H17V13M17,9H7V7H17V9Z"/></svg>
                        <p>Menunggu eksekusi.<br>Hasil akan ditampilkan di sini.</p>
                    </div>

                    <!-- Encrypt/Decrypt Result -->
                    <div x-show="result && mode !== 'brute'" style="flex-grow:1">
                        <template x-if="mode === 'encrypt' && result">
                            <div>
                                <label>Ciphertext</label>
                                <div class="result-box" x-text="result.ciphertext"></div>
                                <div style="display:flex;gap:8px">
                                    <button class="btn btn-outline btn-sm" style="flex:1" @click="copyText(result.ciphertext)">📋 Salin</button>
                                    <button class="btn btn-outline btn-sm" style="flex:1" @click="useForDecrypt()">🔄 Decrypt Ini</button>
                                </div>
                            </div>
                        </template>
                        <template x-if="mode === 'decrypt' && result">
                            <div>
                                <label>Plaintext Asli</label>
                                <div class="result-box" style="font-family:var(--font-main);font-size:16px" x-text="result.plaintext"></div>
                            </div>
                        </template>

                        <!-- Step-by-Step Visualization -->
                        <template x-if="result && result.step_logs && result.step_logs.length > 0">
                            <div style="margin-top:20px">
                                <label>Visualisasi Substitusi</label>
                                <span class="edu-text">Setiap huruf diganti sesuai tabel substitusi. Karakter non-huruf tidak berubah.</span>
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

                    <!-- Brute Force Result -->
                    <div x-show="bruteResult" style="flex-grow:1">
                        <label>Semua 26 Kemungkinan Dekripsi</label>
                        <span class="edu-text">Cari baris yang menghasilkan teks bermakna!</span>
                        <div class="bf-table">
                            <template x-for="(item, i) in (bruteResult?.results || [])" :key="i">
                                <div class="bf-row" :class="{'match': selectedBfShift === item.shift}" @click="selectedBfShift = item.shift">
                                    <div class="bf-shift" x-text="'Shift ' + item.shift"></div>
                                    <div class="bf-text" x-text="item.plaintext"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

<script>
function caesarApp() {
    return {
        csrfToken: document.querySelector('meta[name=csrf-token]').content,
        mode: 'encrypt',
        loading: false,
        plaintext: '',
        ciphertextInput: '',
        shift: 3,
        showSteps: false,
        error: null,
        result: null,
        bruteResult: null,
        selectedBfShift: -1,

        get shiftedAlphabet() {
            const alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            const s = this.shift % 26;
            return (alpha.slice(s) + alpha.slice(0, s)).split('');
        },

        init() {},

        resetResult() { this.result = null; this.bruteResult = null; this.error = null; },

        async run() {
            this.loading = true; this.error = null; this.result = null; this.bruteResult = null;
            try {
                if (this.mode === 'encrypt') await this.doEncrypt();
                else if (this.mode === 'decrypt') await this.doDecrypt();
                else await this.doBrute();
            } catch (err) {
                this.error = err.message || 'Terjadi error.';
            } finally { this.loading = false; }
        },

        async doEncrypt() {
            if (!this.plaintext) throw new Error('Plaintext tidak boleh kosong.');
            const data = await this.apiPost('{{ route("caesar.encrypt") }}', {
                plaintext: this.plaintext, shift: this.shift, show_steps: this.showSteps,
            });
            this.result = data;
        },

        async doDecrypt() {
            if (!this.ciphertextInput) throw new Error('Ciphertext tidak boleh kosong.');
            const data = await this.apiPost('{{ route("caesar.decrypt") }}', {
                ciphertext: this.ciphertextInput, shift: this.shift, show_steps: this.showSteps,
            });
            this.result = data;
        },

        async doBrute() {
            if (!this.ciphertextInput) throw new Error('Ciphertext tidak boleh kosong.');
            const data = await this.apiPost('{{ route("caesar.brute-force") }}', {
                ciphertext: this.ciphertextInput,
            });
            this.bruteResult = data;
        },

        useForDecrypt() {
            if (!this.result?.ciphertext) return;
            this.ciphertextInput = this.result.ciphertext;
            this.mode = 'decrypt';
            this.result = null;
        },

        copyText(text) {
            navigator.clipboard.writeText(text).then(() => alert('Berhasil disalin!'));
        },

        async apiPost(url, body) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':this.csrfToken,'Accept':'application/json'},
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
