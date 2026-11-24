<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ServerInstance;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Pemesanan Mikhmon Online: pelanggan memilih subdomain dan sandi pembuka sendiri. */
class HostingController extends Controller
{
    /** Subdomain yang tidak boleh dipakai pelanggan. */
    public const RESERVED = [
        'www', 'mail', 'admin', 'api', 'app', 'portainer', 'traefik', 'panel', 'login', 'dashboard',
        'mikhmon', 'order', 'ftp', 'ns1', 'ns2', 'smtp', 'imap', 'pop', 'cpanel', 'webmail', 'vpn', 'support',
    ];

    public function create()
    {
        return view('hosting.create', [
            'domain' => config('provisioning.docker.base_domain'),
            'price' => (int) config('provisioning.docker.price'),
        ]);
    }

    public function store(Request $request)
    {
        $domain = config('provisioning.docker.base_domain');

        $request->merge(['subdomain' => strtolower(trim((string) $request->input('subdomain')))]);

        $data = $request->validate([
            'subdomain' => [
                'required', 'string', 'regex:/^[a-z0-9]{4,24}$/',
                Rule::notIn(self::RESERVED),
                // Alamat yang sedang dipakai atau dipesan orang lain.
                fn ($attr, $value, $fail) => ServerInstance::where('mikhmon_subdomain', "{$value}.{$domain}")->exists()
                    ? $fail('Subdomain ini sudah dipakai. Coba yang lain.') : null,
            ],
            'password' => ['required', 'string', 'regex:/^[A-Za-z0-9]{6,24}$/'],
        ], [
            'subdomain.regex' => 'Subdomain hanya huruf dan angka tanpa spasi, 4 sampai 24 karakter.',
            'subdomain.not_in' => 'Subdomain ini tidak boleh dipakai. Coba yang lain.',
            'password.regex' => 'Sandi hanya huruf dan angka tanpa spasi, 6 sampai 24 karakter.',
        ]);

        $price = (int) config('provisioning.docker.price');
        $host = "{$data['subdomain']}.{$domain}";

        try {
            $order = DB::transaction(function () use ($data, $price, $host) {
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'category' => 'hosting',
                    'service_name' => 'Mikhmon Online',
                    'price_label' => 'Rp '.number_format($price, 0, ',', '.').' / bulan',
                    'price' => $price,
                    'notes' => 'Subdomain: '.$host,
                    'status' => 'pending',
                ]);

                // Alamat langsung dipesan atas nama pesanan ini (indeks unik mencegah dua pelanggan memilih yang sama).
                ServerInstance::create([
                    'order_id' => $order->id,
                    'status' => 'pending',
                    'mikhmon_subdomain' => $host,
                    'web_user' => \App\Services\DockerProvisioningService::WEB_USER,
                    'web_password' => $data['password'],
                ]);

                return $order;
            });
        } catch (QueryException) {
            return back()->withInput($request->except('password'))
                ->withErrors(['subdomain' => 'Subdomain ini baru saja dipesan orang lain. Coba yang lain.']);
        }

        return redirect()->route('services.my-orders')
            ->with('success', "Pesanan Mikhmon Online dibuat dengan alamat https://{$host}. Bayar dengan poin dari halaman ini, layanan aktif otomatis.")
            ->with('last_order_id', $order->id);
    }
}
