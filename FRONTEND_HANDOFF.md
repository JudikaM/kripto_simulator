# Frontend Handoff — ChaCha20 Simulator

> **Dokumen ini untuk tim frontend.**
> Berisi semua informasi yang diperlukan untuk membangun UI yang berkomunikasi dengan backend ChaCha20 Simulator.

---

## 🏗️ Arsitektur Sistem

```
┌──────────────────┐     HTTP JSON      ┌──────────────────┐     HTTP JSON     ┌──────────────────┐
│                  │ ──────────────────> │                  │ ────────────────> │                  │
│    FRONTEND      │                    │  LARAVEL (PHP)   │                   │  PYTHON FastAPI  │
│    (Tim Kalian)  │ <────────────────  │  Port 8000       │ <──────────────── │  Port 8001       │
│                  │     JSON Response  │  Validasi + Proxy│    JSON Response  │  ChaCha20 Engine │
└──────────────────┘                    └──────────────────┘                   └──────────────────┘

Frontend HANYA berkomunikasi dengan Laravel (port 8000).
JANGAN call Python langsung. Laravel yang handle semua validasi + forwarding.
```

### Dua Opsi Integrasi Frontend

| Opsi | Cara Kerja | Cocok Untuk |
|------|-----------|-------------|
| **A. Blade + Alpine.js** (saat ini) | Frontend di-render oleh Laravel sebagai file `.blade.php`. Sudah ada contohnya di `resources/views/chacha20/index.blade.php` | Satu repo, simple, cepat |
| **B. Frontend Terpisah (SPA)** | Frontend pakai React/Vue/Svelte/dll, jalan di port sendiri, panggil Laravel API via `fetch()` | Dua repo, lebih fleksibel |

