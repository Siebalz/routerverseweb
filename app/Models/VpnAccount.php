<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class VpnAccount extends Model
{
    protected $fillable = [
        'user_id', 'order_id', 'applied_order_id', 'server', 'plan', 'username', 'password',
        'port_type', 'target_port', 'remote_port', 'vpn_ip', 'sync_error', 'price', 'status', 'needs_sync',
        'activated_at', 'expired_at',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'password' => 'encrypted',
        'needs_sync' => 'boolean',
        'activated_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function ports()
    {
        return $this->hasMany(VpnPort::class)->orderBy('id');
    }

    public function getPlanNameAttribute(): string
    {
        return config("vpn.plans.{$this->plan}.name", $this->plan);
    }

    /** IP publik server (untuk NAT), jatuh ke host bila tidak diisi. */
    public function getServerIpAttribute(): string
    {
        return config("vpn.servers.{$this->server}.public_ip") ?: $this->host;
    }

    public function getServerNameAttribute(): string
    {
        return config("vpn.servers.{$this->server}.name", $this->server);
    }

    public function getHostAttribute(): string
    {
        return config("vpn.servers.{$this->server}.host", $this->server);
    }

    /** Status yang ditampilkan: aktif yang sudah lewat tanggal dianggap expired. */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === 'active' && $this->expired_at && $this->expired_at->isPast()) {
            return 'expired';
        }

        return $this->status;
    }

    public function getStatusLabelAttribute(): string
    {
        return ['pending' => 'Menunggu', 'active' => 'Aktif', 'expired' => 'Expired'][$this->display_status] ?? $this->status;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return ['pending' => 'order-badge-pending', 'active' => 'order-badge-done', 'expired' => 'order-badge-expired'][$this->display_status] ?? 'order-badge-pending';
    }

    public function getDaysLeftAttribute(): ?int
    {
        return $this->expired_at
            ? (int) Carbon::now()->startOfDay()->diffInDays($this->expired_at->copy()->startOfDay(), false)
            : null;
    }

    /**
     * Terapkan akun ini ke MikroTik sekarang juga (secret + NAT per port).
     *
     * @return string|null pesan galat, atau null bila berhasil / tidak perlu
     */
    public function syncNow(): ?string
    {
        if ($this->status === 'pending') {
            return null;
        }

        try {
            (new \App\Jobs\SyncVpnAccountJob($this->id))->apply($this);

            return null;
        } catch (\Throwable $e) {
            $this->update(['needs_sync' => true, 'sync_error' => mb_substr($e->getMessage(), 0, 500)]);
            \Illuminate\Support\Facades\Log::warning("Sinkron VPN {$this->username} gagal: ".$e->getMessage());

            return $e->getMessage();
        }
    }

    /** Hapus akun dari MikroTik (secret, sesi aktif, semua NAT), lalu dari database. Galat dilempar ke pemanggil. */
    public function destroyEverywhere(): void
    {
        if ($this->status !== 'pending' || $this->vpn_ip) {
            \App\Services\VpnProvisioner::make()->remove($this->server, $this->username);
        }
        $this->delete(); // port ikut terhapus (cascade)
    }

    /**
     * Dipanggil saat admin mengaktifkan pesanan terkait (diproses/selesai).
     * Aman dipanggil berulang untuk pesanan yang sama.
     */
    public function applyOrder(Order $order): void
    {
        if ($this->applied_order_id === $order->id) {
            if ($this->needs_sync) {
                $this->syncNow(); // sinkron sebelumnya gagal: coba lagi saat admin menyimpan ulang
            }

            return;
        }

        $base = $this->expired_at && $this->expired_at->isFuture() ? $this->expired_at : now();

        // Bagikan port publik untuk port yang belum punya (akun baru). Perpanjangan tidak mengubahnya.
        $missing = $this->ports()->whereNull('public_port')->get();
        if ($missing->isNotEmpty()) {
            $block = VpnPort::allocate($this->server, $missing->count());
            foreach ($missing->values() as $i => $port) {
                $port->update(['public_port' => $block[$i]]);
            }
        }
        $this->activated_at = $this->activated_at ?? now();
        $this->expired_at = Carbon::parse($base)->addMonthNoOverflow();
        $this->status = 'active';
        $this->applied_order_id = $order->id;
        $this->needs_sync = true; // akun baru atau diperpanjang: server perlu menyamakan
        $this->save();

        $this->syncNow(); // langsung, tanpa antrean; galat disimpan di sync_error dan dicoba ulang oleh vpn:sync
    }

}
