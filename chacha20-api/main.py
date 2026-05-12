"""
Crypto Microservice — FastAPI

Endpoints:
    GET  /                    → Service info
    GET  /keygen              → Generate random ChaCha20 key + nonce
    POST /encrypt             → Encrypt plaintext with ChaCha20
    POST /decrypt             → Decrypt ciphertext with ChaCha20
    POST /encrypt-file        → Encrypt uploaded file with ChaCha20
    POST /decrypt-file        → Decrypt uploaded file with ChaCha20
    POST /caesar/encrypt      → Encrypt plaintext with Caesar cipher
    POST /caesar/decrypt      → Decrypt ciphertext with Caesar cipher
    POST /caesar/brute-force  → Try all 26 shifts (crack)
    GET  /caesar/shift-table  → Get shifted alphabet table

All crypto logic is implemented from scratch.
"""

import base64
import io
from fastapi import FastAPI, HTTPException, UploadFile, File, Form
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import StreamingResponse
from pydantic import BaseModel, Field
from typing import Optional, Any

from chacha20 import chacha20_crypt, generate_key, generate_nonce
from caesar import caesar_encrypt, caesar_decrypt, caesar_brute_force, generate_shift_table


# ─────────────────────────────────────────────
#  Constants
# ─────────────────────────────────────────────
MAX_FILE_SIZE = 5 * 1024 * 1024  # 5 MB


# ─────────────────────────────────────────────
#  FastAPI App
# ─────────────────────────────────────────────

