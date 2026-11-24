<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\ServerInstance;
use App\Services\ProvisioningService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

class MikhmonTest extends Command
{
    protected $signature = 'mikhmon:test {order? : ID pesanan Mikhmon yang ingin dibuat sekarang, tanpa antrean}';

    protected $description = 'Periksa koneksi ke Portainer/Docker dan (opsional) buat container untuk satu pesanan';

    public function handle(ProvisioningService $provisioning): int
    {
        $cfg = config('provisioning.docker');
        $this->line('API     : '.$cfg['api_url']);
        $this->line('Token   : '.($cfg['api_key'] ? 'terisi' : 'KOSONG'));
        $this->line('Jaringan: '.$cfg['network']);
        $this->line('Image   : '.$cfg['mikhmon_image']);
        $this->line('Antrean : '.config('queue.default').', menunggu '.DB::table('jobs')->count().', gagal '.DB::table('failed_jobs')->count());

        $http = Http::baseUrl(rtrim($cfg['api_url'], '/'))->timeout(10)->acceptJson();
        if ($cfg['api_key']) {
            $http = $http->withHeaders(['X-API-Key' => $cfg['api_key']]);
        }
        if (! $cfg['verify']) {
            $http = $http->withoutVerifying();
        }

        try {
            $v = $http->get('/version');
            $this->{$v->successful() ? 'info' : 'error'}('GET /version: HTTP '.$v->status().' '.($v->successful() ? 'Docker '.$v->json('Version') : substr($v->body(), 0, 200)));

            $n = $http->get('/networks/'.$cfg['network']);
            $this->{$n->successful() ? 'info' : 'error'}('Jaringan '.$cfg['network'].': '.($n->successful() ? 'ada' : 'HTTP '.$n->status().' (buat di Portainer)'));
        } catch (Throwable $e) {
            $this->error('Tidak bisa terhubung: '.$e->getMessage());

            return self::FAILURE;
        }

        foreach (ServerInstance::latest()->take(5)->get() as $i) {
            $this->line("Instance #{$i->id} order {$i->order_id}: {$i->status} {$i->mikhmon_subdomain} ".($i->last_error ? 'GALAT: '.$i->last_error : ''));
        }

        if ($id = $this->argument('order')) {
            try {
                $i = $provisioning->provision(Order::findOrFail($id));
                $this->info("Berhasil: {$i->docker_container_name} -> https://{$i->mikhmon_subdomain}");
            } catch (Throwable $e) {
                $this->error('Gagal: '.$e->getMessage());

                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
