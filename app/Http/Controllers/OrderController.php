<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentSetting;
use App\Services\InsufficientPointsException;
use App\Services\OrderActivator;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Katalog layanan & paket yang bisa dipesan.
     * (Statis dulu, samaan dengan yang ditampilkan di halaman utama)
     */
    protected function catalog(): array
    {
        return [
            'remote_server' => [
                'label' => 'Remote Server',
                'icon' => 'bi-hdd-network',
                'color' => '#3b46f2',
                'items' => [
                    ['name' => '1 Port', 'amount' => config('vpn.plans.port1.price'), 'price' => 'Rp '.number_format(config('vpn.plans.port1.price'), 0, ',', '.').' / bulan', 'desc' => 'Pilih satu: Winbox, API, atau port custom. Script L2TP dan kelola port dari dashboard.'],
                    ['name' => '4 Port', 'amount' => config('vpn.plans.port4.price'), 'price' => 'Rp '.number_format(config('vpn.plans.port4.price'), 0, ',', '.').' / bulan', 'desc' => 'Winbox, API, WebFig, dan HTTPS sekaligus. Script L2TP dan kelola port dari dashboard.'],
                ],
            ],
            'hosting' => [
                'label' => 'Hosting',
                'icon' => 'bi-server',
                'color' => '#00c2a8',
                'items' => [
                    ['name' => 'Mikhmon Online', 'amount' => 10000, 'price' => 'Rp 10.000 / bulan', 'desc' => 'Akses 24/7, Remote Dashboard, Manage Hotspot & Voucheran, VPN Port API'],
                ],
            ],
            'network_setting' => [
                'label' => 'Setting Jaringan',
                'icon' => 'bi-router',
                'color' => '#ff9f43',
                'items' => [
                    ['name' => 'Load Balance', 'price' => 'Mulai Rp 50.000', 'desc' => 'Bagi beban beberapa jalur ISP otomatis biar koneksi stabil'],
                    ['name' => 'PPPoE Server', 'price' => 'Mulai Rp 50.000', 'desc' => 'Setup PPPoE untuk pelanggan bulanan, lengkap limit bandwidth'],
                    ['name' => 'Hotspot', 'price' => 'Mulai Rp 50.000', 'desc' => 'Setup hotspot voucheran, halaman login custom, integrasi Mikhmon'],
                    ['name' => 'Pisah Trafik', 'price' => 'Mulai Rp 50.000', 'desc' => 'Pisahkan trafik game, streaming, dan browsing'],
                ],
            ],
        ];
    }

    /**
     * Halaman katalog Layanan & Paket (bisa dipesan langsung).
     */
    public function index()
    {
        return view('services.index', ['catalog' => $this->catalog()]);
    }

    /**
     * Simpan pesanan baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|in:remote_server,hosting,network_setting',
            'service_name' => 'required|string|max:150',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Harga selalu dari katalog di server, bukan dari form.
        $item = collect($this->catalog()[$validated['category']]['items'])->firstWhere('name', $validated['service_name']);

        $order = Order::create([
            'user_id' => Auth::id(),
            'category' => $validated['category'],
            'service_name' => $validated['service_name'],
            'price_label' => $item['price'] ?? '-',
            'price' => $item['amount'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('services.my-orders')
            ->with('success', 'Pesanan berhasil dikirim! Tim kami akan segera menghubungi Anda untuk konfirmasi.')
            ->with('last_order_id', $order->id);
    }

    /**
     * Riwayat pesanan milik user yang login.
     */
    public function myOrders()
    {
        $orders = Auth::user()->orders()->latest()->get();
        $lastOrderId = session('last_order_id');
        $paymentSetting = PaymentSetting::current();

        $saldo = (int) Auth::user()->points;

        return view('services.my-orders', compact('orders', 'lastOrderId', 'paymentSetting', 'saldo'));
    }

    /**
     * Layanan yang sedang aktif dikelola untuk user ini ("Server Saya").
     * Hanya pesanan yang sudah dikonfirmasi admin (diproses/selesai).
     */
    public function myServers()
    {
        $servers = Auth::user()->orders()
            ->whereIn('status', [...Order::ACTIVE_STATUSES, 'expired'])
            ->latest()
            ->get();

        return view('services.my-servers', compact('servers'));
    }

    /**
     * Khusus admin: lihat semua pesanan dari semua user.
     */
    public function adminIndex()
    {
        $orders = Order::with('user')->latest()->paginate(15);

        return view('services.admin', compact('orders'));
    }

    /**
     * Khusus admin: update status pesanan.
     */
    public function updateStatus(Request $request, Order $order, OrderActivator $activator)
    {
        $request->validate([
            'status' => 'required|in:pending,diproses,selesai,dibatalkan,expired',
        ]);

        $vpnError = null;
        if (in_array($request->status, Order::ACTIVE_STATUSES, true)) {
            // Aktifkan lewat jalur yang sama dengan bayar poin: tanggal aktif, akun VPN, provisioning.
            $vpnError = $activator->activate($order, $request->status);
        } else {
            $order->status = $request->status;
            $order->save();
        }

        return $vpnError
            ? back()->with('error', 'Status diperbarui, tapi VPN belum tersinkron ke MikroTik: '.$vpnError.' Jalankan "php artisan vpn:test" untuk diagnosa.')
            : back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    /**
     * Pelanggan membayar pesanan dengan poin. Lunas = langsung diproses, tanpa menunggu admin.
     */
    public function payWithPoints(Order $order, PointService $points, OrderActivator $activator)
    {
        try {
            $order = $points->payOrder($order, Auth::user());
        } catch (InsufficientPointsException $e) {
            return redirect()->route('points.index')->with('error', 'Poin tidak cukup. Butuh Rp '.number_format($e->needed, 0, ',', '.').', saldo Anda Rp '.number_format($e->have, 0, ',', '.').'. Top up dulu.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        // Poin sudah terpotong. Kalau aktivasi bermasalah, pesanan tetap berstatus dibayar dan diulang oleh vpn:sync.
        $error = $activator->activate($order, 'diproses');

        return $error
            ? back()->with('error', 'Pembayaran berhasil, tapi layanan belum tersinkron ke server. Sistem mencoba lagi otomatis, atau hubungi admin. ('.$error.')')
            : back()->with('success', 'Pembayaran dengan poin berhasil. Layanan Anda sudah diproses.');
    }

    /**
     * Khusus admin: perpanjang masa aktif layanan 1 bulan dari sekarang
     * (atau dari tanggal expired sebelumnya jika belum lewat).
     */
    public function renew(Order $order, OrderActivator $activator)
    {
        $order->renewForOneMonth();

        $vpnError = $activator->applyVpn($order);

        return $vpnError
            ? back()->with('error', 'Masa aktif diperpanjang, tapi VPN belum tersinkron ke MikroTik: '.$vpnError)
            : back()->with('success', 'Masa aktif layanan berhasil diperpanjang 1 bulan.');
    }
}