> ⚠️ **Jika pakai Opsi B (SPA terpisah)**, backend perlu sedikit penyesuaian — lihat bagian [Integrasi SPA Terpisah](#-opsi-b-integrasi-spa-terpisah) di bawah.

---

## 📡 API Endpoints

**Base URL:** `http://127.0.0.1:8000`

Semua endpoint mengembalikan **JSON**. Semua POST request harus mengirim **JSON body**.

---

### 1. `GET /chacha20/keygen` — Generate Key & Nonce

Menghasilkan key 256-bit dan nonce 96-bit secara acak.

**Request:**
```
GET /chacha20/keygen
Accept: application/json
```

**Response (200):**
```json
{
  "key_hex": "a1b2c3d4e5f6...64 karakter hex...",
  "key_base64": "base64string==",
  "key_length_bits": 256,
  "nonce_hex": "f1e2d3c4b5a6...24 karakter hex...",
  "nonce_base64": "base64string==",
  "nonce_length_bits": 96
}
```

**Contoh JavaScript:**
```javascript
const res = await fetch('/chacha20/keygen');
const data = await res.json();
console.log(data.key_hex);   // "a1b2c3..." (64 chars)
console.log(data.nonce_hex); // "f1e2d3..." (24 chars)
```

---

### 2. `POST /chacha20/encrypt` — Enkripsi Plaintext

**Request:**
```
POST /chacha20/encrypt
Content-Type: application/json
Accept: application/json
X-CSRF-TOKEN: <csrf_token>   ← WAJIB jika pakai Blade (Opsi A)
```

**Body:**
```json
{
  "plaintext": "Hello, dunia!",
  "key": null,
  "nonce": null,
  "counter": 1,
  "show_rounds": false
}
```

| Field | Tipe | Wajib | Aturan |
|-------|------|-------|--------|
| `plaintext` | string | ✅ Ya | Min 1 karakter, max 10.000 karakter |
| `key` | string \| null | Opsional | Tepat **64 karakter hex** (256-bit). Jika `null` atau kosong → auto-generate |
| `nonce` | string \| null | Opsional | Tepat **24 karakter hex** (96-bit). Jika `null` atau kosong → auto-generate |
| `counter` | integer | Opsional | Default: `1`. Range: 0 – 4.294.967.295 |
| `show_rounds` | boolean | Opsional | Default: `false`. Jika `true` → response menyertakan `round_logs` (besar! ±102 entries) |

**Response (200):**
```json
{
  "ciphertext_hex": "a1b2c3d4...",
  "ciphertext_base64": "obLD1A==",
  "key_hex": "64 karakter hex (key yang dipakai)",
  "nonce_hex": "24 karakter hex (nonce yang dipakai)",
  "counter": 1,
  "plaintext_length": 13,
  "ciphertext_length": 13,
  "round_logs": null
}
```

> 💡 **Penting:** Simpan `key_hex` dan `nonce_hex` dari response — diperlukan untuk dekripsi nanti!

---

### 3. `POST /chacha20/decrypt` — Dekripsi Ciphertext

**Request:**
```
POST /chacha20/decrypt
Content-Type: application/json
Accept: application/json
X-CSRF-TOKEN: <csrf_token>
```

**Body:**
```json
{
  "ciphertext_hex": "a1b2c3d4...",
  "key": "64 karakter hex (WAJIB, harus sama saat enkripsi)",
  "nonce": "24 karakter hex (WAJIB, harus sama saat enkripsi)",
  "counter": 1,
  "show_rounds": false
}
```

| Field | Tipe | Wajib | Aturan |
|-------|------|-------|--------|
| `ciphertext_hex` | string | ✅ Ya | String hexadecimal valid, min 2 karakter |
| `key` | string | ✅ Ya | Tepat **64 karakter hex** |
| `nonce` | string | ✅ Ya | Tepat **24 karakter hex** |
| `counter` | integer | Opsional | Harus sama dengan saat enkripsi |
| `show_rounds` | boolean | Opsional | Default: `false` |

**Response (200):**
```json
{
  "plaintext": "Hello, dunia!",
  "plaintext_hex": "48656c6c6f2c2064756e696121",
  "key_hex": "...",
  "nonce_hex": "...",
  "round_logs": null
}
```

---

### 4. `POST /chacha20/steps` — Visualisasi State Matrix (20 Ronde)

Endpoint khusus untuk **fitur visualisasi edukasi**. Selalu mengembalikan round logs lengkap.

**Request:**
```
POST /chacha20/steps
Content-Type: application/json
Accept: application/json
X-CSRF-TOKEN: <csrf_token>
```

**Body:**
```json
{
  "plaintext": "Test",
  "key": null,
  "nonce": null,
  "counter": 1
}
```

**Response (200):**
```json
{
  "ciphertext_hex": "...",
  "key_hex": "...",
  "nonce_hex": "...",
  "counter": 1,
  "total_steps": 102,

  "initial_state": {
    "round": 0,
    "description": "Initial state matrix (before any rounds)",
    "state_matrix": [
      ["0x61707865", "0x3320646e", "0x79622d32", "0x6b206574"],
      ["0x03020100", "0x07060504", "0x0b0a0908", "0x0f0e0d0c"],
      ["0x13121110", "0x17161514", "0x1b1a1918", "0x1f1e1d1c"],
      ["0x00000001", "0x09000000", "0x4a000000", "0x00000000"]
    ],
    "state_words": ["0x61707865", "0x3320646e", "...16 words total"]
  },

  "final_state": {
    "round": "final",
    "description": "Final state (working + initial)",
    "state_matrix": [["..."],["..."],["..."],["..."]],
    "state_words": ["..."]
  },

  "round_summaries": [
    {
      "round": 1,
      "type": "column",
      "description": "Round 1 complete — Column rounds",
      "state_matrix": [["..."],["..."],["..."],["..."]],
      "state_words": ["..."],
      "quarter_rounds": ["QR(0, 4, 8, 12)", "QR(1, 5, 9, 13)", "QR(2, 6, 10, 14)", "QR(3, 7, 11, 15)"]
    },
    {
      "round": 2,
      "type": "diagonal",
      "description": "Round 2 complete — Diagonal rounds",
      "...": "..."
    }
  ],

  "quarter_round_details": [
    {
      "round": 1,
      "type": "quarter_round_detail",
      "description": "After QR(0, 4, 8, 12)",
      "indices": {"a": 0, "b": 4, "c": 8, "d": 12},
      "affected_words": {
        "state[0]": "0x...",
        "state[4]": "0x...",
        "state[8]": "0x...",
        "state[12]": "0x..."
      },
      "state_matrix": [["..."],["..."],["..."],["..."]]
    }
  ],

  "summary": {
    "total_rounds": 20,
    "column_rounds": 10,
    "diagonal_rounds": 10,
    "quarter_rounds_total": 80,
    "log_entries_total": 102
  }
}
```

### Cara Menggunakan Data Visualisasi

```
State Matrix adalah grid 4×4 dari 32-bit words:

┌────────────┬────────────┬────────────┬────────────┐
│  word[0]   │  word[1]   │  word[2]   │  word[3]   │  ← Constants
├────────────┼────────────┼────────────┼────────────┤
│  word[4]   │  word[5]   │  word[6]   │  word[7]   │  ← Key (part 1)
├────────────┼────────────┼────────────┼────────────┤
│  word[8]   │  word[9]   │  word[10]  │  word[11]  │  ← Key (part 2)
├────────────┼────────────┼────────────┼────────────┤
│  word[12]  │  word[13]  │  word[14]  │  word[15]  │  ← Counter + Nonce
└────────────┴────────────┴────────────┴────────────┘

Navigasi ronde:
  - initial_state         → Tampilkan sebagai state awal
  - round_summaries[0-19] → 20 ronde (ganjil = column, genap = diagonal)
  - final_state           → State akhir setelah 20 ronde + penambahan initial

Highlight perubahan:
  Bandingkan state_words ronde N dengan ronde N-1.
  Words yang berubah → highlight merah/kuning di UI.
```

---

## ⚠️ Penanganan Error

### Validation Error (422)
Terjadi saat input tidak valid (key bukan hex, plaintext kosong, dll).

```json
{
  "message": "The key field format is invalid.",
  "errors": {
    "key": ["Key harus tepat 64 karakter hexadecimal (256-bit)."],
    "plaintext": ["Plaintext tidak boleh kosong."]
  }
}
```

**Cara handle di JavaScript:**
```javascript
const res = await fetch('/chacha20/encrypt', { method: 'POST', ... });
const data = await res.json();

if (!res.ok) {
  if (data.errors) {
    // Validation error — tampilkan per field
    Object.entries(data.errors).forEach(([field, messages]) => {
      console.error(`${field}: ${messages.join(', ')}`);
    });
  } else {
    // General error
    console.error(data.message || 'Terjadi error');
  }
}
```

### Service Error (503)
Terjadi saat Python microservice tidak berjalan.

```json
{
  "error": true,
  "message": "Tidak bisa terhubung ke ChaCha20 microservice. Pastikan service Python sedang berjalan.",
  "api_error": null
}
```

### Crypto Error (400)
Terjadi saat Python menolak input (misal hex yang corrupt).

```json
{
  "error": true,
  "message": "Invalid hex string for 'key': contains non-hex characters",
  "api_error": { "detail": "..." }
}
```

---

## 🔧 Opsi A: Integrasi via Blade (Satu Repo)

Jika kalian ingin bekerja **dalam repo Laravel yang sama**:

### Struktur File
```
resources/views/chacha20/
  └── index.blade.php    ← File UI kalian di sini
```

### CSRF Token
Laravel melindungi semua POST request dengan CSRF token. Di Blade, taruh ini di `<head>`:

```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

Lalu saat fetch:
```javascript
const csrfToken = document.querySelector('meta[name=csrf-token]').content;

const res = await fetch('/chacha20/encrypt', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-CSRF-TOKEN': csrfToken,    // ← WAJIB untuk POST
  },
  body: JSON.stringify({ plaintext: 'Hello!' }),
});
```

### Cara Menjalankan di Lokal
```bash
# Terminal 1 — Backend Python (ChaCha20 engine)
cd chacha20-api
python -m uvicorn main:app --host 127.0.0.1 --port 8001

