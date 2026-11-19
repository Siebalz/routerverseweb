<?php

namespace App\Services;

use App\Models\Topup;
use Illuminate\Support\Facades\DB;

/** Menyelesaikan top up: satu-satunya tempat poin top up ditambahkan. Idempoten. */
class TopupSettler
{
    public function __construct(protected PointService $points)
    {
    }

    /**
     * @param  string  $resultCode  '00' lunas, '02' gagal (kode callback Duitku)
     * @return string 'credited' | 'already' | 'failed' | 'ignored'
     */
    public function settle(Topup $topup, string $resultCode, ?string $paymentCode = null, string $source = 'Duitku'): string
    {
        return DB::transaction(function () use ($topup, $resultCode, $paymentCode, $source) {
            $t = Topup::whereKey($topup->id)->lockForUpdate()->first();
            if ($t->status === 'paid') {
                return 'already'; // callback ganda
            }
            if ($resultCode === '00') {
                $ledger = $this->points->credit($t->user_id, (int) $t->amount, 'topup', 'Top up via '.$source.' '.$t->merchant_order_id);
                $ledger->forceFill(['topup_id' => $t->id])->save();
                $t->update(['status' => 'paid', 'paid_at' => now(), 'payment_code' => $paymentCode ? substr($paymentCode, 0, 10) : null]);

                return 'credited';
            }
            if ($resultCode === '02') {
                $t->update(['status' => 'failed']);

                return 'failed';
            }

            return 'ignored';
        });
    }
}
