<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Story of Kripto | NakamotoX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #0B0B0C;
            --primary: #E50914;
            --primary-caesar: #f59e0b;
            --text-main: #FFFFFF;
            --text-muted: #B3B3B3;
            --border-glass: rgba(255, 255, 255, 0.1);
            --bg-glass: rgba(20, 20, 20, 0.45);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            background-color: var(--bg-base); color: var(--text-main);
            font-family: 'Inter', sans-serif; line-height: 1.6;
            overflow-x: hidden; scroll-behavior: smooth;
        }

        /* Ambient Orbs */
        .bg-orbs { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; overflow: hidden; }
        .orb { position: absolute; border-radius: 50%; filter: blur(100px); opacity: 0.3; animation: float 20s infinite ease-in-out alternate; }
        .orb-1 { width: 600px; height: 600px; background: #E50914; top: -200px; right: -100px; }
        .orb-2 { width: 500px; height: 500px; background: #f59e0b; bottom: 0; left: -100px; animation-delay: -5s; }
        .orb-3 { width: 400px; height: 400px; background: #660099; top: 30%; right: 40%; animation-delay: -10s; opacity: 0.15; }
        @keyframes float { 0% { transform: translate(0, 0) scale(1); } 100% { transform: translate(-100px, 100px) scale(1.1); } }

        /* Navbar */
        .navbar {
            padding: 24px 4%; display: flex; justify-content: space-between; align-items: center;
            background: linear-gradient(to bottom, rgba(0,0,0,0.8) 0%, transparent 100%);
            position: fixed; width: 100%; top: 0; z-index: 100;
        }
        .logo { color: var(--text-main); font-size: 26px; font-weight: 800; text-decoration: none; }
        .logo span { color: var(--primary); }
        .btn-back {
            color: white; text-decoration: none; font-weight: 600; padding: 10px 20px;
            background: rgba(255,255,255,0.1); border-radius: 30px; transition: background 0.3s;
            border: 1px solid var(--border-glass); backdrop-filter: blur(10px);
        }
        .btn-back:hover { background: var(--primary); border-color: var(--primary); }

        /* Cinematic Hero */
        .hero {
            height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center;
            text-align: center; padding: 0 4%;
        }
        .hero h1 { font-size: 4.5rem; font-weight: 900; letter-spacing: -2px; margin-bottom: 20px; text-shadow: 0 10px 30px rgba(229,9,20,0.5); }
        .hero p { font-size: 1.5rem; color: var(--text-muted); max-width: 800px; font-weight: 300; }
        .hero-subtitle { color: var(--primary); font-weight: 700; text-transform: uppercase; letter-spacing: 4px; margin-bottom: 16px; display: block;}

        /* Content Sections */
        .section { padding: 80px 4%; max-width: 1200px; margin: 0 auto; }
        .grid-2 { display: grid; grid-template-columns: 1fr; gap: 40px; align-items: center; }
        @media (min-width: 900px) { .grid-2 { grid-template-columns: 1fr 1fr; } }
        .grid-2.reverse > div:first-child { order: 2; }
        .grid-2.reverse > div:last-child { order: 1; }
        @media (min-width: 900px) {
            .grid-2.reverse > div:first-child { order: 1; }
            .grid-2.reverse > div:last-child { order: 2; }
        }

        /* Glass Cards */
        .glass-card {
            background: var(--bg-glass); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass); border-radius: 16px; padding: 40px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }
        .glass-card.caesar { border-color: rgba(245, 158, 11, 0.4); box-shadow: 0 20px 50px rgba(245, 158, 11, 0.1); }
        .glass-card.chacha { border-color: rgba(229, 9, 20, 0.4); box-shadow: 0 20px 50px rgba(229, 9, 20, 0.1); }

        .fact-number { font-size: 6rem; font-weight: 900; color: rgba(255,255,255,0.1); line-height: 1; margin-bottom: -40px; position: relative; z-index: -1; }
        h2 { font-size: 2.5rem; font-weight: 800; margin-bottom: 24px; letter-spacing: -1px; }
        .text-caesar { color: var(--primary-caesar); }
        .text-chacha { color: var(--primary); }
        p { font-size: 1.15rem; color: var(--text-muted); margin-bottom: 20px; }
        strong { color: white; }

        .timeline-line { width: 4px; height: 100px; background: linear-gradient(to bottom, var(--primary-caesar), var(--primary)); margin: 40px auto; border-radius: 2px;}

        /* Footer */
        .footer { text-align: center; padding: 60px 4%; border-top: 1px solid var(--border-glass); margin-top: 100px; color: var(--text-muted); }
    </style>
</head>
<body>

    <div class="bg-orbs">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <nav class="navbar">
        <a href="{{ route('simulator.index') }}" class="logo"><span>Naka</span>motoX</a>
        <a href="{{ route('simulator.index') }}" class="btn-back">← Kembali ke Workspace</a>
    </nav>

    <header class="hero">
        <span class="hero-subtitle">Evolusi Kriptografi</span>
        <h1>Dari Romawi hingga Era Digital</h1>
        <p>Sebuah perjalanan seni menyembunyikan pesan rahasia, melintasi ribuan tahun dari medan perang kuno hingga menembus serat optik internet modern.</p>
    </header>

    <!-- CAESAR CIPHER SECTION -->
    <section class="section" id="caesar">
        <div class="grid-2">
            <div>
                <div class="fact-number" style="color: rgba(245, 158, 11, 0.1);">58 SM</div>
                <h2>Lahirnya Kriptografi Kuno: <span class="text-caesar">Caesar Cipher</span></h2>
                <p>Jauh sebelum ada komputer, Julius Caesar membutuhkan cara agar pesan rahasia militernya tidak bisa dibaca oleh musuh (meskipun kurirnya tertangkap).</p>
                <p>Solusinya sangat sederhana namun jenius pada masanya: ia <strong>menggeser</strong> setiap huruf abjad sejauh 3 posisi. Huruf A menjadi D, B menjadi E, dan seterusnya.</p>
                <p>Karena pada masa itu sangat sedikit orang yang bisa membaca, dan metode ini belum pernah terbayangkan oleh musuh, pesan-pesan Caesar aman tanpa perlu mesin enkripsi apa pun.</p>
            </div>
            <div class="glass-card caesar" style="text-align: center;">
                <div style="font-size: 80px; margin-bottom: 20px;">🏛️📜</div>
                <h3 style="font-size: 24px; margin-bottom: 10px;">The Substitution Cipher</h3>
                <p style="font-size: 16px;">Kelemahan terbesar Caesar Cipher adalah jumlah kemungkinan kuncinya yang hanya 26. Di era komputasi modern, sandi ini bisa dipecahkan seketika menggunakan <strong>Brute Force</strong>.</p>
            </div>
        </div>
    </section>

    <div class="timeline-line"></div>

    <!-- CHACHA20 SECTION -->
    <section class="section" id="chacha20">
        <div class="grid-2 reverse">
            <div class="glass-card chacha" style="text-align: center;">
                <div style="font-size: 80px; margin-bottom: 20px;">🚀📱</div>
                <h3 style="font-size: 24px; margin-bottom: 10px;">The Speed Demon</h3>
                <p style="font-size: 16px;">ChaCha20 mengenkripsi data menggunakan operasi ARX (Addition, Rotation, XOR) yang luar biasa cepat, menghemat baterai ponsel pintar Anda.</p>
            </div>
            <div>
                <div class="fact-number" style="color: rgba(229, 9, 20, 0.1);">2008</div>
                <h2>Sang Penjaga Internet Modern: <span class="text-chacha">ChaCha20</span></h2>
                <p>Ribuan tahun berlalu, data yang kita kirim bukan lagi gulungan perkamen, melainkan jutaan *gigabyte* foto, *chat*, dan informasi kartu kredit. Standar enkripsi sebelumnya (AES) terlalu berat untuk perangkat *mobile* murah.</p>
                <p>Muncullah <strong>ChaCha20</strong>. Dirancang oleh Daniel J. Bernstein, algoritma ini berjalan murni sebagai *software* dan berlari <strong>3x lipat lebih cepat</strong> daripada AES di ponsel biasa, dengan tingkat keamanan yang sama kuatnya (kunci 256-bit).</p>
                <p>Bahkan super-komputer terkuat saat ini pun butuh miliaran tahun untuk memecahkan sandinya.</p>
            </div>
        </div>
    </section>

    <!-- ARX EXPLANATION -->
    <section class="section">
        <div class="grid-2">
            <div>
                <h2>Analogi Kubus Rubik (ARX)</h2>
                <p>Bagaimana cara ChaCha20 mengacak data sedemikian kuat namun sangat cepat? Bayangkan sebuah <strong>Kubus Rubik</strong> (State Matrix 4x4).</p>
                <p>Setiap sisi kubus ini mewakili Pesan Anda, Kunci, dan Nonce. ChaCha20 mulai mengacaknya dengan 3 gerakan pasti: menambah, memutar, dan menyilang (<strong>A</strong>ddition, <strong>R</strong>otation, <strong>X</strong>OR).</p>
                <p>Gerakan ini (<em>Quarter Round</em>) diulang sebanyak <strong>20 Ronde</strong>. Setelah selesai, rubik tersebut akan tampak sangat acak tak tertebak, lalu hasilnya dicampur dengan pesan asli Anda.</p>
            </div>
            <div class="glass-card chacha" style="text-align: center;">
                <div style="font-size: 80px; margin-bottom: 20px;">🎲✨</div>
                <h3 style="font-size: 24px; margin-bottom: 10px;">The ARX Dance</h3>
                <p style="font-size: 16px;">Pendekatan ARX menghalau serangan kriptanalisis canggih tanpa memerlukan tabel substitusi memori (S-Box) yang memakan performa komputasi.</p>
            </div>
        </div>
    </section>

    <!-- USAGE SECTION -->
    <section class="section" style="text-align: center; max-width: 900px;">
        <div class="fact-number" style="margin-bottom: 10px; color: rgba(255,255,255,0.05);">NOW</div>
        <h2>Siapa yang Memakainya Hari Ini?</h2>
        <p style="font-size: 1.3rem; margin-bottom: 40px;">ChaCha20 kini merupakan standar dunia yang diakui dan digunakan di infrastruktur paling vital di bumi.</p>
        
        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px;">
            <div class="glass-card" style="flex: 1; min-width: 250px; padding: 30px;">
                <div style="font-size: 40px; margin-bottom: 15px;">🌐</div>
                <h3 style="margin-bottom: 10px;">TLS 1.3 (Browser)</h3>
                <p style="font-size: 14px; margin: 0;">Digunakan oleh Chrome dan browser lain untuk mengamankan koneksi website (gembok hijau di URL).</p>
            </div>
            <div class="glass-card" style="flex: 1; min-width: 250px; padding: 30px;">
                <div style="font-size: 40px; margin-bottom: 15px;">🛡️</div>
                <h3 style="margin-bottom: 10px;">WireGuard VPN</h3>
                <p style="font-size: 14px; margin: 0;">Protokol VPN modern yang paling cepat dan aman saat ini, menjadikan ChaCha20 sebagai senjata utamanya.</p>
            </div>
            <div class="glass-card" style="flex: 1; min-width: 250px; padding: 30px;">
                <div style="font-size: 40px; margin-bottom: 15px;">💬</div>
                <h3 style="margin-bottom: 10px;">Messaging Apps</h3>
                <p style="font-size: 14px; margin: 0;">Infrastruktur modern dan aplikasi *end-to-end encryption* mulai bergantung pada algoritma turunan ini.</p>
            </div>
        </div>
    </section>

    <div class="footer">
        <p>Built with ❤️ and cryptography magic | NakamotoX Kripto Simulator</p>
    </div>

</body>
</html>