# Terminal 2 — Laravel
cd kripto-simulator
php artisan serve --port=8000

# Buka browser → http://127.0.0.1:8000
```

### Teknologi yang Bebas Dipakai
- Library JS apapun: **Alpine.js, React, Vue, Svelte, vanilla JS** — semua bisa
- CSS framework apapun: **Tailwind, Bootstrap, Vanilla CSS** — bebas
- Animasi: **GSAP, Framer Motion, CSS animations** — bebas
- Yang penting: panggil endpoint via `fetch()` atau `axios`

---

## 🌐 Opsi B: Integrasi SPA Terpisah

Jika kalian menggunakan **framework terpisah** (React/Vue/Next.js) yang jalan di port sendiri (misal :3000):

### Perubahan yang Diperlukan di Backend

Tim backend (saya) perlu melakukan 2 hal:

#### 1. Tambah CORS agar Laravel menerima request dari port frontend
Beritahu saya port berapa frontend kalian jalan, lalu saya konfigurasi.

#### 2. Ubah endpoint jadi API route (tanpa CSRF)
Saya akan pindahkan route dari `routes/web.php` ke `routes/api.php` agar tidak butuh CSRF token.

Endpoint berubah menjadi:
```
GET  /api/chacha20/keygen
POST /api/chacha20/encrypt
POST /api/chacha20/decrypt
POST /api/chacha20/steps
```

### Contoh Fetch dari SPA Terpisah
```javascript
const API_BASE = 'http://127.0.0.1:8000/api';

