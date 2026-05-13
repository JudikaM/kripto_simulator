# Teori Lengkap Algoritma Kriptografi ChaCha20

Dokumen ini disusun untuk menjelaskan secara komprehensif bagaimana proses enkripsi dan dekripsi pada algoritma ChaCha20 bekerja, khususnya untuk keperluan penjelasan di proyek Kripto Simulator.

## 1. Pendahuluan

**ChaCha20** adalah algoritma *stream cipher* yang dirancang oleh Daniel J. Bernstein pada tahun 2008. Algoritma ini merupakan modifikasi dan perbaikan dari algoritma pendahulunya, yaitu Salsa20. ChaCha20 dirancang untuk memberikan keamanan yang tinggi dengan kinerja yang sangat cepat pada perangkat lunak, terutama pada arsitektur prosesor yang tidak memiliki instruksi perangkat keras khusus untuk kriptografi (seperti AES-NI).

Berbeda dengan *block cipher* (seperti AES) yang mengenkripsi data secara langsung pada blok tertentu, *stream cipher* seperti ChaCha20 bekerja dengan cara menghasilkan aliran bit acak semu (pseudo-random) yang disebut **Keystream**. Keystream ini kemudian dioperasikan dengan operasi logika **XOR** ($\oplus$) terhadap data asli (Plaintext) untuk menghasilkan Ciphertext.

**Karakteristik Utama ChaCha20:**
*   **Keystream Block Size**: 512 bit (64 byte) per blok.
*   **Key Size**: 256 bit (32 byte).
*   **Nonce Size**: 96 bit (12 byte) (Berdasarkan standar IETF RFC 8439).
*   **Counter Size**: 32 bit (4 byte).

---

## 2. Struktur Dasar: State Matrix

Jantung dari algoritma ChaCha20 adalah sebuah kondisi yang disebut **State**. State direpresentasikan sebagai matriks $4 \times 4$ yang berisi angka bulat (integer) 32-bit tak bertanda (*unsigned 32-bit integer*).
Karena matriks ini berukuran $4 \times 4$, maka total terdapat 16 elemen (disebut *word*).
$16 \text{ word} \times 32 \text{ bit} = 512 \text{ bit} = 64 \text{ byte}$.

### Inisialisasi State

Sebelum menghasilkan keystream, state matrix diinisialisasi dengan struktur berikut:

| Konstanta | Konstanta | Konstanta | Konstanta |
| :---: | :---: | :---: | :---: |
| **Kunci (Key)** | **Kunci (Key)** | **Kunci (Key)** | **Kunci (Key)** |
| **Kunci (Key)** | **Kunci (Key)** | **Kunci (Key)** | **Kunci (Key)** |
| **Counter** | **Nonce** | **Nonce** | **Nonce** |

Jika dituliskan dalam indeks array `State[0...15]`:

*   **`State[0] - State[3]` (Konstanta):** Ini adalah *magic words* bernilai tetap. Biasanya adalah representasi ASCII dari string `"expand 32-byte k"`.
    *   *Catatan: Pembacaan byte ke dalam word 32-bit pada algoritma ini (baik untuk Kunci, Nonce, maupun saat menghasilkan Keystream akhir) selalu menggunakan urutan **Little-Endian** (byte paling tidak signifikan diletakkan di alamat paling awal), sesuai dengan standar RFC 8439 dan implementasi Python pada proyek ini.*
    *   `State[0]` = `0x61707865` ("expa")
    *   `State[1]` = `0x3320646e` ("nd 3")
    *   `State[2]` = `0x79622d32` ("2-by")
    *   `State[3]` = `0x6b206574` ("te k")
*   **`State[4] - State[11]` (Kunci / Key):** Berisi 8 word dari kunci rahasia 256-bit.
*   **`State[12]` (Block Counter):** Counter 32-bit. Mulai dari 0 atau 1, dan akan bertambah 1 (`++`) untuk setiap blok data 64 byte selanjutnya. Ini memastikan bahwa blok teks yang sama jika dienkripsi dua kali dalam urutan yang berbeda akan menghasilkan keystream yang berbeda pula.
*   **`State[13] - State[15]` (Nonce):** Initialization Vector / Nonce 96-bit. Nonce **TIDAK BOLEH** digunakan ulang untuk kunci yang sama. Jika nonce yang sama digunakan dua kali, keamanan sistem akan runtuh secara fatal (*two-time pad attack*).

---

## 3. Fungsi Inti: The Quarter Round (ARX)

Pengacakan data di ChaCha20 sangat bergantung pada operasi **ARX**: **A**ddition (Penjumlahan modulo $2^{32}$), **R**otation (Pergeseran bit ke kiri / *Left Bitwise Rotation*), dan **X**OR. Operasi ini sangat efisien dan tahan terhadap *timing attack* (serangan analisis waktu) karena instruksi CPU-nya konstan.

Fungsi **Quarter Round** atau `QR(a, b, c, d)` mengubah 4 state word secara bersamaan dengan urutan operasi sebagai berikut:

1.  `a = (a + b) mod 2^32`;  `d = d XOR a`;  `d = d <<< 16`;
2.  `c = (c + d) mod 2^32`;  `b = b XOR c`;  `b = b <<< 12`;
3.  `a = (a + b) mod 2^32`;  `d = d XOR a`;  `d = d <<< 8`;
4.  `c = (c + d) mod 2^32`;  `b = b XOR c`;  `b = b <<< 7`;

