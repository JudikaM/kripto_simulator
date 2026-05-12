"""
Caesar Cipher — Pure Python Implementation

A classic substitution cipher where each letter is shifted by a fixed number
of positions in the alphabet.

Features:
    - Encrypt / Decrypt with shift key (0-25)
    - Preserves case (uppercase/lowercase)
    - Non-alphabetic characters pass through unchanged
    - Step-by-step logging for educational visualization
"""

from typing import Optional


# ─────────────────────────────────────────────
#  Constants
# ─────────────────────────────────────────────

ALPHABET_UPPER = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"
ALPHABET_LOWER = "abcdefghijklmnopqrstuvwxyz"


# ─────────────────────────────────────────────
#  Step Logger
# ─────────────────────────────────────────────

class CaesarStepLogger:
    """
    Records each character substitution for educational visualization.
    """

    def __init__(self):
        self.entries: list[dict] = []

    def log_step(
        self,
        index: int,
        original_char: str,
        shifted_char: str,
        original_pos: int,
        shifted_pos: int,
        shift: int,
        is_letter: bool,
        is_upper: bool,
    ):
        self.entries.append({
            "index": index,
            "original_char": original_char,
            "shifted_char": shifted_char,
            "original_position": original_pos,
            "shifted_position": shifted_pos,
            "shift": shift,
            "is_letter": is_letter,
            "is_upper": is_upper,
            "formula": (
                f"({original_pos} + {shift}) mod 26 = {shifted_pos}"
                if is_letter
                else "non-alphabetic — unchanged"
            ),
        })

    def to_list(self) -> list[dict]:
        return self.entries


# ─────────────────────────────────────────────
#  Caesar Cipher Core
# ─────────────────────────────────────────────

def caesar_encrypt_char(char: str, shift: int) -> tuple[str, int, int, bool, bool]:
    """
    Encrypt a single character with Caesar shift.

    Returns:
        (result_char, original_pos, shifted_pos, is_letter, is_upper)
    """
    if char.isupper():
        original_pos = ord(char) - ord('A')
        shifted_pos = (original_pos + shift) % 26
        return chr(shifted_pos + ord('A')), original_pos, shifted_pos, True, True
    elif char.islower():
        original_pos = ord(char) - ord('a')
        shifted_pos = (original_pos + shift) % 26
        return chr(shifted_pos + ord('a')), original_pos, shifted_pos, True, False
    else:
        return char, -1, -1, False, False


def caesar_decrypt_char(char: str, shift: int) -> tuple[str, int, int, bool, bool]:
    """
    Decrypt a single character with Caesar shift (reverse direction).
    """
    return caesar_encrypt_char(char, -shift)


def caesar_encrypt(
    plaintext: str,
    shift: int,
    enable_logging: bool = False,
) -> tuple[str, Optional[list[dict]]]:
    """
    Encrypt plaintext using Caesar cipher.

    Args:
        plaintext:      Text to encrypt
        shift:          Number of positions to shift (0-25)
        enable_logging: If True, return step-by-step logs

    Returns:
        (ciphertext, step_logs_or_None)
    """
    shift = shift % 26  # Normalize shift
    logger = CaesarStepLogger() if enable_logging else None
    result = []

    for i, char in enumerate(plaintext):
        encrypted, orig_pos, shift_pos, is_letter, is_upper = caesar_encrypt_char(char, shift)
        result.append(encrypted)

        if logger:
            logger.log_step(i, char, encrypted, orig_pos, shift_pos, shift, is_letter, is_upper)

    ciphertext = ''.join(result)
    return ciphertext, logger.to_list() if logger else None


def caesar_decrypt(
    ciphertext: str,
    shift: int,
    enable_logging: bool = False,
) -> tuple[str, Optional[list[dict]]]:
    """
    Decrypt ciphertext using Caesar cipher.

    Args:
        ciphertext:     Text to decrypt
        shift:          Number of positions that was used for encryption (0-25)
        enable_logging: If True, return step-by-step logs

    Returns:
        (plaintext, step_logs_or_None)
    """
    shift = shift % 26
    logger = CaesarStepLogger() if enable_logging else None
    result = []

    for i, char in enumerate(ciphertext):
        decrypted, orig_pos, shift_pos, is_letter, is_upper = caesar_decrypt_char(char, shift)
        result.append(decrypted)

        if logger:
            logger.log_step(i, char, decrypted, orig_pos, shift_pos, shift, is_letter, is_upper)

    plaintext = ''.join(result)
    return plaintext, logger.to_list() if logger else None


def caesar_brute_force(ciphertext: str) -> list[dict]:
    """
    Try all 26 possible shifts and return results.
    Useful for educational "cracking" demonstration.

    Returns:
        List of {shift, plaintext} for shifts 0-25
    """
    results = []
    for shift in range(26):
        decrypted, _ = caesar_decrypt(ciphertext, shift)
        results.append({
            "shift": shift,
            "plaintext": decrypted,
        })
    return results


# ─────────────────────────────────────────────
#  Alphabet Table Generator
# ─────────────────────────────────────────────

def generate_shift_table(shift: int) -> dict:
    """
    Generate the shifted alphabet mapping for visualization.

    Returns:
        {
            "shift": 3,
            "original":  "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
            "shifted":   "DEFGHIJKLMNOPQRSTUVWXYZABC",
            "mapping": [{"from": "A", "to": "D", "position": 0}, ...]
        }
    """
    shift = shift % 26
    shifted = ALPHABET_UPPER[shift:] + ALPHABET_UPPER[:shift]
    mapping = [
        {"from": ALPHABET_UPPER[i], "to": shifted[i], "position": i}
        for i in range(26)
    ]
    return {
        "shift": shift,
        "original": ALPHABET_UPPER,
        "shifted": shifted,
        "mapping": mapping,
    }
