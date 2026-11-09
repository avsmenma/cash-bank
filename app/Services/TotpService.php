<?php

namespace App\Services;

class TotpService
{
    private static string $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate secret key Base32 acak (16 karakter).
     */
    public static function generateSecret(int $length = 16): string
    {
        $secret = '';
        $max = strlen(self::$base32Chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::$base32Chars[random_int(0, $max)];
        }
        return $secret;
    }

    /**
     * Hitung kode TOTP 6 digit untuk timestamp tertentu.
     */
    public static function getCode(string $secret, ?int $timestamp = null): string
    {
        if ($timestamp === null) {
            $timestamp = time();
        }

        $timeSlice = (int) floor($timestamp / 30);
        $binarySecret = self::base32Decode($secret);

        // Pack time slice into 8-byte big-endian binary
        $binaryTime = pack('N*', 0) . pack('N*', $timeSlice);

        // Calculate HMAC-SHA1
        $hash = hash_hmac('sha1', $binaryTime, $binarySecret, true);

        // Dynamic truncation (RFC 4226)
        $offset = ord(substr($hash, -1)) & 0x0F;
        $binary = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        );

        $otp = $binary % 1000000;
        return str_pad((string) $otp, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verifikasi kode 6 digit dari user dengan toleransi window drift 1 langkah (±30 detik).
     */
    public static function verifyCode(string $secret, string $code, int $discrepancy = 1): bool
    {
        $code = trim($code);
        if (strlen($code) !== 6 || !ctype_digit($code)) {
            return false;
        }

        $currentTime = time();
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculated = self::getCode($secret, $currentTime + ($i * 30));
            if (hash_equals($calculated, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Buat URL otpauth:// standar untuk Google Authenticator / Microsoft Authenticator.
     */
    public static function getOtpAuthUrl(string $username, string $secret, string $issuer = 'CashBank'): string
    {
        $encodedIssuer = rawurlencode($issuer);
        $encodedUsername = rawurlencode($username);
        return "otpauth://totp/{$encodedIssuer}:{$encodedUsername}?secret={$secret}&issuer={$encodedIssuer}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * URL gambar QR Code untuk dipindai oleh aplikasi Authenticator.
     */
    public static function getQrCodeUrl(string $username, string $secret, string $issuer = 'CashBank'): string
    {
        $otpAuthUrl = self::getOtpAuthUrl($username, $secret, $issuer);
        return 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . rawurlencode($otpAuthUrl);
    }

    /**
     * Decode string Base32 menjadi raw binary.
     */
    private static function base32Decode(string $base32): string
    {
        $base32 = strtoupper(trim($base32));
        $buffer = 0;
        $bitsLeft = 0;
        $binary = '';

        for ($i = 0; $i < strlen($base32); $i++) {
            $char = $base32[$i];
            $val = strpos(self::$base32Chars, $char);
            if ($val === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $binary .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $binary;
    }
}