app = FastAPI(
    title="Crypto Microservice",
    description=(
        "From-scratch implementations of ChaCha20 (RFC 8439) and Caesar cipher "
        "with step-by-step logging for educational visualization.\n\n"
        "**No external cryptographic libraries are used.**"
    ),
    version="2.0.0",
    docs_url="/docs",
    redoc_url="/redoc",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


# ─────────────────────────────────────────────
#  Request / Response Schemas
# ─────────────────────────────────────────────

class EncryptRequest(BaseModel):
    plaintext: str = Field(
        ...,
        description="Plaintext to encrypt (UTF-8 string)",
        examples=["Hello, ChaCha20!"],
    )
    key: Optional[str] = Field(
        None,
        description="256-bit key as 64 hex characters. Auto-generated if omitted.",
        examples=["000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f"],
    )
    nonce: Optional[str] = Field(
        None,
        description="96-bit nonce as 24 hex characters. Auto-generated if omitted.",
        examples=["000000000000004a00000000"],
    )
    counter: int = Field(
        1,
        description="Initial block counter (default: 1 per RFC 8439)",
    )
    show_rounds: bool = Field(
        False,
        description="If true, include step-by-step round logs (20 rounds) in the response",
    )

    model_config = {
        "json_schema_extra": {
            "examples": [
                {
                    "plaintext": "Hello, ChaCha20!",
                    "show_rounds": True,
                }
            ]
        }
    }


class EncryptResponse(BaseModel):
    ciphertext_hex: str
    ciphertext_base64: str
    key_hex: str
    nonce_hex: str
    counter: int
    plaintext_length: int
    ciphertext_length: int
    round_logs: Optional[list[Any]] = None


class DecryptRequest(BaseModel):
    ciphertext_hex: str = Field(
        ...,
        description="Ciphertext as hex string",
    )
    key: str = Field(
        ...,
        description="256-bit key as 64 hex characters (must match encryption key)",
    )
    nonce: str = Field(
        ...,
        description="96-bit nonce as 24 hex characters (must match encryption nonce)",
    )
    counter: int = Field(
        1,
        description="Initial block counter (must match encryption counter)",
    )
    show_rounds: bool = Field(
        False,
        description="If true, include step-by-step round logs in the response",
    )


class DecryptResponse(BaseModel):
    plaintext: str
    plaintext_hex: str
    key_hex: str
    nonce_hex: str
    round_logs: Optional[list[Any]] = None


class KeygenResponse(BaseModel):
    key_hex: str
    key_base64: str
    key_length_bits: int = 256
    nonce_hex: str
    nonce_base64: str
    nonce_length_bits: int = 96


# ─────────────────────────────────────────────
#  Helpers
# ─────────────────────────────────────────────

def _parse_hex(hex_str: str, expected_bytes: int, field_name: str) -> bytes:
    """Parse a hex string and validate its length."""
    hex_str = hex_str.strip().lower()
    try:
        data = bytes.fromhex(hex_str)
    except ValueError:
        raise HTTPException(
            status_code=400,
            detail=f"Invalid hex string for '{field_name}': contains non-hex characters",
        )
    if len(data) != expected_bytes:
        raise HTTPException(
            status_code=400,
            detail=(
                f"'{field_name}' must be exactly {expected_bytes} bytes "
                f"({expected_bytes * 2} hex chars), got {len(data)} bytes "
                f"({len(data) * 2} hex chars)"
            ),
        )
    return data


# ─────────────────────────────────────────────
#  Endpoints
# ─────────────────────────────────────────────

@app.get("/", tags=["Info"])
async def root():
    """Service information and algorithm details."""
    return {
        "service": "ChaCha20 Crypto Microservice",
        "version": "1.0.0",
        "implementation": "Pure Python — no external crypto libraries",
        "endpoints": {
            "GET  /keygen":  "Generate random 256-bit key and 96-bit nonce",
            "POST /encrypt": "Encrypt UTF-8 plaintext → hex ciphertext",
            "POST /decrypt": "Decrypt hex ciphertext → UTF-8 plaintext",
            "GET  /docs":    "Interactive Swagger UI documentation",
            "GET  /redoc":   "ReDoc documentation",
        },
        "algorithm": {
            "name": "ChaCha20",
            "specification": "RFC 8439",
            "key_size_bits": 256,
            "nonce_size_bits": 96,
            "counter_size_bits": 32,
            "block_size_bits": 512,
            "rounds": 20,
            "constants": "expand 32-byte k",
            "operations": "ARX (Addition, Rotation, XOR)",
        },
    }


@app.get("/keygen", response_model=KeygenResponse, tags=["Crypto"])
async def keygen():
    """
    Generate a cryptographically secure random key and nonce.

    - **Key**: 256-bit (32 bytes) — suitable for ChaCha20
    - **Nonce**: 96-bit (12 bytes) — must be unique per key usage
    """
    key = generate_key()
    nonce = generate_nonce()

    return KeygenResponse(
        key_hex=key.hex(),
        key_base64=base64.b64encode(key).decode(),
        nonce_hex=nonce.hex(),
        nonce_base64=base64.b64encode(nonce).decode(),
    )


@app.post("/encrypt", response_model=EncryptResponse, tags=["Crypto"])
async def encrypt(req: EncryptRequest):
    """
    Encrypt plaintext using ChaCha20.

    - If `key` or `nonce` are omitted, they will be auto-generated.
    - Set `show_rounds: true` to get step-by-step state matrix logs
      for all 20 rounds (useful for visualization / education).
    """
    # Parse or generate key
    if req.key:
        key = _parse_hex(req.key, 32, "key")
    else:
        key = generate_key()

    # Parse or generate nonce
    if req.nonce:
        nonce = _parse_hex(req.nonce, 12, "nonce")
    else:
        nonce = generate_nonce()

    plaintext_bytes = req.plaintext.encode("utf-8")
    if not plaintext_bytes:
        raise HTTPException(status_code=400, detail="Plaintext cannot be empty")

    ciphertext, logs = chacha20_crypt(
        key, nonce, plaintext_bytes, req.counter, req.show_rounds
    )

    return EncryptResponse(
        ciphertext_hex=ciphertext.hex(),
        ciphertext_base64=base64.b64encode(ciphertext).decode(),
        key_hex=key.hex(),
        nonce_hex=nonce.hex(),
        counter=req.counter,
        plaintext_length=len(plaintext_bytes),
        ciphertext_length=len(ciphertext),
        round_logs=logs,
    )


@app.post("/decrypt", response_model=DecryptResponse, tags=["Crypto"])
async def decrypt(req: DecryptRequest):
    """
    Decrypt ciphertext using ChaCha20.

    The same key, nonce, and counter used during encryption must be provided.
    ChaCha20 is a stream cipher — decryption is identical to encryption (XOR).
    """
    key = _parse_hex(req.key, 32, "key")
    nonce = _parse_hex(req.nonce, 12, "nonce")

    try:
        ciphertext = bytes.fromhex(req.ciphertext_hex.strip())
    except ValueError:
        raise HTTPException(
            status_code=400,
            detail="Invalid hex string for 'ciphertext_hex'",
        )

    if not ciphertext:
        raise HTTPException(status_code=400, detail="Ciphertext cannot be empty")

    plaintext_bytes, logs = chacha20_crypt(
        key, nonce, ciphertext, req.counter, req.show_rounds
    )

    # Attempt UTF-8 decode, fallback to latin-1 for binary data
    try:
        plaintext_str = plaintext_bytes.decode("utf-8")
    except UnicodeDecodeError:
        plaintext_str = plaintext_bytes.decode("latin-1")

    return DecryptResponse(
        plaintext=plaintext_str,
        plaintext_hex=plaintext_bytes.hex(),
        key_hex=key.hex(),
        nonce_hex=nonce.hex(),
        round_logs=logs,
    )


# ─────────────────────────────────────────────
#  File Encrypt / Decrypt Endpoints
# ─────────────────────────────────────────────

@app.post("/encrypt-file", tags=["File Crypto"])
async def encrypt_file(
    file: UploadFile = File(..., description="File to encrypt (max 5 MB)"),
    key: Optional[str] = Form(None, description="256-bit key as 64 hex chars. Auto-generated if omitted."),
    nonce: Optional[str] = Form(None, description="96-bit nonce as 24 hex chars. Auto-generated if omitted."),
    counter: int = Form(1, description="Initial block counter (default: 1)"),
):
    """
    Encrypt an uploaded file using ChaCha20.

    The encrypted file is returned as a downloadable binary.
    Key and nonce are returned in response headers:
    - `X-Key-Hex`: The 256-bit key used (64 hex chars)
    - `X-Nonce-Hex`: The 96-bit nonce used (24 hex chars)
    - `X-Original-Filename`: The original filename

    **Save the key and nonce — you need them for decryption!**
    """
    # Read and validate file size
    file_bytes = await file.read()
    if len(file_bytes) > MAX_FILE_SIZE:
        raise HTTPException(
            status_code=400,
            detail=f"File too large. Maximum size is {MAX_FILE_SIZE // (1024*1024)} MB, got {len(file_bytes) / (1024*1024):.2f} MB.",
        )
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="File is empty.")

    # Parse or generate key
    if key:
        key_bytes = _parse_hex(key, 32, "key")
    else:
        key_bytes = generate_key()

    # Parse or generate nonce
    if nonce:
        nonce_bytes = _parse_hex(nonce, 12, "nonce")
    else:
        nonce_bytes = generate_nonce()

    # Encrypt
    ciphertext, _ = chacha20_crypt(key_bytes, nonce_bytes, file_bytes, counter)

    # Return encrypted file as download
    original_filename = file.filename or "file"
    encrypted_filename = f"encrypted_{original_filename}"

    return StreamingResponse(
        io.BytesIO(ciphertext),
        media_type="application/octet-stream",
        headers={
            "Content-Disposition": f'attachment; filename="{encrypted_filename}"',
            "X-Key-Hex": key_bytes.hex(),
            "X-Nonce-Hex": nonce_bytes.hex(),
            "X-Original-Filename": original_filename,
            "X-File-Size": str(len(file_bytes)),
            "X-Encrypted-Size": str(len(ciphertext)),
            "Access-Control-Expose-Headers": "X-Key-Hex, X-Nonce-Hex, X-Original-Filename, X-File-Size, X-Encrypted-Size",
        },
    )


