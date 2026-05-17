# UI/UX Design Blueprint: Kripto Simulator (NakaMotoX)

Dokumen ini adalah panduan spesifikasi antarmuka (UI) dan pengalaman pengguna (UX) untuk proyek **Kripto Simulator**. Dokumen ini dapat digunakan oleh tim UI/UX sebagai acuan untuk membuat ulang, melakukan iterasi, atau membuat *mockup* resolusi tinggi di **Figma**, **Penpot**, atau sebagai *prompt* untuk AI Design Generators (seperti v0.dev, Galileo AI, dll).

---

## 1. Konsep Utama & Nuansa (Vibe)
*   **Tema:** *Cybersecurity, Modern Crypto, Glassmorphism, Terminal Hacker.*
*   **Estetika:** Menggunakan efek kaca transparan (*frosted glass / glassmorphism*) dengan elemen cahaya neon yang kontras terhadap latar belakang gelap (atau elemen minimalis bersih pada mode terang).
*   **Latar Belakang:** Bukan warna solid, melainkan memiliki "Ambient Orbs" (lingkaran gradasi besar yang diblur sangat kuat dan melayang lambat di *background*).

---

## 2. Design Tokens (Variabel Sistem Desain)

### A. Typography (Tipografi)
*   **Font Utama (UI & Paragraf):** `Inter` (Google Fonts). Sangat bersih, modern, dan sangat terbaca.
    *   *Weights:* 300 (Light), 400 (Regular), 500 (Medium), 600 (Semi-Bold), 700 (Bold), 800 (Extra Bold).
*   **Font Kriptografi (Hex, Biner, Terminal):** `Courier New` atau font *monospace* modern lainnya (seperti `Fira Code`, `JetBrains Mono`).

### B. Color Palette (Dark Mode - Default)
*   **Background Base:** `#0B0B0C` (Hitam pekat dengan sedikit kebiruan).
*   **Glass Surface:** `rgba(20, 20, 20, 0.45)` dengan *background-blur* tebal (20px).
*   **Text Main:** `#FFFFFF` (Putih).
*   **Text Muted:** `#B3B3B3` (Abu-abu terang).
*   **Borders:** `rgba(255, 255, 255, 0.1)`.

### C. Color Palette (Light Mode - Opsional/Aksesibilitas)
*   **Background Base:** `#F3F4F6` (Abu-abu sangat terang).
*   **Glass Surface:** `rgba(255, 255, 255, 0.75)` dengan *background-blur*.
*   **Text Main:** `#111827` (Abu-abu gelap pekat).
*   **Text Muted:** `#4B5563` (Abu-abu medium).
*   **Borders:** `rgba(0, 0, 0, 0.1)`.

### D. Aksen Warna per Algoritma
Setiap algoritma memiliki "Tema Warna" sendiri yang memengaruhi tombol, efek cahaya, dan ambient orbs.
*   **ChaCha20 (Theme Red):**
    *   Primary/Accent: `#E50914` (Merah Netflix).
    *   Ambient Orbs: Kombinasi Merah, Ungu Tua (`#660099`), dan Biru (`#0044ff`).
*   **Caesar Cipher (Theme Gold):**
    *   Primary/Accent: `#f59e0b` (Emas/Kuning pekat).
    *   Ambient Orbs: Kombinasi Emas, Ungu Terang (`#7c3aed`), dan Cyan (`#06b6d4`).

---

## 3. Struktur Layout (Desktop View)

Antarmuka utama menggunakan konsep **"Pipeline Layout"** (Alur Pipa) 3 kolom yang mengilustrasikan proses input-proses-output:

### Kolom 1: INPUT DATA
*   **Fungsi:** Tempat pengguna memasukkan *Plaintext*, *Ciphertext*, atau *File Upload*.
*   **Visual:** Kartu glassmorphism tinggi. Memiliki "Header Banner" di atasnya bernomor 1.
*   **Komponen:** Textarea yang responsif atau area *Drag & Drop* dengan garis putus-putus.

👉 *Di antara kolom 1 dan 2 terdapat Divider berupa panah Chevron (>) berukuran besar.*

