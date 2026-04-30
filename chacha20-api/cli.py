"""
ChaCha20 CLI — Terminal Interface for ChaCha20 Stream Cipher

Usage:
    python cli.py                     → Interactive mode
    python cli.py encrypt "pesan"     → Quick encrypt
    python cli.py decrypt HEX KEY NONCE → Quick decrypt
    python cli.py keygen              → Generate key + nonce

No server needed — runs chacha20.py directly.
"""

import sys
import os

from chacha20 import chacha20_crypt, generate_key, generate_nonce

# ── Colors ──
G = "\033[92m"   # green
D = "\033[90m"   # dim
R = "\033[91m"   # red
Y = "\033[93m"   # yellow
C = "\033[96m"   # cyan
B = "\033[1m"    # bold
N = "\033[0m"    # reset


def banner():
    print(f"""
{G}╔══════════════════════════════════════════════════╗
║  {B}ChaCha20 Stream Cipher CLI{N}{G}                       ║
║  {D}RFC 8439 · Pure Python · No External Libraries{N}{G}   ║
║  {D}NakamotoX Crypto Simulator{N}{G}                       ║
╚══════════════════════════════════════════════════╝{N}
""")


def print_result(label, value, color=G):
    print(f"  {D}{label}:{N} {color}{value}{N}")


def do_keygen():
    key = generate_key()
    nonce = generate_nonce()
    print(f"\n{G}[KEYGEN]{N} Generated 256-bit key + 96-bit nonce:\n")
    print_result("KEY   (hex)", key.hex())
    print_result("KEY   (len)", f"{len(key)*8} bits")
    print_result("NONCE (hex)", nonce.hex())
    print_result("NONCE (len)", f"{len(nonce)*8} bits")
    print()
    return key, nonce


def do_encrypt(plaintext, key=None, nonce=None):
    if key is None:
        key = generate_key()
    if nonce is None:
        nonce = generate_nonce()

    plaintext_bytes = plaintext.encode("utf-8")
    ciphertext, _ = chacha20_crypt(key, nonce, plaintext_bytes)

    print(f"\n{G}[ENCRYPT]{N} Encryption complete:\n")
    print_result("PLAINTEXT    ", plaintext)
    print_result("CIPHERTEXT   ", ciphertext.hex(), C)
    print_result("KEY          ", key.hex(), Y)
    print_result("NONCE        ", nonce.hex(), Y)
    print_result("INPUT SIZE   ", f"{len(plaintext_bytes)} bytes")
    print_result("OUTPUT SIZE  ", f"{len(ciphertext)} bytes")
    print()

    return ciphertext, key, nonce


def do_decrypt(ciphertext_hex, key_hex, nonce_hex):
    key = bytes.fromhex(key_hex)
    nonce = bytes.fromhex(nonce_hex)
    ciphertext = bytes.fromhex(ciphertext_hex)

    plaintext_bytes, _ = chacha20_crypt(key, nonce, ciphertext)

    try:
        plaintext_str = plaintext_bytes.decode("utf-8")
    except UnicodeDecodeError:
        plaintext_str = plaintext_bytes.decode("latin-1")

    print(f"\n{G}[DECRYPT]{N} Decryption complete:\n")
    print_result("CIPHERTEXT   ", ciphertext_hex)
    print_result("PLAINTEXT    ", plaintext_str, C)
    print_result("PLAINTEXT HEX", plaintext_bytes.hex())
    print()

    return plaintext_str


def interactive():
    banner()

    while True:
        print(f"{G}┌─[{N}{B}nakamotox{N}{G}]─[{N}~/chacha20{G}]{N}")
        print(f"{G}└──▶{N} ", end="")

        try:
            choice = input(f"{D}[1] Encrypt  [2] Decrypt  [3] Keygen  [0] Exit → {N}").strip()
        except (EOFError, KeyboardInterrupt):
            print(f"\n{D}Bye!{N}")
            break

        if choice == "0" or choice.lower() in ("exit", "quit", "q"):
            print(f"\n{D}Bye!{N}")
            break

        elif choice == "1":
            plaintext = input(f"  {G}>{N} Plaintext: ").strip()
            if not plaintext:
                print(f"  {R}Error: Plaintext tidak boleh kosong{N}\n")
                continue

            use_custom = input(f"  {G}>{N} Custom key? (y/N): ").strip().lower()
            if use_custom == "y":
                key_hex = input(f"  {G}>{N} Key (64 hex): ").strip()
                nonce_hex = input(f"  {G}>{N} Nonce (24 hex): ").strip()
                try:
                    key = bytes.fromhex(key_hex)
                    nonce = bytes.fromhex(nonce_hex)
                    if len(key) != 32:
                        print(f"  {R}Error: Key harus 32 bytes (64 hex chars){N}\n")
                        continue
                    if len(nonce) != 12:
                        print(f"  {R}Error: Nonce harus 12 bytes (24 hex chars){N}\n")
                        continue
                except ValueError:
                    print(f"  {R}Error: Format hex tidak valid{N}\n")
                    continue
                do_encrypt(plaintext, key, nonce)
            else:
                do_encrypt(plaintext)

        elif choice == "2":
            ciphertext_hex = input(f"  {G}>{N} Ciphertext (hex): ").strip()
            key_hex = input(f"  {G}>{N} Key (64 hex): ").strip()
            nonce_hex = input(f"  {G}>{N} Nonce (24 hex): ").strip()

            try:
                do_decrypt(ciphertext_hex, key_hex, nonce_hex)
            except ValueError as e:
                print(f"  {R}Error: {e}{N}\n")

        elif choice == "3":
            do_keygen()

        else:
            print(f"  {R}Pilihan tidak valid{N}\n")


def main():
    # ── Non-interactive (command line args) ──
    if len(sys.argv) > 1:
        cmd = sys.argv[1].lower()

        if cmd == "keygen":
            do_keygen()

        elif cmd == "encrypt":
            if len(sys.argv) < 3:
                print(f"{R}Usage: python cli.py encrypt \"plaintext\" [key_hex] [nonce_hex]{N}")
                sys.exit(1)
            plaintext = sys.argv[2]
            key = bytes.fromhex(sys.argv[3]) if len(sys.argv) > 3 else None
            nonce = bytes.fromhex(sys.argv[4]) if len(sys.argv) > 4 else None
            do_encrypt(plaintext, key, nonce)

        elif cmd == "decrypt":
            if len(sys.argv) < 5:
                print(f"{R}Usage: python cli.py decrypt CIPHERTEXT_HEX KEY_HEX NONCE_HEX{N}")
                sys.exit(1)
            do_decrypt(sys.argv[2], sys.argv[3], sys.argv[4])

        else:
            print(f"{R}Unknown command: {cmd}{N}")
            print(f"Usage: python cli.py [encrypt|decrypt|keygen]")
            sys.exit(1)
    else:
        # ── Interactive mode ──
        interactive()


if __name__ == "__main__":
    # Enable ANSI colors on Windows
    if sys.platform == "win32":
        os.system("")
    main()
