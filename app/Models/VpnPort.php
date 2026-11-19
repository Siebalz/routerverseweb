<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VpnPort extends Model
{
    protected $fillable = ['vpn_account_id', 'server', 'name', 'public_port', 'target_port'];

    public function account()
    {
        return $this->belongsTo(VpnAccount::class, 'vpn_account_id');
    }

    public function getLabelAttribute(): string
    {
        return config("vpn.ports.{$this->name}.label", ucfirst($this->name));
    }

    /** Alamat yang dipakai pelanggan, mis. vpn1.routerverse.web.id:23736. Null kalau belum aktif. */
    public function getAddressAttribute(): ?string
    {
        return $this->public_port ? config("vpn.servers.{$this->server}.host", $this->server).':'.$this->public_port : null;
    }

    /**
     * Cari $n nomor port publik berurutan yang masih bebas di server ini.
     *
     * @return int[]
     */
    public static function allocate(string $server, int $n): array
    {
        $min = (int) config('vpn.remote_port_min');
        $max = (int) config('vpn.remote_port_max');
        $used = array_flip(static::where('server', $server)->whereNotNull('public_port')->pluck('public_port')->all());

        for ($try = 0; $try < 300; $try++) {
            $base = random_int($min, $max - $n + 1);
            $ok = true;
            for ($i = 0; $i < $n; $i++) {
                if (isset($used[$base + $i])) {
                    $ok = false;
                    break;
                }
            }
            if ($ok) {
                return range($base, $base + $n - 1);
            }
        }

        throw new \RuntimeException('Port publik untuk server ini sudah habis. Perbesar rentang di config/vpn.php.');
    }
}
