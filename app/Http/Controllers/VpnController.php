<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ServerInstance;
use App\Models\VpnAccount;
use App\Models\VpnPort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VpnController extends Controller
{
    /** Daftar akun VPN milik user. */
    public function index()
    {
        $accounts = Auth::user()->vpnAccounts()->with('ports')->latest()->get();

        return view('vpn.index', compact('accounts'));
    }

    /** Halaman order akun VPN baru. */
    public function create()
    {
        return view('vpn.create', [
            'servers' => $this->servers(),
            'plans' => config('vpn.plans'),
            'ports' => config('vpn.ports'),
        ]);
    }

    /** Simpan akun baru + port + pesanan yang menunggu pembayaran. */
    public function store(Request $request)
    {
        $plans = config('vpn.plans');
        $data = $request->validate([
            'server' => ['required', Rule::in(array_keys($this->servers()))],
            'plan' => ['required', Rule::in(array_keys($plans))],
            'username' => ['required', 'regex:/^[A-Za-z0-9]{4,24}$/', 'unique:vpn_accounts,username'],
            'password' => ['required', 'regex:/^[A-Za-z0-9]{6,24}$/'],
            'port_type' => ['required_if:plan,port1', 'nullable', Rule::in($plans['port1']['choose'])],
            'custom_port' => [Rule::requiredIf(fn () => $request->input('plan') === 'port1' && $request->input('port_type') === 'custom'), 'nullable', 'integer', 'between:1,65535'],
        ], [
            'username.regex' => 'Username hanya huruf dan angka tanpa spasi, 4 sampai 24 karakter.',
            'username.unique' => 'Username ini sudah dipakai. Coba yang lain.',
            'password.regex' => 'Password hanya huruf dan angka tanpa spasi, 6 sampai 24 karakter.',
            'port_type.required_if' => 'Pilih port tujuan.',
            'custom_port.required_if' => 'Isi port custom antara 1 sampai 65535.',
            'custom_port.between' => 'Port custom harus antara 1 sampai 65535.',
        ]);

        $plan = $plans[$data['plan']];
        $server = config("vpn.servers.{$data['server']}");

        // Daftar port yang dibuat: paket tetap = semua port paketnya, paket pilih = satu port pilihan.
        $ports = [];
        if ($plan['fixed']) {
            foreach ($plan['fixed'] as $name) {
                $ports[] = ['name' => $name, 'target' => config("vpn.ports.{$name}.port")];
            }
        } else {
            $ports[] = [
                'name' => $data['port_type'],
                'target' => $data['port_type'] === 'custom' ? (int) $data['custom_port'] : config("vpn.ports.{$data['port_type']}.port"),
            ];
        }

        $account = DB::transaction(function () use ($data, $plan, $server, $ports) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'category' => 'remote_server',
                'service_name' => 'VPN Remote '.$plan['name'].' '.$server['name'].' ('.$data['username'].')',
                'price_label' => 'Rp '.number_format($plan['price'], 0, ',', '.').' / bulan',
                'price' => $plan['price'],
                'notes' => 'Port tujuan: '.implode(', ', array_map(fn ($p) => $p['target'], $ports)),
                'status' => 'pending',
            ]);

            $account = VpnAccount::create([
                'user_id' => Auth::id(),
                'order_id' => $order->id,
                'server' => $data['server'],
                'plan' => $data['plan'],
                'username' => $data['username'],
                'password' => $data['password'],
                'port_type' => $ports[0]['name'],
                'target_port' => $ports[0]['target'],
                'price' => $plan['price'],
                'status' => 'pending',
            ]);
            foreach ($ports as $p) {
                $account->ports()->create(['server' => $data['server'], 'name' => $p['name'], 'target_port' => $p['target']]);
            }

            return $account;
        });

        return redirect()->route('services.my-orders')
            ->with('success', 'Pesanan VPN Remote dibuat. Lanjutkan dengan pembayaran, akun aktif setelah admin mengonfirmasi.')
            ->with('last_order_id', $account->order_id);
    }

    /** Halaman Kelola satu akun. */
    public function show(VpnAccount $vpn)
    {
        $this->own($vpn);
        $vpn->load('ports');

        $mikhmon = ServerInstance::whereHas('order', fn ($q) => $q->where('user_id', Auth::id()))
            ->where('status', 'active')->whereNotNull('mikhmon_subdomain')->latest()->first();

        $script = null;
        if ($vpn->status !== 'pending') {
            $script = \App\Services\VpnProvisioner::make()->clientScript($vpn->server, $vpn->username, $vpn->password, (int) config('vpn.watchdog_seconds'));
        }

        return view('vpn.show', [
            'vpn' => $vpn,
            'script' => $script,
            'mikhmon' => $mikhmon,
            'renewPending' => $vpn->order_id && Order::where('id', $vpn->order_id)->where('status', 'pending')->exists(),
        ]);
    }

    /** Ubah port tujuan (port di router pelanggan) untuk satu port. Port publik tidak berubah. */
    public function updatePort(Request $request, VpnAccount $vpn, VpnPort $port)
    {
        $this->own($vpn);
        abort_unless($port->vpn_account_id === $vpn->id, 404);

        $data = $request->validate([
            'target_port' => ['required', 'integer', 'between:1,65535'],
        ], [
            'target_port.required' => 'Isi port tujuan antara 1 sampai 65535.',
            'target_port.integer' => 'Port tujuan harus angka.',
            'target_port.between' => 'Port tujuan harus antara 1 sampai 65535.',
        ]);

        $port->update(['target_port' => (int) $data['target_port']]);
        $vpn->update(['needs_sync' => $vpn->status === 'active']);

        return $this->applied($vpn, $port->label.' sekarang diteruskan ke port '.$data['target_port'].'.');
    }

    public function updatePassword(Request $request, VpnAccount $vpn)
    {
        $this->own($vpn);

        $data = $request->validate([
            'password' => ['required', 'regex:/^[A-Za-z0-9]{6,24}$/'],
        ], ['password.regex' => 'Password hanya huruf dan angka tanpa spasi, 6 sampai 24 karakter.']);

        $vpn->update(['password' => $data['password'], 'needs_sync' => $vpn->status === 'active']);

        return $this->applied($vpn, 'Password disimpan. Tempel ulang script di router Anda.');
    }

    /** Perpanjang: buat pesanan perpanjangan yang menunggu pembayaran. */
    public function renew(VpnAccount $vpn)
    {
        $this->own($vpn);

        $pending = $vpn->order_id ? Order::where('id', $vpn->order_id)->where('status', 'pending')->first() : null;

        if (! $pending) {
            $pending = Order::create([
                'user_id' => Auth::id(),
                'category' => 'remote_server',
                'service_name' => 'Perpanjang VPN Remote '.$vpn->server_name.' ('.$vpn->username.')',
                'price_label' => 'Rp '.number_format($vpn->price, 0, ',', '.').' / bulan',
                'price' => $vpn->price,
                'notes' => 'Perpanjangan 1 bulan, paket '.$vpn->plan_name,
                'status' => 'pending',
            ]);
            $vpn->update(['order_id' => $pending->id]);
        }

        return redirect()->route('services.my-orders')
            ->with('success', 'Pesanan perpanjangan dibuat. Lanjutkan dengan pembayaran.')
            ->with('last_order_id', $pending->id);
    }

    /** Hanya server yang API MikroTik-nya sudah diisi, supaya pelanggan tidak memesan server yang belum siap. */
    protected function servers(): array
    {
        return array_filter(config('vpn.servers'), fn ($s) => ! empty($s['api']['host']) && ! empty($s['api']['user']));
    }

    /** Terapkan perubahan ke MikroTik sekarang dan beri tahu hasilnya. */
    protected function applied(VpnAccount $vpn, string $message)
    {
        if ($vpn->status === 'pending') {
            return back()->with('success', $message);
        }
        $err = $vpn->syncNow();

        return $err
            ? back()->with('error', 'Tersimpan, tapi belum bisa diterapkan ke server. Sistem mencoba lagi otomatis. ('.$err.')')
            : back()->with('success', $message);
    }

    /** Pelanggan menghapus VPN-nya: secret dan NAT di MikroTik ikut dihapus. */
    public function destroy(VpnAccount $vpn)
    {
        $this->own($vpn);
        $name = $vpn->username;

        try {
            DB::transaction(function () use ($vpn) {
                // Pesanan yang belum dibayar ikut dibatalkan; pesanan yang sudah berjalan tetap jadi riwayat.
                if ($vpn->status === 'pending' && $vpn->order_id) {
                    Order::where('id', $vpn->order_id)->where('status', 'pending')->update(['status' => 'dibatalkan']);
                }
                $vpn->destroyEverywhere();
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'VPN belum bisa dihapus dari server, coba lagi sebentar lagi. ('.$e->getMessage().')');
        }

        return redirect()->route('vpn.index')->with('success', "VPN {$name} dihapus, termasuk akun dan aturan NAT di server.");
    }

    protected function own(VpnAccount $vpn): void
    {
        abort_unless($vpn->user_id === Auth::id(), 404);
    }
}