// Keygen
const keys = await fetch(`${API_BASE}/chacha20/keygen`).then(r => r.json());

// Encrypt
const encrypted = await fetch(`${API_BASE}/chacha20/encrypt`, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    // Tidak perlu CSRF token di API route
  },
  body: JSON.stringify({
    plaintext: 'Hello!',
    key: keys.key_hex,
    nonce: keys.nonce_hex,
  }),
}).then(r => r.json());
```

---

## 🧪 Cara Test API Tanpa Frontend

Tim frontend bisa langsung test API pakai:

### Browser — Swagger UI
Buka `http://127.0.0.1:8001/docs` untuk interactive API docs (Python direct).

### cURL
```bash
# Keygen
curl http://127.0.0.1:8000/chacha20/keygen

# Encrypt (ganti CSRF_TOKEN jika pakai web route)
curl -X POST http://127.0.0.1:8000/chacha20/encrypt \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"plaintext": "Hello!"}'
```

### Postman / Insomnia
Import endpoint di atas. Untuk web route, tambahkan header `X-CSRF-TOKEN`.

---

## 📖 Referensi Cepat

### Validasi Input

| Field | Format | Contoh Valid |
|-------|--------|-------------|
| `key` | 64 karakter hex (0-9, a-f) | `000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f` |
| `nonce` | 24 karakter hex (0-9, a-f) | `000000000000004a00000000` |
| `ciphertext_hex` | String hex genap | `a1b2c3d4e5f6` |
| `counter` | Integer 0 – 4.294.967.295 | `1` |
| `plaintext` | String UTF-8, 1-10.000 chars | `Hello, dunia! 🔐` |

### Flow Lengkap Encrypt → Decrypt

```
1. [Optional] GET /chacha20/keygen → dapat key_hex + nonce_hex
2. POST /chacha20/encrypt
     Body: { plaintext, key?, nonce? }
     Response: { ciphertext_hex, key_hex, nonce_hex }
           ↓
     Simpan key_hex + nonce_hex + ciphertext_hex
           ↓
3. POST /chacha20/decrypt
     Body: { ciphertext_hex, key, nonce }
     Response: { plaintext }
```

### Flow Visualisasi State Matrix

```
1. [Optional] GET /chacha20/keygen → dapat key_hex + nonce_hex
2. POST /chacha20/steps
     Body: { plaintext, key?, nonce? }
     Response: { initial_state, round_summaries[20], final_state, quarter_round_details[80] }
           ↓
     Tampilkan grid 4×4 dengan navigasi antar ronde
     Highlight words yang berubah (bandingkan state ronde N vs N-1)
```

---

## ❓ Kontak & Koordinasi

Jika ada pertanyaan tentang API atau menemukan bug di backend, hubungi tim backend.
Hal yang perlu dikomunikasikan:

- [ ] **Opsi mana yang dipilih** — Blade (Opsi A) atau SPA terpisah (Opsi B)?
- [ ] **Jika Opsi B** — Framework apa dan port berapa?
- [ ] **Fitur visualisasi** — Apakah akan pakai data `quarter_round_details` (80 entries, sangat detail) atau cukup `round_summaries` (20 entries)?
