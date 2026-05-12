<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Story of ChaCha20 | NakamotoX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #0B0B0C;
            --primary: #E50914;
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
        .orb-2 { width: 500px; height: 500px; background: #660099; bottom: 0; left: -100px; animation-delay: -5s; }
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
        .hero h1 { font-size: 5rem; font-weight: 900; letter-spacing: -2px; margin-bottom: 20px; text-shadow: 0 10px 30px rgba(229,9,20,0.5); }
        .hero p { font-size: 1.5rem; color: var(--text-muted); max-width: 800px; font-weight: 300; }
        .hero-subtitle { color: var(--primary); font-weight: 700; text-transform: uppercase; letter-spacing: 4px; margin-bottom: 16px; display: block;}

        /* Content Sections */
        .section { padding: 100px 4%; max-width: 1200px; margin: 0 auto; }
        .grid-2 { display: grid; grid-template-columns: 1fr; gap: 40px; align-items: center; }
        @media (min-width: 900px) { .grid-2 { grid-template-columns: 1fr 1fr; } }

        /* Glass Cards */
        .glass-card {
            background: var(--bg-glass); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass); border-radius: 16px; padding: 40px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }

        .fact-number { font-size: 6rem; font-weight: 900; color: rgba(255,255,255,0.1); line-height: 1; margin-bottom: -40px; position: relative; z-index: -1; }
        h2 { font-size: 2.5rem; font-weight: 800; margin-bottom: 24px; letter-spacing: -1px; }
        p { font-size: 1.15rem; color: var(--text-muted); margin-bottom: 20px; }
        strong { color: white; }

        /* Footer */
        .footer { text-align: center; padding: 60px 4%; border-top: 1px solid var(--border-glass); margin-top: 100px; color: var(--text-muted); }
    </style>
</head>
<body>

    <div class="bg-orbs">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
    </div>

    <nav class="navbar">
        <a href="{{ route('chacha20.index') }}" class="logo"><span>Naka</span>motoX</a>
        <a href="{{ route('chacha20.index') }}" class="btn-back">← Kembali ke Workspace</a>
    </nav>

    <header class="hero">
        <span class="hero-subtitle">The Silent Guardian</span>
        <h1>ChaCha20</h1>
        <p>Anda mungkin tidak pernah menyadarinya, tapi algoritma ini mengamankan jutaan pesan WhatsApp Anda, koneksi internet Anda, dan hampir semua ponsel pintar di planet ini setiap detiknya.</p>
    </header>

    <section class="section">
        <div class="grid-2">
            <div>
                <div class="fact-number">01</div>
                <h2>Kenapa Google Jatuh Cinta padanya?</h2>
                <p>Selama bertahun-tahun, standar emas keamanan dunia adalah algoritma bernama <strong>AES (Advanced Encryption Standard)</strong>. Namun, AES punya masalah besar: ia sangat berat dijalankan di ponsel lawas atau murah yang tidak memiliki chip akselerator khusus (hardware AES-NI).</p>
                <p>Muncullah ChaCha20. Algoritma ini dirancang murni melalui perangkat lunak (*software-based*) dan secara mengejutkan berlari <strong>3x lipat lebih cepat</strong> daripada AES di ponsel biasa, namun dengan tingkat keamanan yang sama kuatnya!</p>
                <p>Karena itu, raksasa seperti Google menjadikannya standar utama (bersama Poly1305) untuk lalu lintas koneksi HTTPS di peramban Chrome untuk perangkat mobile.</p>
            </div>
            <div class="glass-card" style="text-align: center;">
                <div style="font-size: 80px; margin-bottom: 20px;">🚀📱</div>
                <h3 style="font-size: 24px; margin-bottom: 10px;">The Speed Demon</h3>
                <p style="font-size: 16px;">ChaCha20 mampu mengenkripsi ber-megabyte data hanya dalam sekian milidetik, tanpa menguras baterai ponsel pintar Anda.</p>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="grid-2">
            <div class="glass-card" style="text-align: center; border-color: rgba(229,9,20,0.5);">
                <div style="font-size: 80px; margin-bottom: 20px;">🎲✨</div>
                <h3 style="font-size: 24px; margin-bottom: 10px;">The ARX Dance</h3>
                <p style="font-size: 16px;">Sebuah tarian matematika elegan yang terdiri dari Penambahan (<strong>A</strong>ddition), Pergeseran (<strong>R</strong>otation), dan <strong>X</strong>OR.</p>
            </div>
            <div>
                <div class="fact-number">02</div>
                <h2>Analogi Kubus Rubik (ARX)</h2>
                <p>Bagaimana cara ChaCha20 mengacak data? Bayangkan sebuah <strong>Kubus Rubik</strong> (State Matrix 4x4).</p>
                <p>Setiap sisi kubus ini sudah Anda beri warna yang sesuai (Pesan Anda, Key, dan Nonce). ChaCha20 mulai mengacak sisi tersebut dengan 3 gerakan pasti: memutarnya, menukarnya, dan membaliknya. Dalam kriptografi, ini disebut <strong>ARX (Addition, Rotation, XOR)</strong>.</p>
                <p>Gerakan ini (satu <em>Quarter Round</em>) dilakukan berulang-ulang sebanyak <strong>20 Ronde</strong>. Setelah 20 ronde, rubik tersebut akan tampak sangat berantakan dan acak. Tidak ada manusia maupun super-komputer yang bisa menebak urutan aslinya tanpa memegang "kunci rahasia" putarannya.</p>
            </div>
        </div>
    </section>

    <section class="section" style="text-align: center; max-width: 900px;">
        <div class="fact-number" style="margin-bottom: 10px;">03</div>
        <h2>Siapa yang Memakainya Hari Ini?</h2>
        <p style="font-size: 1.3rem; margin-bottom: 40px;">ChaCha20 kini merupakan standar dunia yang diakui dan digunakan di infrastruktur paling vital di bumi.</p>
        
        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px;">
            <div class="glass-card" style="flex: 1; min-width: 250px; padding: 30px;">
                <div style="font-size: 40px; margin-bottom: 15px;">🌐</div>
                <h3 style="margin-bottom: 10px;">TLS 1.3</h3>
                <p style="font-size: 14px; margin: 0;">Digunakan oleh browser untuk mengamankan koneksi website (gembok hijau di ujung URL).</p>
            </div>
            <div class="glass-card" style="flex: 1; min-width: 250px; padding: 30px;">
                <div style="font-size: 40px; margin-bottom: 15px;">🛡️</div>
                <h3 style="margin-bottom: 10px;">WireGuard VPN</h3>
                <p style="font-size: 14px; margin: 0;">Protokol VPN modern yang paling cepat dan aman saat ini, menjadikan ChaCha20 sebagai senjata utamanya.</p>
            </div>
            <div class="glass-card" style="flex: 1; min-width: 250px; padding: 30px;">
                <div style="font-size: 40px; margin-bottom: 15px;">💬</div>
                <h3 style="margin-bottom: 10px;">Messaging Apps</h3>
                <p style="font-size: 14px; margin: 0;">Banyak aplikasi chatting end-to-end terenkripsi menggunakan variasi algoritma ini untuk chat Anda.</p>
            </div>
        </div>
    </section>

    <div class="footer">
        <p>Built with ❤️ and cryptography magic | NakamotoX Kripto Simulator</p>
    </div>

</body>
</html>
