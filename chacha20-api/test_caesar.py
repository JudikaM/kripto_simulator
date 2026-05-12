"""
Test suite for the Caesar Cipher implementation.
"""

from caesar import (
    caesar_encrypt,
    caesar_decrypt,
    caesar_brute_force,
    generate_shift_table,
    caesar_encrypt_char,
    caesar_decrypt_char,
)


def test_basic_encrypt():
    """Test basic encryption with shift=3 (classic Caesar)."""
    ciphertext, _ = caesar_encrypt("HELLO", 3)
    assert ciphertext == "KHOOR", f"Expected KHOOR, got {ciphertext}"
    print("✅ Basic encrypt test passed (HELLO → KHOOR)")


def test_basic_decrypt():
    """Test basic decryption with shift=3."""
    plaintext, _ = caesar_decrypt("KHOOR", 3)
    assert plaintext == "HELLO", f"Expected HELLO, got {plaintext}"
    print("✅ Basic decrypt test passed (KHOOR → HELLO)")


def test_mixed_case():
    """Test that case is preserved."""
    ciphertext, _ = caesar_encrypt("Hello, World!", 3)
    assert ciphertext == "Khoor, Zruog!", f"Expected 'Khoor, Zruog!', got '{ciphertext}'"
    print("✅ Mixed case test passed (Hello, World! → Khoor, Zruog!)")


def test_non_alpha_preserved():
    """Test that non-alphabetic characters are unchanged."""
    ciphertext, _ = caesar_encrypt("123!@#", 5)
    assert ciphertext == "123!@#", f"Expected '123!@#', got '{ciphertext}'"
    print("✅ Non-alpha preservation test passed")


def test_roundtrip():
    """Test encrypt → decrypt roundtrip for all shifts."""
    original = "The Quick Brown Fox Jumps Over The Lazy Dog! 123"
    for shift in range(26):
        encrypted, _ = caesar_encrypt(original, shift)
        decrypted, _ = caesar_decrypt(encrypted, shift)
        assert decrypted == original, f"Roundtrip failed for shift={shift}"
    print("✅ Roundtrip test passed (all 26 shifts)")


def test_shift_wrap():
    """Test shift wrapping at alphabet boundary."""
    # Z + 1 should be A
    ciphertext, _ = caesar_encrypt("Z", 1)
    assert ciphertext == "A", f"Expected A, got {ciphertext}"
    # z + 1 should be a
    ciphertext, _ = caesar_encrypt("z", 1)
    assert ciphertext == "a", f"Expected a, got {ciphertext}"
    print("✅ Shift wrap test passed (Z+1=A, z+1=a)")


def test_shift_zero():
    """Shift=0 should return identical text."""
    original = "Hello"
    ciphertext, _ = caesar_encrypt(original, 0)
    assert ciphertext == original
    print("✅ Shift zero test passed")


def test_brute_force():
    """Test brute force returns all 26 results including original."""
    original = "Hello"
    encrypted, _ = caesar_encrypt(original, 7)
    results = caesar_brute_force(encrypted)
    assert len(results) == 26
    plaintexts = [r["plaintext"] for r in results]
    assert original in plaintexts, f"Original '{original}' not found in brute force results"
    print("✅ Brute force test passed (26 results, original found)")


def test_shift_table():
    """Test shift table generation."""
    table = generate_shift_table(3)
    assert table["shift"] == 3
    assert table["original"] == "ABCDEFGHIJKLMNOPQRSTUVWXYZ"
    assert table["shifted"] == "DEFGHIJKLMNOPQRSTUVWXYZABC"
    assert len(table["mapping"]) == 26
    assert table["mapping"][0]["from"] == "A"
    assert table["mapping"][0]["to"] == "D"
    print("✅ Shift table test passed")


def test_step_logger():
    """Test that step logger records all characters."""
    _, logs = caesar_encrypt("Hi!", 3, enable_logging=True)
    assert logs is not None
    assert len(logs) == 3  # H, i, !
    assert logs[0]["is_letter"] is True
    assert logs[0]["original_char"] == "H"
    assert logs[0]["shifted_char"] == "K"
    assert logs[2]["is_letter"] is False  # !
    print(f"✅ Step logger test passed ({len(logs)} entries)")


if __name__ == "__main__":
    print("=" * 50)
    print("  Caesar Cipher Test Suite")
    print("=" * 50)
    print()

    test_basic_encrypt()
    test_basic_decrypt()
    test_mixed_case()
    test_non_alpha_preserved()
    test_roundtrip()
    test_shift_wrap()
    test_shift_zero()
    test_brute_force()
    test_shift_table()
    test_step_logger()

    print()
    print("=" * 50)
    print("  All tests passed! ✅")
    print("=" * 50)
