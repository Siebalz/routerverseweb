<?php

namespace App\Services;

use App\Mail\LoginOtpMail;
use App\Models\LoginOtp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Kode OTP 6 digit untuk verifikasi kedua saat login.
 * Yang disimpan hanya hash (HMAC dengan APP_KEY). Kode berlaku singkat, percobaannya dibatasi,
 * dan setiap kode hanya bisa dipakai sekali.
 */
class LoginOtpService
{
    public const OK = 'ok';
    public const INVALID = 'invalid';
    public const EXPIRED = 'expired';
    public const LOCKED = 'locked';
    public const NONE = 'none';

    public static function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function hash(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    public function ttlMinutes(): int
    {
        return max(1, (int) config('security.otp.ttl_minutes', 10));
    }

    public function maxAttempts(): int
    {
        return max(1, (int) config('security.otp.max_attempts', 5));
    }

    public function resendSeconds(): int
    {
        return max(0, (int) config('security.otp.resend_seconds', 60));
    }

    /** Buat kode baru (menggantikan yang lama) dan kirim ke email pengguna. */
    public function issue(User $user): void
    {
        $code = self::generateCode();

        $otp = LoginOtp::updateOrCreate(
            ['user_id' => $user->id],
            [
                'code_hash' => self::hash($code),
                'expires_at' => now()->addMinutes($this->ttlMinutes()),
                'attempts' => 0,
                'sent_at' => now(),
            ]
        );

        try {
            Mail::to($user->email)->send(new LoginOtpMail($user, $code, $this->ttlMinutes()));
        } catch (\Throwable $e) {
            // Kode yang tidak sampai ke pengguna tidak ada gunanya: hapus supaya tidak menahan permintaan berikutnya.
            $otp->delete();
            Log::error('Gagal mengirim kode OTP login: '.$e->getMessage(), ['user_id' => $user->id]);
            throw $e;
        }
    }

    /** Sisa detik sebelum kode baru boleh diminta (0 = boleh sekarang). */
    public function secondsUntilResend(User $user): int
    {
        $otp = LoginOtp::where('user_id', $user->id)->first();
        if (! $otp) {
            return 0;
        }

        return max(0, $this->resendSeconds() - (now()->timestamp - $otp->sent_at->timestamp));
    }

    /** Sisa percobaan untuk kode yang sedang berlaku. */
    public function attemptsLeft(User $user): int
    {
        $otp = LoginOtp::where('user_id', $user->id)->first();

        return $otp ? max(0, $this->maxAttempts() - (int) $otp->attempts) : 0;
    }

    /** Periksa kode yang dimasukkan. Mengembalikan salah satu konstanta hasil di atas. */
    public function verify(User $user, string $code): string
    {
        return DB::transaction(function () use ($user, $code) {
            $otp = LoginOtp::where('user_id', $user->id)->lockForUpdate()->first();

            if (! $otp) {
                return self::NONE;
            }
            if ($otp->expires_at->isPast()) {
                $otp->delete();

                return self::EXPIRED;
            }
            if ((int) $otp->attempts >= $this->maxAttempts()) {
                $otp->delete();

                return self::LOCKED;
            }

            if (! hash_equals($otp->code_hash, self::hash($code))) {
                $otp->increment('attempts');
                if ((int) $otp->attempts >= $this->maxAttempts()) {
                    $otp->delete();

                    return self::LOCKED;
                }

                return self::INVALID;
            }

            $otp->delete(); // sekali pakai

            return self::OK;
        });
    }

    /** Hapus kode yang masih tersimpan (dipakai saat OTP dimatikan atau pengguna keluar dari alur). */
    public function forget(User $user): void
    {
        LoginOtp::where('user_id', $user->id)->delete();
    }
}
