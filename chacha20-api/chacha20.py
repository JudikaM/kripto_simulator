"""
ChaCha20 Stream Cipher — Pure Python Implementation (No External Crypto Libraries)

Implements the ChaCha20 algorithm as specified in RFC 8439.

State Matrix Layout (512-bit = 16 × 32-bit words):
┌───────────┬───────────┬───────────┬───────────┐
│ const[0]  │ const[1]  │ const[2]  │ const[3]  │  ← "expand 32-byte k"
├───────────┼───────────┼───────────┼───────────┤
│  key[0]   │  key[1]   │  key[2]   │  key[3]   │  ← 256-bit key
├───────────┼───────────┼───────────┼───────────┤
│  key[4]   │  key[5]   │  key[6]   │  key[7]   │  ← (continued)
├───────────┼───────────┼───────────┼───────────┤
│  counter  │ nonce[0]  │ nonce[1]  │ nonce[2]  │  ← 32-bit ctr + 96-bit nonce
└───────────┴───────────┴───────────┴───────────┘
"""

import struct
import os
from typing import Optional


# ─────────────────────────────────────────────
#  Constants: "expand 32-byte k" (little-endian)
# ─────────────────────────────────────────────
SIGMA = [
    0x61707865,  # "expa"
    0x3320646e,  # "nd 3"
    0x79622d32,  # "2-by"
    0x6b206574,  # "te k"
]


# ─────────────────────────────────────────────
#  Primitive Operations
# ─────────────────────────────────────────────

def _rotl32(v: int, n: int) -> int:
    """Rotate a 32-bit unsigned integer left by n bits."""
    return ((v << n) | (v >> (32 - n))) & 0xFFFFFFFF


def _add32(a: int, b: int) -> int:
    """Add two 32-bit unsigned integers with modular wrap."""
    return (a + b) & 0xFFFFFFFF


# ─────────────────────────────────────────────
#  Step Logger
# ─────────────────────────────────────────────

class StepLogger:
    """
    Records state matrix changes at every round of ChaCha20
    for educational visualization purposes.
    """

    def __init__(self):
        self.entries: list[dict] = []

    def log_state(
        self,
        round_num: int | str,
        description: str,
        state: list[int],
        round_type: Optional[str] = None,
        quarter_rounds_applied: Optional[list[str]] = None,
    ):
        entry = {
            "round": round_num,
            "description": description,
            "state_matrix": self._to_matrix(state),
            "state_words": [f"0x{w:08x}" for w in state],
        }
        if round_type:
            entry["type"] = round_type
        if quarter_rounds_applied:
            entry["quarter_rounds"] = quarter_rounds_applied
        self.entries.append(entry)

    def log_quarter_round(
        self,
        round_num: int,
        qr_label: str,
        state: list[int],
        a: int, b: int, c: int, d: int,
        arx_micro_steps: list[dict] | None = None,
    ):
        self.entries.append({
            "round": round_num,
            "description": f"After {qr_label}",
            "type": "quarter_round_detail",
            "indices": {"a": a, "b": b, "c": c, "d": d},
            "affected_words": {
                f"state[{a}]": f"0x{state[a]:08x}",
                f"state[{b}]": f"0x{state[b]:08x}",
                f"state[{c}]": f"0x{state[c]:08x}",
                f"state[{d}]": f"0x{state[d]:08x}",
            },
            "state_matrix": self._to_matrix(state),
            "arx_micro_steps": arx_micro_steps or [],
        })

    @staticmethod
    def _to_matrix(state: list[int]) -> list[list[str]]:
        """Format the 16-word state as a 4×4 matrix of hex strings."""
        return [
            [f"0x{state[r * 4 + c]:08x}" for c in range(4)]
            for r in range(4)
        ]

    def to_list(self) -> list[dict]:
        return self.entries


# ─────────────────────────────────────────────
#  ARX Micro-Step Helpers
# ─────────────────────────────────────────────

def _fmt(v: int) -> str:
    """Format a 32-bit int as 0x-prefixed hex."""
    return f"0x{v:08x}"

def _bin32(v: int) -> str:
    """Format a 32-bit int as a 32-char binary string."""
    return f"{v:032b}"

def _record_add(target_label: str, src_label: str,
                target_idx: int, src_idx: int,
                val_before: int, src_val: int, val_after: int) -> dict:
    return {
        "op": "ADD",
        "symbol": "+",
        "description": f"{target_label} += {src_label}",
        "target_idx": target_idx,
        "source_idx": src_idx,
        "operand1_hex": _fmt(val_before),
        "operand1_bin": _bin32(val_before),
        "operand2_hex": _fmt(src_val),
        "operand2_bin": _bin32(src_val),
        "result_hex": _fmt(val_after),
        "result_bin": _bin32(val_after),
    }

