# 🔐 Kripto Simulator

> Simulator edukasional untuk algoritma kriptografi, dibangun sebagai proyek mata kuliah Kriptografi.

Aplikasi web yang memungkinkan pengguna mengenkripsi dan mendekripsi pesan menggunakan berbagai algoritma kriptografi, dilengkapi visualisasi step-by-step proses internal algoritma untuk keperluan pembelajaran.

## ✨ Fitur

- **ChaCha20 Stream Cipher** — Implementasi murni Python sesuai [RFC 8439](https://datatracker.ietf.org/doc/html/rfc8439), tanpa library kriptografi eksternal.
- **Caesar Cipher** — Algoritma substitusi klasik *(coming soon — tim 2)*.
- **Visualisasi Edukatif Interaktif (Floating Pop-up)** — Lihat modifikasi state matrix 4x4 secara interaktif dengan penjelasan step-by-step per ronde mengenai operasi ARX (Addition, Rotation, XOR).
- **Desain UI Premium (Netflix Glassmorphism)** — Antarmuka pengguna sinematik 3-kolom bergaya *pipeline* (Input ➜ Proses ➜ Output) yang modern, estetik, dan *user-friendly*.
- **Key & Nonce Generator** — Generate key 256-bit dan nonce 96-bit secara acak menggunakan CSPRNG.
- **Enkripsi & Dekripsi** — Form interaktif untuk encrypt/decrypt pesan secara real-time.

## 🏗️ Arsitektur

```
┌──────────────────────┐
│   Browser (Frontend)  │
│   Alpine.js + CSS     │
└──────────┬───────────┘
           │ HTTP :8000
┌──────────▼───────────┐
│   Laravel 12 (PHP)    │
│   Validation + Routing│
│   API Gateway         │
└──────────┬───────────┘
           │ HTTP :8001 (internal)
┌──────────▼───────────┐
│   Python FastAPI      │
│   ChaCha20 Engine     │
│   Pure Implementation │
└──────────────────────┘
```

| Layer | Teknologi | Fungsi |
|-------|-----------|--------|
| Frontend | Alpine.js, Vanilla CSS | UI/UX sinematik (Glassmorphism), layout pipeline 3-kolom, visualisasi matrix edukatif (modal interaktif) |
| API Gateway | Laravel 12, PHP 8.3 | Validasi input, routing, error handling |
| Crypto Engine | Python 3.11, FastAPI | Eksekusi algoritma ChaCha20 (pure, no external crypto libs) |

## 📋 Prerequisites

- **PHP** ≥ 8.3
- **Composer** ≥ 2.x
- **Python** ≥ 3.11
- **Node.js** ≥ 18 *(opsional, hanya jika menggunakan Vite)*

## 🚀 Cara Menjalankan (Lokal, Tanpa Docker)

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

### 2. Konfigurasi `.env` untuk Lokal

Pastikan setting berikut di file `.env`:

```env
DB_CONNECTION=sqlite

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

CHACHA20_SERVICE_URL=http://127.0.0.1:8001
CHACHA20_SERVICE_TIMEOUT=30
```

Buat file SQLite:

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

### 4. Jalankan (2 Terminal)

```bash
# Terminal 1 — Python ChaCha20 Engine
cd chacha20-api
python -m uvicorn main:app --host 127.0.0.1 --port 8001

# Terminal 2 — Laravel
cd kripto-simulator
php artisan serve --port=8000
```

### 5. Buka Browser

```
http://127.0.0.1:8000
```

## 🐳 Cara Menjalankan (Docker)

```bash
# Build & jalankan semua service
docker compose up --build -d

# Buka browser → http://localhost
```

Docker Compose menjalankan 4 service: Nginx, Laravel (PHP-FPM), Python FastAPI, dan MySQL.

## 📡 API Endpoints

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| `GET` | `/chacha20/keygen` | Generate key 256-bit + nonce 96-bit |
| `POST` | `/chacha20/encrypt` | Enkripsi plaintext → ciphertext |
| `POST` | `/chacha20/decrypt` | Dekripsi ciphertext → plaintext |
| `POST` | `/chacha20/steps` | Visualisasi state matrix (20 ronde) |

Dokumentasi lengkap request/response ada di [`FRONTEND_HANDOFF.md`](./FRONTEND_HANDOFF.md).

### Contoh: Encrypt

```bash
curl -X POST http://127.0.0.1:8000/chacha20/encrypt \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"plaintext": "Hello, Kriptografi!"}'
```

```json
{
  "ciphertext_hex": "a1b2c3d4e5f6...",
  "key_hex": "000102030405...1f",
  "nonce_hex": "000000090000004a00000000",
  "plaintext_length": 19,
  "ciphertext_length": 19
}
```

## 🧪 Testing

### Python — ChaCha20 Algorithm

```bash
cd chacha20-api

# Set encoding untuk Windows
set PYTHONIOENCODING=utf-8

# Jalankan test suite (RFC 8439 test vectors)
python test_chacha20.py

# Jalankan API integration test (pastikan uvicorn sudah jalan)
python test_api.py
```

Test meliputi:
- ✅ Quarter round — RFC 8439 §2.1.1
- ✅ Block function — RFC 8439 §2.3.2
- ✅ Encrypt/decrypt roundtrip
- ✅ Multi-block (>64 bytes)
- ✅ Key uniqueness
- ✅ Step logger (102 log entries)
- ✅ State matrix layout

## 📂 Struktur Proyek

```
kripto-simulator/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── ChaCha20Controller.php   # Request handling
│   │   └── Requests/
│   │       ├── ChaCha20EncryptRequest.php
│   │       ├── ChaCha20DecryptRequest.php
│   │       └── ChaCha20StepsRequest.php
│   ├── Services/
│   │   └── ChaCha20Service.php          # HTTP client ke Python
│   └── Exceptions/
│       └── ChaCha20Exception.php        # Custom exception
│
├── chacha20-api/                        # Python microservice
│   ├── chacha20.py                      # Implementasi algoritma (329 lines)
│   ├── main.py                          # FastAPI endpoints
│   ├── test_chacha20.py                 # Unit tests (RFC test vectors)
│   ├── test_api.py                      # API integration tests
│   └── requirements.txt
│
├── resources/views/chacha20/
│   └── index.blade.php                  # UI simulator
│
├── routes/web.php                       # Laravel routes
├── config/services.php                  # Konfigurasi microservice URL
├── docker-compose.yml                   # Docker orchestration
├── docker/                              # Dockerfile + Nginx config
│
├── FRONTEND_HANDOFF.md                  # Dokumentasi API untuk tim frontend
└── README.md                            # File ini
```

## 🔬 Tentang ChaCha20

ChaCha20 adalah **stream cipher** yang dirancang oleh Daniel J. Bernstein. Digunakan secara luas di TLS 1.3, WireGuard VPN, dan Google Chrome.

### Karakteristik Utama

| Properti | Nilai |
|----------|-------|
| Tipe | Stream cipher (ARX — Addition, Rotation, XOR) |
| Ukuran key | 256 bit (32 bytes) |
| Ukuran nonce | 96 bit (12 bytes) |
| Block counter | 32 bit |
| Block size | 512 bit (64 bytes) |
| Jumlah ronde | 20 (10 column + 10 diagonal) |
| Spesifikasi | [RFC 8439](https://datatracker.ietf.org/doc/html/rfc8439) |

### State Matrix

```
┌────────────┬────────────┬────────────┬────────────┐
│ "expa"     │ "nd 3"     │ "2-by"     │ "te k"     │  ← Konstanta
├────────────┼────────────┼────────────┼────────────┤
│  Key[0]    │  Key[1]    │  Key[2]    │  Key[3]    │  ← 256-bit Key
├────────────┼────────────┼────────────┼────────────┤
│  Key[4]    │  Key[5]    │  Key[6]    │  Key[7]    │  ← (lanjutan)
├────────────┼────────────┼────────────┼────────────┤
│  Counter   │  Nonce[0]  │  Nonce[1]  │  Nonce[2]  │  ← Counter + Nonce
└────────────┴────────────┴────────────┴────────────┘
```

## 👥 Tim Proyek

| Tim | Scope | Algoritma |
|-----|-------|-----------|
| Backend 1 | Implementasi engine + API gateway | ChaCha20 (RFC 8439) |
| Backend 2 | Implementasi engine | Caesar Cipher |
| Frontend | UI/UX simulator | — |

## 📄 Lisensi

Proyek ini dibuat untuk keperluan akademis mata kuliah Kriptografi.
