<?php

namespace App\Services;

use App\Exceptions\ChaCha20Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CaesarService
 *
 * Berkomunikasi dengan Python FastAPI microservice untuk Caesar cipher.
 * Menggunakan URL yang sama dengan ChaCha20 (CHACHA20_SERVICE_URL).
 */
class CaesarService
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.chacha20.url', 'http://python:8001'), '/');
        $this->timeout = (int) config('services.chacha20.timeout', 30);
    }

    /**
     * Enkripsi plaintext menggunakan Caesar cipher.
     */
    public function encrypt(string $plaintext, int $shift = 3, bool $showSteps = false): array
    {
        return $this->post('/caesar/encrypt', [
            'plaintext'  => $plaintext,
            'shift'      => $shift,
            'show_steps' => $showSteps,
        ]);
    }

    /**
     * Dekripsi ciphertext menggunakan Caesar cipher.
     */
    public function decrypt(string $ciphertext, int $shift = 3, bool $showSteps = false): array
    {
        return $this->post('/caesar/decrypt', [
            'ciphertext' => $ciphertext,
            'shift'      => $shift,
            'show_steps' => $showSteps,
        ]);
    }

    /**
     * Brute force — coba semua 26 shift.
     */
    public function bruteForce(string $ciphertext): array
    {
        return $this->post('/caesar/brute-force', [
            'ciphertext' => $ciphertext,
        ]);
    }

    /**
     * Ambil tabel alfabet yang sudah di-shift.
     */
    public function shiftTable(int $shift = 3): array
    {
        return $this->get("/caesar/shift-table?shift={$shift}");
    }

    // ─────────────────────────────────────────────
    //  HTTP Helpers
    // ─────────────────────────────────────────────

    private function get(string $path): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get($this->baseUrl . $path);

            return $this->handleResponse($response, $path);
        } catch (ConnectionException $e) {
            Log::error("CaesarService: Cannot connect to microservice", [
                'url'   => $this->baseUrl . $path,
                'error' => $e->getMessage(),
            ]);
            throw new ChaCha20Exception(
                "Tidak bisa terhubung ke microservice. Pastikan service Python sedang berjalan.",
                code: 503,
                previous: $e
            );
        }
    }

    private function post(string $path, array $payload): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->asJson()
                ->post($this->baseUrl . $path, $payload);

            return $this->handleResponse($response, $path);
        } catch (ConnectionException $e) {
            Log::error("CaesarService: Cannot connect to microservice", [
                'url'   => $this->baseUrl . $path,
                'error' => $e->getMessage(),
            ]);
            throw new ChaCha20Exception(
                "Tidak bisa terhubung ke microservice. Pastikan service Python sedang berjalan.",
                code: 503,
                previous: $e
            );
        }
    }

    private function handleResponse(\Illuminate\Http\Client\Response $response, string $path): array
    {
        if ($response->successful()) {
            return $response->json();
        }

        $body = $response->json();
        $detail = $body['detail'] ?? 'Unknown error from microservice';

        Log::warning("CaesarService: API error", [
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