def _record_xor(target_label: str, src_label: str,
                target_idx: int, src_idx: int,
                val_before: int, src_val: int, val_after: int) -> dict:
    return {
        "op": "XOR",
        "symbol": "⊕",
        "description": f"{target_label} ^= {src_label}",
        "target_idx": target_idx,
        "source_idx": src_idx,
        "operand1_hex": _fmt(val_before),
        "operand1_bin": _bin32(val_before),
        "operand2_hex": _fmt(src_val),
        "operand2_bin": _bin32(src_val),
        "result_hex": _fmt(val_after),
        "result_bin": _bin32(val_after),
    }

def _record_rot(target_label: str, target_idx: int,
                val_before: int, val_after: int, shift: int) -> dict:
    return {
        "op": "ROT",
        "symbol": f"<<<",
        "description": f"{target_label} <<<= {shift}",
        "target_idx": target_idx,
        "shift": shift,
        "operand1_hex": _fmt(val_before),
        "operand1_bin": _bin32(val_before),
        "result_hex": _fmt(val_after),
        "result_bin": _bin32(val_after),
    }


# ─────────────────────────────────────────────
#  Quarter Round (ARX)
# ─────────────────────────────────────────────

def quarter_round(
    state: list[int],
    a: int, b: int, c: int, d: int,
    logger: Optional[StepLogger] = None,
    round_num: int = 0,
    qr_label: str = "",
):
    """
    ChaCha20 Quarter Round — ARX (Addition, Rotation, XOR).

    Operations:
        a += b; d ^= a; d <<<= 16
        c += d; b ^= c; b <<<= 12
        a += b; d ^= a; d <<<= 8
        c += d; b ^= c; b <<<= 7
    """
    micro = [] if logger else None

    # Step 1: a += b; d ^= a; d <<<= 16
    before_a = state[a]
    state[a] = _add32(state[a], state[b])
    if micro is not None:
        micro.append(_record_add(f"a[{a}]", f"b[{b}]", a, b, before_a, state[b], state[a]))
    before_d = state[d]
    state[d] ^= state[a]
    if micro is not None:
        micro.append(_record_xor(f"d[{d}]", f"a[{a}]", d, a, before_d, state[a], state[d]))
    before_d = state[d]
    state[d] = _rotl32(state[d], 16)
    if micro is not None:
        micro.append(_record_rot(f"d[{d}]", d, before_d, state[d], 16))

    # Step 2: c += d; b ^= c; b <<<= 12
    before_c = state[c]
    state[c] = _add32(state[c], state[d])
    if micro is not None:
        micro.append(_record_add(f"c[{c}]", f"d[{d}]", c, d, before_c, state[d], state[c]))
    before_b = state[b]
    state[b] ^= state[c]
    if micro is not None:
        micro.append(_record_xor(f"b[{b}]", f"c[{c}]", b, c, before_b, state[c], state[b]))
    before_b = state[b]
    state[b] = _rotl32(state[b], 12)
    if micro is not None:
        micro.append(_record_rot(f"b[{b}]", b, before_b, state[b], 12))

    # Step 3: a += b; d ^= a; d <<<= 8
    before_a = state[a]
    state[a] = _add32(state[a], state[b])
    if micro is not None:
        micro.append(_record_add(f"a[{a}]", f"b[{b}]", a, b, before_a, state[b], state[a]))
    before_d = state[d]
    state[d] ^= state[a]
    if micro is not None:
        micro.append(_record_xor(f"d[{d}]", f"a[{a}]", d, a, before_d, state[a], state[d]))
    before_d = state[d]
    state[d] = _rotl32(state[d], 8)
    if micro is not None:
        micro.append(_record_rot(f"d[{d}]", d, before_d, state[d], 8))

    # Step 4: c += d; b ^= c; b <<<= 7
    before_c = state[c]
    state[c] = _add32(state[c], state[d])
    if micro is not None:
        micro.append(_record_add(f"c[{c}]", f"d[{d}]", c, d, before_c, state[d], state[c]))
    before_b = state[b]
    state[b] ^= state[c]
    if micro is not None:
        micro.append(_record_xor(f"b[{b}]", f"c[{c}]", b, c, before_b, state[c], state[b]))
    before_b = state[b]
    state[b] = _rotl32(state[b], 7)
    if micro is not None:
        micro.append(_record_rot(f"b[{b}]", b, before_b, state[b], 7))

    if logger:
        logger.log_quarter_round(round_num, qr_label, state, a, b, c, d, micro)


# ─────────────────────────────────────────────
#  State Initialization
# ─────────────────────────────────────────────

def init_state(key: bytes, counter: int, nonce: bytes) -> list[int]:
    """
    Build the initial 512-bit state matrix.

    Args:
        key:     256-bit (32 bytes) encryption key
        counter: 32-bit block counter
        nonce:   96-bit (12 bytes) number-used-once
    """
    if len(key) != 32:
        raise ValueError(f"Key must be 32 bytes (256 bits), got {len(key)}")
    if len(nonce) != 12:
        raise ValueError(f"Nonce must be 12 bytes (96 bits), got {len(nonce)}")

    state = list(SIGMA)                              # words 0-3:  constants
    state.extend(struct.unpack('<8I', key))           # words 4-11: key
    state.append(counter & 0xFFFFFFFF)                # word  12:   counter
    state.extend(struct.unpack('<3I', nonce))          # words 13-15: nonce
    return state


