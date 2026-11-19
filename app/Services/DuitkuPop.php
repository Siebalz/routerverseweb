<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Klien Duitku POP sesuai https://docs.duitku.com/pop/id
 *  - createInvoice: POST {api}/api/merchant/createInvoice
 *      header x-duitku-signature = sha256(merchantCode + timestamp(ms) + apiKey), x-duitku-timestamp, x-duitku-merchantcode
 *  - callback: POST dari Duitku, signature = md5(merchantCode + amount + merchantOrderId + apiKey)
 * Dokumen tidak menyediakan endpoint cek status, jadi status hanya dari callback.
 */
class DuitkuPop
{
    public const API = ['sandbox' => 'https://api-sandbox.duitku.com', 'production' => 'https://api-prod.duitku.com'];

    /** @var callable(string, array, array): array  fn($url, $headers, $body) => ['status'=>int,'json'=>array] */
    protected $transport;

    public function __construct(protected string $merchantCode, protected string $apiKey, protected string $env = 'sandbox', ?callable $transport = null)
    {
        $this->transport = $transport ?? function (string $url, array $headers, array $body): array {
            $r = Http::withHeaders($headers)->acceptJson()->asJson()->timeout(20)->post($url, $body);

            return ['status' => $r->status(), 'json' => (array) $r->json()];
        };
    }

    public static function make(): self
    {
        return new self((string) config('duitku.merchant_code'), (string) config('duitku.api_key'), (string) config('duitku.env'));
    }

    /**
     * Alamat callback yang dikirim ke Duitku. Selalu dari APP_URL (bukan dari alamat yang sedang dibuka di browser),
     * jadi saat uji lokal cukup isi APP_URL dengan alamat tunnel publik, sementara Anda tetap membuka web di localhost.
     */
    public static function callbackUrl(): string
    {
        return rtrim((string) config('app.url'), '/').route('duitku.callback', [], false);
    }

    public function configured(): bool
    {
        return $this->merchantCode !== '' && $this->apiKey !== '';
    }

    public function createSignature(int $timestampMs): string
    {
        return hash('sha256', $this->merchantCode.$timestampMs.$this->apiKey);
    }

    /**
     * Buat tagihan POP.
     *
     * @return array{reference:string,paymentUrl:string}
     *
     * @throws \RuntimeException bila Duitku menolak atau respons tidak lengkap
     */
    public function createInvoice(array $body, ?int $timestampMs = null): array
    {
        if (! $this->configured()) {
            throw new \RuntimeException('Duitku belum dikonfigurasi (DUITKU_MERCHANT_CODE / DUITKU_API_KEY di .env).');
        }
        $ts = $timestampMs ?? (int) round(microtime(true) * 1000);
        $headers = [
            'x-duitku-signature' => $this->createSignature($ts),
            'x-duitku-timestamp' => (string) $ts,
            'x-duitku-merchantcode' => $this->merchantCode,
            'Content-Type' => 'application/json',
        ];
        $url = (self::API[$this->env] ?? self::API['sandbox']).'/api/merchant/createInvoice';

        $res = ($this->transport)($url, $headers, $body);
        $j = $res['json'] ?? [];
        if (($res['status'] ?? 0) !== 200 || ($j['statusCode'] ?? '') !== '00' || empty($j['reference']) || empty($j['paymentUrl'])) {
            throw new \RuntimeException('Duitku menolak transaksi (HTTP '.($res['status'] ?? '?').'): '.($j['statusMessage'] ?? $j['Message'] ?? 'respons tidak dikenal'));
        }

        return ['reference' => $j['reference'], 'paymentUrl' => $j['paymentUrl']];
    }

    /** Callback sah bila field wajib ada, merchantCode cocok, dan signature = md5(merchantCode+amount+merchantOrderId+apiKey). */
    public function verifyCallback(array $p): bool
    {
        foreach (['merchantCode', 'amount', 'merchantOrderId', 'signature'] as $k) {
            if (! isset($p[$k]) || ! is_scalar($p[$k]) || $p[$k] === '') {
                return false;
            }
        }
        if (! $this->configured() || ! hash_equals($this->merchantCode, (string) $p['merchantCode'])) {
            return false;
        }
        $calc = md5($p['merchantCode'].$p['amount'].$p['merchantOrderId'].$this->apiKey);

        return hash_equals($calc, strtolower((string) $p['signature']));
    }
}