*(Keterangan: `<<<` menandakan rotasi bit ke kiri, bukan sekadar shift biasa. Bit yang keluar dari kiri akan masuk kembali dari kanan).*

---

## 4. Fungsi Blok ChaCha20 (20 Rounds)

Untuk menghasilkan 1 blok keystream (64 byte), algoritma menjalankan fungsi block pada State Matrix awal. "ChaCha20" berarti algoritma melakukan **20 putaran (rounds)**.

Satu "Double Round" (2 round) terdiri dari:
1.  **Column Round** (Putaran Kolom): Menerapkan fungsi Quarter Round secara vertikal ke masing-masing dari 4 kolom pada state matrix.
    *   `QR(0, 4, 8, 12)`
    *   `QR(1, 5, 9, 13)`
    *   `QR(2, 6, 10, 14)`
    *   `QR(3, 7, 11, 15)`
2.  **Diagonal Round** (Putaran Diagonal): Menerapkan fungsi Quarter Round secara menyilang/diagonal. Ini yang disebut "difusi" antar kolom.
    *   `QR(0, 5, 10, 15)`
    *   `QR(1, 6, 11, 12)`
    *   `QR(2, 7, 8, 13)`
    *   `QR(3, 4, 9, 14)`

Operasi ganda (Column + Diagonal) ini diulang sebanyak 10 kali, sehingga total menghasilkan **20 putaran**.

**Penjumlahan Akhir (Sangat Penting):**
Setelah 20 putaran selesai, matriks hasil yang telah sangat teracak **TIDAK** langsung menjadi keystream. Terlebih dahulu, matriks hasil pengacakan tersebut dijumlahkan kembali (modulo $2^{32}$) secara per-elemen (indeks ke indeks) dengan **State Matrix Inisialisasi awal**.
Tahapan ini krusial agar algoritma bersifat non-reversible (tidak bisa di-reverse engineering jika penyerang mengetahui output keystream).

Terakhir, matriks state yang merupakan kumpulan 16 integer 32-bit di-serialize (diubah urutannya dari little-endian jika perlu) menjadi array sepanjang 64 byte berurutan. Ini adalah **Keystream**.

---

## 5. Proses Enkripsi

Setelah kita bisa men-generate blok Keystream 64-byte, proses enkripsi menjadi sangat mudah:

1.  Persiapkan data **Plaintext**, **Key**, dan **Nonce**.
2.  Bagi Plaintext ke dalam blok-blok sebesar 64 Byte (blok terakhir boleh kurang dari 64 byte).
3.  Set nilai **Counter** ke 1 (atau 0 tergantung spesifikasi awal).
4.  Untuk setiap blok Plaintext ke-$i$:
    *   Buat Inisialisasi State Matrix dengan Key, Nonce, dan Counter = $i$.
    *   Jalankan fungsi Blok ChaCha20 (20 rounds + Penjumlahan awal).
    *   Hasilkan 64 byte **Keystream**.
    *   Lakukan operasi logika **XOR** secara per-byte antara blok Plaintext dengan Keystream: `Ciphertext_i = Plaintext_i XOR Keystream`.
    *   Naikkan nilai Counter (`Counter = Counter + 1`).
5.  Gabungkan semua `Ciphertext_i` untuk mendapatkan **Ciphertext** utuh.

---

## 6. Proses Dekripsi

Sifat inheren dari operasi logika bitwise **XOR** adalah kebalikannya adalah dirinya sendiri.
`(A XOR B) XOR B = A`.

Oleh karena itu, pada algoritma *stream cipher*, **fungsi Dekripsi sepenuhnya SAMA PERSIS dengan fungsi Enkripsi**. Tidak ada algoritma dekripsi khusus di ChaCha20.

Langkah Dekripsi:
1.  Penerima membutuhkan **Ciphertext**, **Key** yang sama, dan **Nonce** yang sama.
2.  Penerima mengulangi langkah-langkah inisialisasi State (dengan Key dan Nonce yang sama) dan membagi iterasi block dengan Counter yang berjalan naik.
3.  Proses ini akan **merekonstruksi ulang Keystream yang sama persis** dengan yang dihasilkan saat mengenkripsi.
4.  Operasi **XOR** dilakukan antara Ciphertext dan Keystream: `Plaintext_i = Ciphertext_i XOR Keystream`.
5.  Hasil gabungan `Plaintext_i` akan mengembalikan pesan asli utuh.

---

## 7. Kesimpulan untuk Dosen

Jika Dosen bertanya "Bagaimana konsep utama ChaCha20?", poin penting yang harus dijawab adalah:
1.  **Ini adalah Stream Cipher**: Tidak memecah teks dan mengenkripsi teks langsung, melainkan mengenkripsi sebuah matriks internal (*State*) menjadi *Keystream* acak semu, lalu men-XOR keystream tersebut dengan teks.
2.  **Operasi ARX**: Operasi intinya murni hanya menggunakan Penjumlahan (+), Rotasi (<<<), dan XOR ($\oplus$). Tidak ada operasi matematika berat (perkalian/pembagian/tabel s-box) sehingga sangat cepat.
3.  **Matriks State 4x4**: State sebesar 512 bit, diatur dalam 16 variabel 32-bit yang memuat Konstanta, Kunci 256-bit, Nonce 96-bit, dan Counter Blok 32-bit.
4.  **Dekripsi == Enkripsi**: Karena memakai XOR, alur kode program yang digunakan untuk mendekripsi persis sama dengan alur kode untuk mengenkripsi. Cukup panggil fungsi enkripsi dengan memberikan ciphertext-nya.
