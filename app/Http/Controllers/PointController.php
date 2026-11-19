<?php

namespace App\Http\Controllers;

use App\Models\Topup;
use App\Services\DuitkuPop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PointController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('points.index', [
            'balance' => (int) $user->points,
            'ledgers' => $user->pointLedgers()->latest('id')->paginate(15),
            'topups' => $user->topups()->latest('id')->take(5)->get(),
            'minTopup' => (int) config('duitku.min_topup'),
            'maxTopup' => (int) config('duitku.max_topup'),
            'feeFlat' => (int) config('duitku.fee_flat'),
            'feePercent' => (float) config('duitku.fee_percent'),
            'gatewayReady' => DuitkuPop::make()->configured(),
            'returned' => request()->boolean('kembali'),
        ]);
    }

    /** Buat tagihan Duitku POP lalu arahkan pelanggan ke halaman bayar. Poin baru masuk setelah callback lunas. */
    public function topup(Request $request, DuitkuPop $duitku)
    {
        $min = (int) config('duitku.min_topup');
        $max = (int) config('duitku.max_topup');
        $data = $request->validate([
            'amount' => ['required', 'integer', "min:$min", "max:$max"],
        ], [
            'amount.required' => 'Isi nominal top up.',
            'amount.integer' => 'Nominal harus angka tanpa titik atau koma.',
            'amount.min' => 'Top up minimal Rp '.number_format($min, 0, ',', '.').'.',
            'amount.max' => 'Top up maksimal Rp '.number_format($max, 0, ',', '.').' per transaksi.',
        ]);

        $user = Auth::user();
        $amount = (int) $data['amount'];
        $fee = Topup::feeFor($amount);

        $topup = Topup::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'fee' => $fee,
            'total' => $amount + $fee,
            'merchant_order_id' => 'TU'.$user->id.'-'.now()->format('ymdHis').'-'.Str::upper(Str::random(5)), // maks 50 karakter
            'status' => 'pending',
        ]);

        $items = [['name' => 'Top up poin', 'quantity' => 1, 'price' => $amount]];
        if ($fee > 0) {
            $items[] = ['name' => 'Biaya admin', 'quantity' => 1, 'price' => $fee];
        }

        try {
            $inv = $duitku->createInvoice([
                'paymentAmount' => $topup->total,
                'merchantOrderId' => $topup->merchant_order_id,
                'productDetails' => 'Top up poin Routerverse',
                'email' => $user->email,
                'customerVaName' => Str::limit(preg_replace('/[^A-Za-z0-9 ]/', '', $user->name) ?: 'Pelanggan', 20, ''),
                'itemDetails' => $items,
                'callbackUrl' => DuitkuPop::callbackUrl(),
                'returnUrl' => route('points.index', ['kembali' => 1]),
                'expiryPeriod' => (int) config('duitku.expiry_minutes'),
            ]);
        } catch (\Throwable $e) {
            $topup->update(['status' => 'failed']);
            report($e);

            return back()->withInput()->with('error', 'Top up belum bisa dibuat. Coba lagi sebentar lagi, atau hubungi admin.');
        }

        $topup->update(['reference' => $inv['reference'], 'payment_url' => $inv['paymentUrl']]);

        return redirect()->away($inv['paymentUrl']);
    }
}
