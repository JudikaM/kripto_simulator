<?php

namespace App\Http\Controllers;

use App\Exceptions\ChaCha20Exception;
use App\Http\Requests\ChaCha20DecryptRequest;
use App\Http\Requests\ChaCha20EncryptRequest;
use App\Http\Requests\ChaCha20FileEncryptRequest;
use App\Http\Requests\ChaCha20FileDecryptRequest;
use App\Http\Requests\ChaCha20StepsRequest;
use App\Services\ChaCha20Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChaCha20Controller extends Controller
{
    public function __construct(
        private readonly ChaCha20Service $chacha20
    ) {}

    // ─────────────────────────────────────────────
    //  Web
    // ─────────────────────────────────────────────

    /**
     * Tampilkan halaman simulator ChaCha20.
     */
    public function index(): \Illuminate\View\View
    {
        return view('chacha20.index', [
            'apiUrl' => config('services.chacha20.url', 'http://python:8001'),
        ]);
    }

    // ─────────────────────────────────────────────
    //  API Endpoints (JSON)
    // ─────────────────────────────────────────────

    /**
     * GET /chacha20/keygen
     * Generate key 256-bit dan nonce 96-bit secara acak.
     */
    public function keygen(): JsonResponse
    {
        try {
            $result = $this->chacha20->keygen();
            return response()->json($result);
        } catch (ChaCha20Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * POST /chacha20/encrypt
     * Enkripsi plaintext dengan ChaCha20.
     *
     * Body:
     *   - plaintext    : string (required)
     *   - key          : string|null (64 hex chars, opsional - auto-generate jika kosong)
     *   - nonce        : string|null (24 hex chars, opsional - auto-generate jika kosong)
     *   - counter      : int (default: 1)
     *   - show_rounds  : bool (default: false)
     *
     * Response:
     *   - ciphertext_hex, ciphertext_base64, key_hex, nonce_hex,
     *     counter, plaintext_length, ciphertext_length, round_logs?
     */
    public function encrypt(ChaCha20EncryptRequest $request): JsonResponse
    {
        try {
            $result = $this->chacha20->encrypt(
                plaintext:  $request->input('plaintext'),
                key:        $request->input('key'),
                nonce:      $request->input('nonce'),
                counter:    (int) $request->input('counter', 1),
                showRounds: (bool) $request->input('show_rounds', false),
            );

            return response()->json($result);
        } catch (ChaCha20Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * POST /chacha20/decrypt
     * Dekripsi ciphertext dengan ChaCha20.
     *
     * Body:
     *   - ciphertext_hex : string (required, hex)
     *   - key            : string (required, 64 hex chars)
     *   - nonce          : string (required, 24 hex chars)
     *   - counter        : int (default: 1, harus sama dengan saat enkripsi)
     *   - show_rounds    : bool (default: false)
     *
     * Response:
     *   - plaintext, plaintext_hex, key_hex, nonce_hex, round_logs?
     */
    public function decrypt(ChaCha20DecryptRequest $request): JsonResponse
    {
        try {
            $result = $this->chacha20->decrypt(
                ciphertextHex: $request->input('ciphertext_hex'),
                key:           $request->input('key'),
                nonce:         $request->input('nonce'),
                counter:       (int) $request->input('counter', 1),
                showRounds:    (bool) $request->input('show_rounds', false),
            );

            return response()->json($result);
        } catch (ChaCha20Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * POST /chacha20/steps
     * Ambil step-by-step state matrix untuk keperluan visualisasi (State Matrix Viewer).
     *
     * Endpoint ini selalu mengaktifkan show_rounds=true ke Python,
     * lalu memformat ulang round_logs menjadi struktur yang lebih kaya
     * untuk dikonsumsi langsung oleh komponen Alpine.js frontend.
     *
     * Body:
     *   - plaintext : string (required)
     *   - key       : string|null (64 hex chars, opsional)
     *   - nonce     : string|null (24 hex chars, opsional)
     *   - counter   : int (default: 1)
     *
     * Response:
     *   - ciphertext_hex, key_hex, nonce_hex, counter
     *   - total_steps    : total jumlah log entry
     *   - round_logs     : semua entry mentah dari Python
     *   - initial_state  : state sebelum ronde dimulai
     *   - final_state    : state setelah ronde + final addition
     *   - round_summaries      : 20 entry (per ronde column/diagonal)
     *   - quarter_round_details: 80 entry (detail tiap QR)
     *   - summary        : meta info (total_rounds, dll.)
     */
    public function steps(ChaCha20StepsRequest $request): JsonResponse
    {
        try {
            $result = $this->chacha20->steps(
                plaintext: $request->input('plaintext'),
                key:       $request->input('key'),
                nonce:     $request->input('nonce'),
                counter:   (int) $request->input('counter', 1),
            );

            return response()->json($result);
        } catch (ChaCha20Exception $e) {
            return $this->errorResponse($e);
        }
    }

    // ─────────────────────────────────────────────
    //  File Encrypt / Decrypt
    // ─────────────────────────────────────────────

    /**
     * POST /chacha20/encrypt-file
     * Enkripsi file dengan ChaCha20.
     *
     * Menerima multipart/form-data, mengembalikan JSON dengan
     * metadata + file content sebagai base64.
     */
    public function encryptFile(ChaCha20FileEncryptRequest $request): JsonResponse
    {
        try {
            $result = $this->chacha20->encryptFile(
                file:    $request->file('file'),
                key:     $request->input('key'),
                nonce:   $request->input('nonce'),
                counter: (int) $request->input('counter', 1),
            );

            return response()->json([
                'success'          => true,
                'result_filename'  => $result['result_filename'],
                'original_filename'=> $result['original_filename'],
                'key_hex'          => $result['key_hex'],
                'nonce_hex'        => $result['nonce_hex'],
                'file_size'        => $result['file_size'],
                'content_length'   => $result['content_length'],
                'file_base64'      => base64_encode($result['content']),
            ]);
        } catch (ChaCha20Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * POST /chacha20/decrypt-file
     * Dekripsi file dengan ChaCha20.
     */
    public function decryptFile(ChaCha20FileDecryptRequest $request): JsonResponse
    {
        try {
            $result = $this->chacha20->decryptFile(
                file:    $request->file('file'),
                key:     $request->input('key'),
                nonce:   $request->input('nonce'),
                counter: (int) $request->input('counter', 1),
            );

            return response()->json([
                'success'          => true,
                'result_filename'  => $result['result_filename'],
                'original_filename'=> $result['original_filename'],
                'file_size'        => $result['file_size'],
                'content_length'   => $result['content_length'],
                'file_base64'      => base64_encode($result['content']),
            ]);
        } catch (ChaCha20Exception $e) {
            return $this->errorResponse($e);
        }
    }

    // ─────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────

    private function errorResponse(ChaCha20Exception $e): JsonResponse
    {
        $status = $e->getCode() ?: 500;

        // Pastikan status code valid (100–599)
        if ($status < 100 || $status > 599) {
            $status = 500;
        }

        return response()->json([
            'error'     => true,
            'message'   => $e->getMessage(),
            'api_error' => $e->getApiError(),
        ], $status);
    }
}
