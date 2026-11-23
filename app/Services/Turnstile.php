<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cloudflare Turnstile: memastikan pengirim formulir adalah browser manusia.
 * Token dari widget wajib diperiksa di server (siteverify); widget saja tidak melindungi apa pun.
 * Token berlaku 5 menit dan sekali pakai.
 */
class Turnstile
{
    public const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /** Aktif hanya bila saklar menyala dan kedua kunci terisi. Tanpa kunci (uji lokal) dilewati. */
    public static function enabled(): bool
    {
        return (bool) config('security.turnstile.enabled')
            && filled(config('services.turnstile.site_key'))
            && filled(config('services.turnstile.secret_key'));
    }

    public static function siteKey(): ?string
    {
        return config('services.turnstile.site_key');
    }

    public function verify(mixed $token, ?string $ip = null): bool
    {
        if (! self::enabled()) {
            return true;
        }

        if (! is_string($token) || $token === '' || strlen($token) > 2048) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(5)->post(self::VERIFY_URL, array_filter([
                'secret' => config('services.turnstile.secret_key'),
                'response' => $token,
                'remoteip' => $ip,
            ]));
        } catch (\Throwable $e) {
            // Gagal menghubungi Cloudflare: tolak (fail closed), jangan loloskan bot.
            Log::warning('Turnstile: gagal menghubungi Cloudflare: '.$e->getMessage());

            return false;
        }

        if (! $response->successful()) {
            Log::warning('Turnstile: balasan tidak normal dari Cloudflare', ['status' => $response->status()]);

            return false;
        }

        if (! $response->json('success')) {
            Log::info('Turnstile menolak token', ['error_codes' => $response->json('error-codes')]);

            return false;
        }

        return true;
    }
}
