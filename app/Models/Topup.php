<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topup extends Model
{
    protected $fillable = ['user_id', 'amount', 'fee', 'total', 'merchant_order_id', 'reference', 'payment_url', 'status', 'payment_code', 'paid_at'];

    protected $casts = ['paid_at' => 'datetime'];

    public const STATUSES = ['pending' => 'Menunggu pembayaran', 'paid' => 'Lunas', 'failed' => 'Gagal'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Biaya = flat + persen dari nominal (dibulatkan ke atas). */
    public static function feeFor(int $amount): int
    {
        return (int) config('duitku.fee_flat') + (int) ceil($amount * (float) config('duitku.fee_percent') / 100);
    }
}