@app.post("/decrypt-file", tags=["File Crypto"])
async def decrypt_file(
    file: UploadFile = File(..., description="Encrypted file to decrypt (max 5 MB)"),
    key: str = Form(..., description="256-bit key as 64 hex chars (must match encryption key)"),
    nonce: str = Form(..., description="96-bit nonce as 24 hex chars (must match encryption nonce)"),
    counter: int = Form(1, description="Initial block counter (must match encryption counter)"),
):
    """
    Decrypt an uploaded file using ChaCha20.

    The same key, nonce, and counter used during encryption must be provided.
    The decrypted file is returned as a downloadable binary.
    """
    # Read and validate file size
    file_bytes = await file.read()
    if len(file_bytes) > MAX_FILE_SIZE:
        raise HTTPException(
            status_code=400,
            detail=f"File too large. Maximum size is {MAX_FILE_SIZE // (1024*1024)} MB, got {len(file_bytes) / (1024*1024):.2f} MB.",
        )
    if len(file_bytes) == 0:
        raise HTTPException(status_code=400, detail="File is empty.")

    key_bytes = _parse_hex(key, 32, "key")
    nonce_bytes = _parse_hex(nonce, 12, "nonce")

    # Decrypt (ChaCha20 is symmetric — same XOR operation)
    plaintext_bytes, _ = chacha20_crypt(key_bytes, nonce_bytes, file_bytes, counter)

    # Return decrypted file as download
    original_filename = file.filename or "file"
    # Strip "encrypted_" prefix if present
    if original_filename.startswith("encrypted_"):
        decrypted_filename = original_filename[len("encrypted_"):]
    else:
        decrypted_filename = f"decrypted_{original_filename}"

    return StreamingResponse(
        io.BytesIO(plaintext_bytes),
        media_type="application/octet-stream",
        headers={
            "Content-Disposition": f'attachment; filename="{decrypted_filename}"',
            "X-Original-Filename": original_filename,
            "X-File-Size": str(len(plaintext_bytes)),
        },
    )


