<?php

namespace App\Services;

class AesGcmService
{
    private const CIPHER = 'aes-256-gcm';

    private const IV_LENGTH = 12; // 96-bit recommended for GCM

    private const TAG_LENGTH = 16; // 128-bit recommended for GCM

    private const VERSION = '1';

    private string $key;

    public function __construct()
    {
        $this->key = env('AES_256_GCM_KEY');

        if (empty($this->key)) {
            throw new \RuntimeException('AES_256_GCM_KEY environment variable is missing.');
        }

        if (strlen($this->key) !== 32) {
            throw new \RuntimeException('AES_256_GCM_KEY must be exactly 32 bytes (256-bit).');
        }
    }

    /**
     * Encrypts a plaintext string using AES-256-GCM.
     *
     * @return string Base64 encoded JSON payload
     */
    public function encrypt(string $plaintext): string
    {
        $iv = openssl_random_pseudo_bytes(self::IV_LENGTH);
        $tag = '';

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if ($ciphertext === false) {
            throw new \RuntimeException('Encryption failed.');
        }

        $payload = [
            'v' => self::VERSION,
            'iv' => base64_encode($iv),
            'c' => base64_encode($ciphertext),
            't' => base64_encode($tag),
        ];

        return base64_encode(json_encode($payload));
    }

    /**
     * Decrypts an encrypted payload using AES-256-GCM.
     *
     * @param  string  $payload  Base64 encoded JSON payload
     * @return string Plaintext
     */
    public function decrypt(string $payload): string
    {
        $decodedPayload = base64_decode($payload, true);
        if ($decodedPayload === false) {
            throw new \RuntimeException('Invalid base64 encoding.');
        }

        $data = json_decode($decodedPayload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid payload format.');
        }

        if (! isset($data['v'], $data['iv'], $data['c'], $data['t'])) {
            throw new \RuntimeException('Missing payload components.');
        }

        if ($data['v'] !== self::VERSION) {
            throw new \RuntimeException('Unsupported payload version.');
        }

        $iv = base64_decode($data['iv'], true);
        $ciphertext = base64_decode($data['c'], true);
        $tag = base64_decode($data['t'], true);

        if ($iv === false || $ciphertext === false || $tag === false) {
            throw new \RuntimeException('Invalid base64 encoding in components.');
        }

        if (strlen($iv) !== self::IV_LENGTH) {
            throw new \RuntimeException('Invalid IV length.');
        }

        if (strlen($tag) !== self::TAG_LENGTH) {
            throw new \RuntimeException('Invalid tag length.');
        }

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plaintext === false) {
            throw new \RuntimeException('Decryption or authentication failed.');
        }

        return $plaintext;
    }

    /**
     * Calculate Avalanche Effect percentage between two plaintexts
     */
    public function calculateAvalanche(string $plaintextA, string $plaintextB): array
    {
        $iv = openssl_random_pseudo_bytes(self::IV_LENGTH);

        $tagA = '';
        $cipherA = openssl_encrypt($plaintextA, self::CIPHER, $this->key, OPENSSL_RAW_DATA, $iv, $tagA, '', self::TAG_LENGTH);
        $payloadA = base64_encode($cipherA.$tagA); // Just the raw output bits for comparison

        $tagB = '';
        $cipherB = openssl_encrypt($plaintextB, self::CIPHER, $this->key, OPENSSL_RAW_DATA, $iv, $tagB, '', self::TAG_LENGTH);
        $payloadB = base64_encode($cipherB.$tagB);

        // Convert base64 to binary strings
        $binA = $this->strToBin(base64_decode($payloadA));
        $binB = $this->strToBin(base64_decode($payloadB));

        // Pad shorter string with 0s if they differ in length
        $lenA = strlen($binA);
        $lenB = strlen($binB);
        $maxLen = max($lenA, $lenB);

        $binA = str_pad($binA, $maxLen, '0', STR_PAD_RIGHT);
        $binB = str_pad($binB, $maxLen, '0', STR_PAD_RIGHT);

        $changedBits = 0;
        for ($i = 0; $i < $maxLen; $i++) {
            if ($binA[$i] !== $binB[$i]) {
                $changedBits++;
            }
        }

        $percentage = $maxLen > 0 ? ($changedBits / $maxLen) * 100 : 0;

        return [
            'total_bits' => $maxLen,
            'changed_bits' => $changedBits,
            'percentage' => round($percentage, 2),
            'binary_a' => $binA,
            'binary_b' => $binB,
        ];
    }

    private function strToBin(string $str): string
    {
        $bin = '';
        foreach (str_split($str) as $char) {
            $bin .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        return $bin;
    }
}