### Kolom 2: KONFIGURASI (Center)
*   **Fungsi:** "Mesin" pemrosesan. Memilih algoritma, mode operasi, dan kunci rahasia.
*   **Visual:** Kartu glassmorphism tinggi dengan "Header Banner" bernomor 2.
*   **Komponen:**
    *   **Dropdown Algoritma** (ChaCha20 / Caesar).
    *   **Mode Switcher Tab:** Segmented control UI (Encrypt | Decrypt | Visualize | File | Brute Force).
    *   **Input Key & Nonce (ChaCha20):** Input *monospace*.
    *   **Shift Slider (Caesar):** Slider visual untuk memilih angka 0-25.
    *   **Tombol Aksi Utama (Execute):** Tombol besar yang mengambil warna Aksen (Merah/Emas) dengan *hover state* efek cahaya (*glow*).

👉 *Di antara kolom 2 dan 3 terdapat Divider panah Chevron (>) berukuran besar.*

### Kolom 3: OUTPUT RESULT
*   **Fungsi:** Menampilkan hasil akhir.
*   **Visual:** Kartu glassmorphism dengan "Header Banner" bernomor 3.
*   **Komponen:**
    *   **Empty State:** Jika belum ada hasil, tampilkan ikon kotak kosong transparan.
    *   **Result Box:** Kotak bergaya terminal dengan font *monospace* untuk teks terenkripsi.
    *   **Terminal Brute Force (Caesar):** Tampilan seperti terminal *hacker* bergulir ke bawah, warna latar `#000` (atau `#f5f5f5` di light mode), teks hijau monospace.

---

## 4. UI Komponen Spesifik (Figma Details)

### A. ChaCha20 State Matrix Visualizer (Pop-up Modal)
Ini adalah fitur edukatif utama (layar penuh/overlay).
*   **Layout Modal:** Kotak besar di tengah layar, latar belakang luar sangat gelap dengan efek blur (`backdrop-filter`).
*   **Matrix Grid:** Susunan kotak 4x4 (Total 16 kotak).
*   **Desain Sel (Cell):** 
    *   Border transparan, teks *monospace* (cth: `0x61707865`).
    *   Setiap sel punya indeks kecil di atasnya `[0]`, `[1]`, dsb.
    *   **Efek Interaktif:** Ketika ronde berubah dan sel dienkripsi, sel tersebut harus memiliki transisi membesar sedikit (`scale: 1.08`), *border* menyala merah terang, dan warna latar menjadi kemerahan.
*   **Legend (Legenda Warna):** Indikator titik warna (Dot) untuk membedakan elemen (Abu-abu = Konstanta, Merah = Kunci, Biru = Counter, Hijau = Nonce).
*   **Panel Narasi (Sebelah Kanan Grid):** Panel yang berisi teks penjelasan ronde dan tombol navigasi Prev/Next Step.

### B. Navbar
*   **Logo:** Teks `NakamotoX`. Tulisan "Naka" menggunakan warna aksen (Merah/Emas), "motoX" menggunakan warna putih/hitam.
*   **Tombol Navigasi:** 
    *   Tombol Toggle Light/Dark Mode (Ikon ☀️/🌙).
    *   Link Buku Edukasi.
    *   Status Badge (Engine: 🟢 Online) dengan latar *glass* berbentuk pil melingkar.

---

## 5. Instruksi Khusus untuk Tim Desain / AI
Jika Anda *mem-paste* dokumen ini ke AI Prompt (seperti Midjourney untuk referensi, atau v0.dev):
> *"Create a UI design for a web-based Cryptography Simulator tool called NakamotoX. The style is modern, glassmorphism, cybersecurity hacker theme. Dark mode. The main layout has a top navbar and three main columns side-by-side: Input, Config, and Output. The accent color is #E50914 (Red). Include a background with large, heavily blurred, floating neon orbs. Use Inter font for UI and Courier New for encrypted hex code displays. In the center column, place a Segmented Control for mode selection (Encrypt/Decrypt/Visualize) and an 'Execute' button that glows."*