# ─────────────────────────────────────────────
#  Caesar Cipher Schemas
# ─────────────────────────────────────────────

class CaesarEncryptRequest(BaseModel):
    plaintext: str = Field(
        ...,
        description="Plaintext to encrypt",
        examples=["Hello, World!"],
        min_length=1,
        max_length=10000,
    )
    shift: int = Field(
        3,
        description="Number of positions to shift (0-25). Default: 3",
        ge=0,
        le=25,
    )
    show_steps: bool = Field(
        False,
        description="If true, include step-by-step substitution logs",
    )


class CaesarDecryptRequest(BaseModel):
    ciphertext: str = Field(
        ...,
        description="Ciphertext to decrypt",
        examples=["Khoor, Zruog!"],
        min_length=1,
        max_length=10000,
    )
    shift: int = Field(
        3,
        description="Shift that was used for encryption (0-25)",
        ge=0,
        le=25,
    )
    show_steps: bool = Field(
        False,
        description="If true, include step-by-step substitution logs",
    )


class CaesarBruteForceRequest(BaseModel):
    ciphertext: str = Field(
        ...,
        description="Ciphertext to crack by trying all 26 shifts",
        min_length=1,
        max_length=10000,
    )


# ─────────────────────────────────────────────
#  Caesar Cipher Endpoints
# ─────────────────────────────────────────────

@app.post("/caesar/encrypt", tags=["Caesar Cipher"])
async def caesar_encrypt_endpoint(req: CaesarEncryptRequest):
    """
    Encrypt plaintext using Caesar cipher.

    Each letter is shifted by the specified number of positions.
    Non-alphabetic characters are preserved unchanged.
    """
    ciphertext, logs = caesar_encrypt(req.plaintext, req.shift, req.show_steps)

    return {
        "ciphertext": ciphertext,
        "plaintext": req.plaintext,
        "shift": req.shift % 26,
        "plaintext_length": len(req.plaintext),
        "ciphertext_length": len(ciphertext),
        "alphabet_table": generate_shift_table(req.shift),
        "step_logs": logs,
    }


@app.post("/caesar/decrypt", tags=["Caesar Cipher"])
async def caesar_decrypt_endpoint(req: CaesarDecryptRequest):
    """
    Decrypt ciphertext using Caesar cipher.

    The same shift value used during encryption must be provided.
    """
    plaintext, logs = caesar_decrypt(req.ciphertext, req.shift, req.show_steps)

    return {
        "plaintext": plaintext,
        "ciphertext": req.ciphertext,
        "shift": req.shift % 26,
        "step_logs": logs,
    }


@app.post("/caesar/brute-force", tags=["Caesar Cipher"])
async def caesar_brute_force_endpoint(req: CaesarBruteForceRequest):
    """
    Try all 26 possible shifts to crack the ciphertext.

    Returns all 26 possible plaintexts — the user must identify
    which one is the correct decryption.
    """
    results = caesar_brute_force(req.ciphertext)

    return {
        "ciphertext": req.ciphertext,
        "results": results,
        "total_attempts": 26,
    }


@app.get("/caesar/shift-table", tags=["Caesar Cipher"])
async def caesar_shift_table(shift: int = 3):
    """
    Get the shifted alphabet mapping for a given shift value.

    Useful for visualizing how letters map from original to shifted alphabet.
    """
    if shift < 0 or shift > 25:
        raise HTTPException(status_code=400, detail="Shift must be between 0 and 25")

    return generate_shift_table(shift)

