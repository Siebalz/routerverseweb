<?php

namespace App\Http\Controllers;

use App\Models\Topup;
use App\Services\DuitkuPop;
use App\Services\TopupSettler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Callback Duitku (POST, tanpa login, tanpa CSRF). Satu-satunya pintu poin top up bertambah.
 * Aman dipanggil berulang: top up yang sudah lunas tidak diproses lagi.
 */
class DuitkuCallbackController extends Controller
{
    public function __invoke(Request $request, DuitkuPop $duitku, TopupSettler $settler)
    {
        $p = $request->all();
        // Jejak untuk diagnosa (signature tidak dicatat).
        Log::info('Duitku callback masuk', ['ip' => $request->ip()] + array_diff_key($p, ['signature' => 1]));

        if (! $duitku->verifyCallback($p)) {
            Log::warning('Duitku callback: signature/parameter tidak valid', ['ip' => $request->ip(), 'order' => $p['merchantOrderId'] ?? null]);

            return response('Bad Signature', 400);
        }

        $topup = Topup::where('merchant_order_id', $p['merchantOrderId'])->first();
        if (! $topup) {
            Log::warning('Duitku callback: order tidak dikenal', ['order' => $p['merchantOrderId']]);

            return response('Unknown Order', 404);
        }

        // Jumlah dari Duitku harus sama dengan yang kita tagihkan.
        if ((int) round((float) $p['amount']) !== (int) $topup->total) {
            Log::error('Duitku callback: jumlah tidak cocok', ['order' => $topup->merchant_order_id, 'diterima' => $p['amount'], 'seharusnya' => $topup->total]);

            return response('Bad Amount', 400);
        }

        $result = (string) ($p['resultCode'] ?? '');

        $outcome = $settler->settle($topup, (string) ($p['resultCode'] ?? ''), isset($p['paymentCode']) ? (string) $p['paymentCode'] : null);
        Log::info('Duitku callback diproses', ['order' => $topup->merchant_order_id, 'hasil' => $outcome]);

        return response('OK', 200);
    }
}