# ─────────────────────────────────────────────
#  ChaCha20 Block Function
# ─────────────────────────────────────────────

def chacha20_block(
    key: bytes,
    counter: int,
    nonce: bytes,
    enable_logging: bool = False,
) -> tuple[bytes, Optional[list[dict]]]:
    """
    Generate one 64-byte ChaCha20 keystream block.

    Performs 20 rounds (10 column rounds + 10 diagonal rounds)
    then adds the original state to the result.

    Returns:
        (keystream_64_bytes, round_logs_or_None)
    """
    state = init_state(key, counter, nonce)
    initial_state = list(state)

    logger = StepLogger() if enable_logging else None

    if logger:
        logger.log_state(0, "Initial state matrix (before any rounds)", list(state))

    # ── 20 rounds: 10 iterations × 2 (column + diagonal) ──
    for iteration in range(10):

        # ── Column round (odd round number) ──
        col_round = iteration * 2 + 1
        quarter_round(state, 0, 4,  8, 12, logger, col_round, "QR(0, 4, 8, 12)")
        quarter_round(state, 1, 5,  9, 13, logger, col_round, "QR(1, 5, 9, 13)")
        quarter_round(state, 2, 6, 10, 14, logger, col_round, "QR(2, 6, 10, 14)")
        quarter_round(state, 3, 7, 11, 15, logger, col_round, "QR(3, 7, 11, 15)")

        if logger:
            logger.log_state(
                col_round,
                f"Round {col_round} complete — Column rounds",
                list(state),
                round_type="column",
                quarter_rounds_applied=[
                    "QR(0, 4, 8, 12)",
                    "QR(1, 5, 9, 13)",
                    "QR(2, 6, 10, 14)",
                    "QR(3, 7, 11, 15)",
                ],
            )

        # ── Diagonal round (even round number) ──
        diag_round = iteration * 2 + 2
        quarter_round(state, 0, 5, 10, 15, logger, diag_round, "QR(0, 5, 10, 15)")
        quarter_round(state, 1, 6, 11, 12, logger, diag_round, "QR(1, 6, 11, 12)")
        quarter_round(state, 2, 7,  8, 13, logger, diag_round, "QR(2, 7, 8, 13)")
        quarter_round(state, 3, 4,  9, 14, logger, diag_round, "QR(3, 4, 9, 14)")

        if logger:
            logger.log_state(
                diag_round,
                f"Round {diag_round} complete — Diagonal rounds",
                list(state),
                round_type="diagonal",
                quarter_rounds_applied=[
                    "QR(0, 5, 10, 15)",
                    "QR(1, 6, 11, 12)",
                    "QR(2, 7, 8, 13)",
                    "QR(3, 4, 9, 14)",
                ],
            )

    # ── Final addition: working_state + initial_state ──
    output = [_add32(state[i], initial_state[i]) for i in range(16)]

    if logger:
        logger.log_state("final", "Final state (working + initial)", output)

    keystream = struct.pack('<16I', *output)
    return keystream, logger.to_list() if logger else None


# ─────────────────────────────────────────────
#  ChaCha20 Encrypt / Decrypt (Stream XOR)
# ─────────────────────────────────────────────

def chacha20_crypt(
    key: bytes,
    nonce: bytes,
    data: bytes,
    counter: int = 1,
    enable_logging: bool = False,
) -> tuple[bytes, Optional[list[dict]]]:
    """
    Encrypt or decrypt data using ChaCha20.

    ChaCha20 is a stream cipher — the same XOR operation
    is used for both encryption and decryption.

    Args:
        key:            256-bit key (32 bytes)
        nonce:          96-bit nonce (12 bytes)
        data:           plaintext or ciphertext bytes
        counter:        initial block counter (default 1 per RFC 8439)
        enable_logging: if True, return round logs for the first block

    Returns:
        (result_bytes, round_logs_or_None)
    """
    result = bytearray()
    first_block_logs = None

    num_blocks = (len(data) + 63) // 64

    for block_idx in range(num_blocks):
        offset = block_idx * 64
        block = data[offset : offset + 64]

        # Only log the first block to keep the response manageable
        log_this_block = enable_logging and block_idx == 0
        keystream, logs = chacha20_block(
            key, counter + block_idx, nonce, log_this_block
        )

        if logs is not None:
            first_block_logs = logs

        # XOR data with keystream
        for j in range(len(block)):
            result.append(block[j] ^ keystream[j])

    return bytes(result), first_block_logs


# ─────────────────────────────────────────────
#  Key & Nonce Generation
# ─────────────────────────────────────────────

def generate_key() -> bytes:
    """Generate a cryptographically secure random 256-bit (32-byte) key."""
    return os.urandom(32)


def generate_nonce() -> bytes:
    """Generate a cryptographically secure random 96-bit (12-byte) nonce."""
    return os.urandom(12)
