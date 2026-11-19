<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointLedger extends Model
{
    protected $fillable = ['user_id', 'type', 'amount', 'balance_after', 'order_id', 'topup_id', 'note'];

    public const TYPES = ['topup' => 'Top up', 'purchase' => 'Pembayaran', 'refund' => 'Pengembalian', 'adjust' => 'Penyesuaian admin'];

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
