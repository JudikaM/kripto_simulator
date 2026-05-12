<?php

use App\Http\Controllers\ChaCha20Controller;
use App\Http\Controllers\CaesarController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('chacha20.index'));

// ─────────────────────────────────────────────
//  ChaCha20 Simulator
// ─────────────────────────────────────────────
Route::prefix('chacha20')->name('chacha20.')->group(function () {

    // Halaman simulator (Blade view)
    Route::get('/', [ChaCha20Controller::class, 'index'])->name('index');
    
    // Halaman Edukasi "Kisah ChaCha20"
    Route::view('/learn', 'chacha20.learn')->name('learn');

    // JSON endpoints — dikonsumsi Alpine.js di frontend
    Route::get('/keygen',   [ChaCha20Controller::class, 'keygen'])->name('keygen');
    Route::post('/encrypt', [ChaCha20Controller::class, 'encrypt'])->name('encrypt');
    Route::post('/decrypt', [ChaCha20Controller::class, 'decrypt'])->name('decrypt');

    // Khusus untuk State Matrix Viewer — selalu mengembalikan round_logs
    Route::post('/steps',   [ChaCha20Controller::class, 'steps'])->name('steps');

    // File encrypt/decrypt — multipart/form-data
    Route::post('/encrypt-file', [ChaCha20Controller::class, 'encryptFile'])->name('encrypt-file');
    Route::post('/decrypt-file', [ChaCha20Controller::class, 'decryptFile'])->name('decrypt-file');

});

// ─────────────────────────────────────────────
//  Caesar Cipher Simulator
// ─────────────────────────────────────────────
Route::prefix('caesar')->name('caesar.')->group(function () {

    // Halaman simulator (Blade view)
    Route::get('/', [CaesarController::class, 'index'])->name('index');

    // JSON endpoints
    Route::post('/encrypt',     [CaesarController::class, 'encrypt'])->name('encrypt');
    Route::post('/decrypt',     [CaesarController::class, 'decrypt'])->name('decrypt');
    Route::post('/brute-force', [CaesarController::class, 'bruteForce'])->name('brute-force');
    Route::get('/shift-table',  [CaesarController::class, 'shiftTable'])->name('shift-table');

});
