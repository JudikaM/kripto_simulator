# 🔐 Kripto Simulator

> Simulator edukasional terpadu untuk algoritma kriptografi, dibangun sebagai proyek mata kuliah Kriptografi.

Aplikasi web *Single Page Application* (SPA) yang memungkinkan pengguna mengenkripsi dan mendekripsi pesan menggunakan berbagai algoritma kriptografi. Dilengkapi visualisasi *step-by-step* proses internal algoritma untuk keperluan pembelajaran yang sangat mendalam dan interaktif.

## ✨ Fitur Utama

- **Unified Workspace** — Antarmuka pengguna sinematik 3-kolom bergaya *pipeline* (Input ➜ Proses ➜ Output) yang menggabungkan semua algoritma dalam satu halaman tanpa perlu *reload*.
- **ChaCha20 Stream Cipher** — Implementasi murni Python sesuai [RFC 8439](https://datatracker.ietf.org/doc/html/rfc8439), tanpa library kriptografi eksternal.
  - *State Matrix Visualizer*: Pop-up interaktif untuk melihat modifikasi matriks 4x4 secara detail.
  - *ARX Micro-Steps*: Pembedahan operasi Penambahan, Rotasi, dan XOR lengkap dengan animasi difusi warna (Avalanche Effect).
  - *File Encryption*: Dukungan enkripsi dan dekripsi file dokumen (drag & drop).
- **Caesar Cipher** — Algoritma substitusi klasik peninggalan Romawi Kuno.
  - *Hacker Terminal Brute Force*: Animasi peretasan *real-time* ala terminal console yang menembus 26 kemungkinan kunci (shift) secara otomatis.
- **The Story of Kripto** — Halaman edukasi (`/learn`) yang menceritakan evolusi kriptografi dari era Romawi hingga penjaga gembok internet modern.
- **Desain UI Premium (Glassmorphism)** — Antarmuka modern yang secara dinamis mengubah warna tema berdasarkan algoritma yang dipilih (Merah untuk ChaCha20, Emas untuk Caesar).

## 🏗️ Arsitektur

```
┌──────────────────────────────┐
│      Browser (Frontend)      │
│  Alpine.js + CSS Variables   │
└──────────────┬───────────────┘
               │ HTTP :8000
┌──────────────▼───────────────┐
│       Laravel 12 (PHP)       │
│  Validation + SPA Controller │
│         API Gateway          │
└──────────────┬───────────────┘
               │ HTTP :8001 (internal)
┌──────────────▼───────────────┐
│        Python FastAPI        │
│  ChaCha20 & Caesar Engine    │
│     Pure Implementation      │
└──────────────────────────────┘
```

| Layer | Teknologi | Fungsi |
|-------|-----------|--------|
| Frontend | Alpine.js, Vanilla CSS | UI/UX sinematik (Glassmorphism), layout pipeline 3-kolom, visualisasi matrix edukatif, terminal cracking. |
| API Gateway | Laravel 12, PHP 8.3 | Validasi input tingkat lanjut, routing terpadu, error handling. |
| Crypto Engine | Python 3.11, FastAPI | Eksekusi algoritma inti ChaCha20 & Caesar Cipher (pure, no external crypto libs). |

## 📋 Prerequisites

- **PHP** ≥ 8.3
- **Composer** ≥ 2.x
- **Python** ≥ 3.11

## 🚀 Cara Menjalankan (Lokal)

### 1. Clone & Install Dependencies

```bash
# Clone repo
git clone https://github.com/<username>/kripto-simulator.git
cd kripto-simulator

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env
php artisan key:generate
```

### 2. Konfigurasi Database (Lokal)

Buat file SQLite kosong untuk memenuhi requirement Laravel:

```bash
# Windows
type nul > database/database.sqlite

# Linux / Mac
touch database/database.sqlite
```

### 3. Install Python Dependencies

```bash
cd chacha20-api
pip install fastapi uvicorn pydantic
```

### 4. Jalankan (Membutuhkan 2 Terminal)

```bash
# Terminal 1 — Python Crypto Engine
cd chacha20-api
python -m uvicorn main:app --host 127.0.0.1 --port 8001

# Terminal 2 — Laravel Web Server
cd kripto-simulator
php artisan serve --port=8000
```

### 5. Buka Browser

Akses aplikasi di: **`http://127.0.0.1:8000`**

## 📡 API Endpoints (Python Backend)

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| `GET` | `/chacha20/keygen` | Generate key 256-bit + nonce 96-bit |
| `POST` | `/chacha20/encrypt` | Enkripsi plaintext → ciphertext |
| `POST` | `/chacha20/decrypt` | Dekripsi ciphertext → plaintext |
| `POST` | `/chacha20/steps` | Visualisasi state matrix (20 ronde + ARX tracking) |
| `POST` | `/chacha20/encrypt-file` | Enkripsi file binary |
| `POST` | `/chacha20/decrypt-file` | Dekripsi file binary |
| `POST` | `/caesar/encrypt` | Enkripsi substitusi Caesar |
| `POST` | `/caesar/decrypt` | Dekripsi substitusi Caesar |
| `POST` | `/caesar/brute-force`| Menguji ke-26 kemungkinan shift |

## 📂 Struktur Proyek

```
kripto-simulator/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── SimulatorController.php  # Menangani load halaman SPA utama
│   │   │   ├── ChaCha20Controller.php   # Gateway API ChaCha20
│   │   │   └── CaesarController.php     # Gateway API Caesar
│   │   └── Requests/
│   │       └── ... (Form Validation)
│   ├── Services/
│   │   ├── ChaCha20Service.php          # HTTP client ke microservice Python
│   │   └── CaesarService.php
│
├── chacha20-api/                        # Python microservice (Core Crypto)
│   ├── chacha20.py                      # Implementasi murni ChaCha20
│   ├── caesar.py                        # Implementasi murni Caesar
│   ├── main.py                          # FastAPI endpoints router
│   └── test_*.py                        # Unit tests
│
├── resources/views/simulator/
│   ├── index.blade.php                  # UI SPA Utama (Pipeline, Visualizer, Terminal)
│   └── learn.blade.php                  # Halaman edukasi (The Story of Kripto)
│
├── routes/web.php                       # Definisi route Laravel
└── README.md                            # File ini
```

## 🔬 Tentang Algoritma

### ChaCha20
ChaCha20 adalah **stream cipher** yang dirancang oleh Daniel J. Bernstein. Sangat cepat di perangkat mobile tanpa perlu akselerasi hardware. Digunakan secara luas di TLS 1.3, WireGuard VPN, dan Google Chrome. Mengandalkan operasi ARX (Addition, Rotation, XOR) pada State Matrix 4x4.

### Caesar Cipher
Salah satu teknik enkripsi tertua yang diketahui, digunakan oleh Julius Caesar untuk mengirim pesan rahasia militer. Merupakan algoritma *substitution cipher* di mana setiap huruf digeser sejumlah posisi tertentu di dalam alfabet. Mudah diretas di era modern menggunakan *Brute Force*.

## 📄 Lisensi

Proyek ini dibuat untuk keperluan akademis mata kuliah Kriptografi.
