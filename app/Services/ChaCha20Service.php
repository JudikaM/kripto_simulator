<?php

namespace App\Services;

use App\Exceptions\ChaCha20Exception;
use Illuminate\Http\Client\ConnectionException;
// RequestException tidak dipakai — dihapus
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ChaCha20Service
 *
 * Berkomunikasi dengan Python FastAPI microservice via Laravel Http facade.
 * URL dikonfigurasi melalui CHACHA20_SERVICE_URL di .env, mengarah ke
 * nama service Docker (http://python:8001) di production.
 */
class ChaCha20Service
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.chacha20.url', 'http://python:8001'), '/');
        $this->timeout = (int) config('services.chacha20.timeout', 30);
    }

    // ─────────────────────────────────────────────
    //  Public Methods
    // ─────────────────────────────────────────────

    /**
     * Hasilkan key 256-bit dan nonce 96-bit secara acak.
     *
     * @return array{key_hex: string, key_base64: string, nonce_hex: string, nonce_base64: string, ...}
     * @throws ChaCha20Exception
     */
    public function keygen(): array
    {
        $response = $this->get('/keygen');

        return [
            'key_hex'           => $response['key_hex'],
            'key_base64'        => $response['key_base64'],
            'key_length_bits'   => $response['key_length_bits'] ?? 256,
            'nonce_hex'         => $response['nonce_hex'],
            'nonce_base64'      => $response['nonce_base64'],
            'nonce_length_bits' => $response['nonce_length_bits'] ?? 96,
        ];
    }

    /**
     * Enkripsi plaintext menggunakan ChaCha20.
     *
     * @param  string       $plaintext    Teks yang akan dienkripsi (UTF-8)
     * @param  string|null  $key          64 hex chars (256-bit). Auto-generate jika null.
     * @param  string|null  $nonce        24 hex chars (96-bit). Auto-generate jika null.
     * @param  int          $counter      Block counter awal (default 1)
     * @param  bool         $showRounds   Sertakan round_logs atau tidak
     * @return array
     * @throws ChaCha20Exception
     */
    public function encrypt(
        string $plaintext,
        ?string $key = null,
        ?string $nonce = null,
        int $counter = 1,
        bool $showRounds = false
    ): array {
        // Bangun payload: key & nonce hanya dikirim jika user menyediakan (jika null → Python auto-generate)
        // counter & show_rounds selalu dikirim eksplisit agar tidak bergantung default Python
        $payload = [
            'plaintext'   => $plaintext,
            'counter'     => $counter,
            'show_rounds' => $showRounds,
        ];

        if ($key !== null) {
            $payload['key'] = $key;
        }
        if ($nonce !== null) {
            $payload['nonce'] = $nonce;
        }

        return $this->post('/encrypt', $payload);
    }

    /**
     * Dekripsi ciphertext menggunakan ChaCha20.
     *
     * @param  string  $ciphertextHex  Ciphertext dalam format hex string
     * @param  string  $key            64 hex chars (256-bit)
     * @param  string  $nonce          24 hex chars (96-bit)
     * @param  int     $counter        Block counter awal (harus sama saat enkripsi)
     * @param  bool    $showRounds     Sertakan round_logs atau tidak
     * @return array
     * @throws ChaCha20Exception
     */
    public function decrypt(
        string $ciphertextHex,
        string $key,
        string $nonce,
        int $counter = 1,
        bool $showRounds = false
    ): array {
        return $this->post('/decrypt', [
            'ciphertext_hex' => $ciphertextHex,
            'key'            => $key,
            'nonce'          => $nonce,
            'counter'        => $counter,
            'show_rounds'    => $showRounds,
        ]);
    }

    /**
     * Ambil step-by-step state matrix untuk keperluan visualisasi.
     *
     * Method ini memanggil /encrypt dengan show_rounds=true
     * dan mengekstrak data round_logs yang diperlukan State Matrix Viewer.
     *
     * Struktur round_logs per entry:
     * - round: int|"final"
     * - type: "column"|"diagonal"|"quarter_round_detail"
     * - description: string
     * - state_matrix: array[4][4] of hex strings
     * - state_words: array[16] of hex strings
     * - quarter_rounds: array of string labels (untuk round summary)
     * - indices: {a,b,c,d} (untuk quarter_round_detail)
     * - affected_words: {state[n]: hex} (untuk quarter_round_detail)
     *
     * @param  string       $plaintext
     * @param  string|null  $key    64 hex chars (256-bit)
     * @param  string|null  $nonce  24 hex chars (96-bit)
     * @param  int          $counter
     * @return array{
     *     ciphertext_hex: string,
     *     key_hex: string,
     *     nonce_hex: string,
     *     total_steps: int,
     *     round_logs: array,
     *     summary: array
     * }
     * @throws ChaCha20Exception
     */
    public function steps(
        string $plaintext,
        ?string $key = null,
        ?string $nonce = null,
        int $counter = 1
    ): array {
        $result = $this->encrypt($plaintext, $key, $nonce, $counter, showRounds: true);

        $roundLogs = $result['round_logs'] ?? [];

        // Pisahkan jenis log agar frontend lebih mudah mengkonsumsinya
        $initialState      = collect($roundLogs)->firstWhere('round', 0);
        $finalState        = collect($roundLogs)->firstWhere('round', 'final');
        $roundSummaries    = collect($roundLogs)->filter(
            fn($l) => is_int($l['round']) && $l['round'] > 0
                && in_array($l['type'] ?? '', ['column', 'diagonal'])
        )->values()->all();
        $quarterRoundDetails = collect($roundLogs)->where('type', 'quarter_round_detail')
            ->values()
            ->all();

        return [
            'ciphertext_hex'       => $result['ciphertext_hex'],
            'key_hex'              => $result['key_hex'],
            'nonce_hex'            => $result['nonce_hex'],
            'counter'              => $result['counter'],
            'total_steps'          => count($roundLogs),
            'round_logs'           => $roundLogs,          // raw, semua entry
            'initial_state'        => $initialState,        // state sebelum ronde
            'final_state'          => $finalState,           // state setelah ronde + add
            'round_summaries'      => $roundSummaries,      // 20 round summary (col+diag)
            'quarter_round_details'=> $quarterRoundDetails,  // 80 QR detail per step
            'summary' => [
                'total_rounds'         => 20,
                'column_rounds'        => 10,
                'diagonal_rounds'      => 10,
                'quarter_rounds_total' => 80,
                'log_entries_total'    => count($roundLogs),
            ],
        ];
    }

    // ─────────────────────────────────────────────
    //  File Encrypt / Decrypt
    // ─────────────────────────────────────────────

    /**
     * Enkripsi file menggunakan ChaCha20.
     *
     * Mengirim file ke Python via multipart/form-data.
     * Key dan nonce dikembalikan via response headers.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string|null  $key    64 hex chars (256-bit). Auto-generate jika null.
     * @param  string|null  $nonce  24 hex chars (96-bit). Auto-generate jika null.
     * @param  int          $counter
     * @return array{content: string, key_hex: string, nonce_hex: string, original_filename: string, encrypted_filename: string, file_size: int, encrypted_size: int}
     * @throws ChaCha20Exception
     */
    public function encryptFile(
        \Illuminate\Http\UploadedFile $file,
        ?string $key = null,
        ?string $nonce = null,
        int $counter = 1
    ): array {
        return $this->postFile('/encrypt-file', $file, $key, $nonce, $counter);
    }

    /**
     * Dekripsi file menggunakan ChaCha20.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $key    64 hex chars (256-bit)
     * @param  string  $nonce  24 hex chars (96-bit)
     * @param  int     $counter
     * @return array{content: string, original_filename: string, decrypted_filename: string, file_size: int}
     * @throws ChaCha20Exception
     */
    public function decryptFile(
        \Illuminate\Http\UploadedFile $file,
        string $key,
        string $nonce,
        int $counter = 1
    ): array {
        return $this->postFile('/decrypt-file', $file, $key, $nonce, $counter);
    }

    // ─────────────────────────────────────────────
    //  HTTP Helpers
    // ─────────────────────────────────────────────

    /**
     * @throws ChaCha20Exception
     */
    private function get(string $path): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get($this->baseUrl . $path);

            return $this->handleResponse($response, $path);
        } catch (ConnectionException $e) {
            Log::error("ChaCha20Service: Cannot connect to microservice", [
                'url'   => $this->baseUrl . $path,
                'error' => $e->getMessage(),
            ]);
            throw new ChaCha20Exception(
                "Tidak bisa terhubung ke ChaCha20 microservice. Pastikan service Python sedang berjalan.",
                code: 503,
                previous: $e
            );
        }
    }

    /**
     * @throws ChaCha20Exception
     */
    private function post(string $path, array $payload): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->asJson()
                ->post($this->baseUrl . $path, $payload);

            return $this->handleResponse($response, $path);
        } catch (ConnectionException $e) {
            Log::error("ChaCha20Service: Cannot connect to microservice", [
                'url'   => $this->baseUrl . $path,
                'error' => $e->getMessage(),
            ]);
            throw new ChaCha20Exception(
                "Tidak bisa terhubung ke ChaCha20 microservice. Pastikan service Python sedang berjalan.",
                code: 503,
                previous: $e
            );
        }
    }

    /**
     * Send a file to the Python microservice via multipart/form-data.
     *
     * @throws ChaCha20Exception
     */
    private function postFile(
        string $path,
        \Illuminate\Http\UploadedFile $file,
        ?string $key,
        ?string $nonce,
        int $counter
    ): array {
        try {
            $request = Http::timeout($this->timeout)
                ->attach('file', $file->getContent(), $file->getClientOriginalName());

            // Add form fields
            $formData = ['counter' => $counter];
            if ($key !== null) {
                $formData['key'] = $key;
            }
            if ($nonce !== null) {
                $formData['nonce'] = $nonce;
            }

            $response = $request->post($this->baseUrl . $path, $formData);

            if (!$response->successful()) {
                $body = $response->json() ?? [];
                $detail = $body['detail'] ?? 'Unknown error from microservice';

                Log::warning("ChaCha20Service: File API error", [
                    'path'   => $path,
                    'status' => $response->status(),
                    'body'   => $body,
                ]);

                throw new ChaCha20Exception(
                    is_string($detail) ? $detail : json_encode($detail),
                    apiError: $body,
                    code: $response->status()
                );
            }

            // Extract metadata from response headers
            $contentDisposition = $response->header('Content-Disposition') ?? '';
            preg_match('/filename="?([^"]+)"?/', $contentDisposition, $matches);
            $resultFilename = $matches[1] ?? 'result_file';

            return [
                'content'            => $response->body(),
                'key_hex'            => $response->header('X-Key-Hex') ?? '',
                'nonce_hex'          => $response->header('X-Nonce-Hex') ?? '',
                'original_filename'  => $response->header('X-Original-Filename') ?? $file->getClientOriginalName(),
                'result_filename'    => $resultFilename,
                'file_size'          => (int) ($response->header('X-File-Size') ?? strlen($response->body())),
                'content_length'     => strlen($response->body()),
            ];
        } catch (ConnectionException $e) {
            Log::error("ChaCha20Service: Cannot connect to microservice", [
                'url'   => $this->baseUrl . $path,
                'error' => $e->getMessage(),
            ]);
            throw new ChaCha20Exception(
                "Tidak bisa terhubung ke ChaCha20 microservice. Pastikan service Python sedang berjalan.",
                code: 503,
                previous: $e
            );
        }
    }

    /**
     * @throws ChaCha20Exception
     */
    private function handleResponse(\Illuminate\Http\Client\Response $response, string $path): array
    {
        if ($response->successful()) {
            return $response->json();
        }

        $body = $response->json();
        $detail = $body['detail'] ?? 'Unknown error from microservice';

        Log::warning("ChaCha20Service: API error", [
            'path'   => $path,
            'status' => $response->status(),
            'body'   => $body,
        ]);

        throw new ChaCha20Exception(
            is_string($detail) ? $detail : json_encode($detail),
            apiError: $body,
            code: $response->status()
        );
    }
}
