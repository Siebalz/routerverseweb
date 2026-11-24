<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ServerInstance;
use App\Models\User;
use App\Services\ProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Jalankan: php artisan test --filter=MikhmonProvisioningTest */
class MikhmonProvisioningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'provisioning.docker.api_url' => 'http://192.168.5.45:4500/api/endpoints/2/docker',
            'provisioning.docker.api_key' => 'ptr_uji',
            'provisioning.docker.base_domain' => 'mikhmonverse.my.id',
            'provisioning.docker.network' => 'mikhmon-net',
            'provisioning.docker.gate' => true,
            'provisioning.docker.mikhmon_image' => 'animegasan/mikhmon:v3-latest',
        ]);
    }

    protected function order(): Order
    {
        $user = User::factory()->create();

        return Order::create([
            'user_id' => $user->id, 'category' => 'hosting', 'service_name' => 'Mikhmon Online',
            'price' => 10000, 'price_label' => 'Rp 10.000 / bulan', 'status' => 'diproses',
        ]);
    }

    public function test_creates_container_with_routing_and_gate_login(): void
    {
        Http::fake([
            '*/images/*/json' => Http::response([], 200),
            '*/containers/mikhmon-order-*' => Http::response('', 204),
            '*/containers/create*' => Http::response(['Id' => 'abc123'], 201),
            '*/containers/abc123/start' => Http::response('', 204),
        ]);
        $order = $this->order();

        $instance = app(ProvisioningService::class)->provision($order);

        $this->assertSame('active', $instance->status);
        $this->assertSame("order{$order->id}.mikhmonverse.my.id", $instance->mikhmon_subdomain);
        $this->assertNotEmpty($instance->web_password);
        $this->assertNotSame($instance->web_password, $instance->getRawOriginal('web_password'));

        Http::assertSent(function (Request $r) use ($order) {
            if (! str_contains($r->url(), '/containers/create')) {
                return false;
            }
            $labels = $r['Labels'];
            $n = "mikhmon-order-{$order->id}";

            return $r->hasHeader('X-API-Key', 'ptr_uji')
                && $r['Image'] === 'animegasan/mikhmon:v3-latest'
                && $r['HostConfig']['NetworkMode'] === 'mikhmon-net'
                && $labels["traefik.http.routers.{$n}.rule"] === "Host(`order{$order->id}.mikhmonverse.my.id`)"
                && $labels["traefik.http.routers.{$n}.entrypoints"] === 'web'
                && str_starts_with($labels["traefik.http.middlewares.{$n}-auth.basicauth.users"], 'pelanggan:$2y$');
        });
    }

    public function test_pulls_image_when_missing(): void
    {
        Http::fake([
            '*/images/*/json' => Http::response([], 404),
            '*/images/create*' => Http::response('', 200),
            '*/containers/mikhmon-order-*' => Http::response('', 204),
            '*/containers/create*' => Http::response(['Id' => 'abc123'], 201),
            '*/containers/abc123/start' => Http::response('', 204),
        ]);

        app(ProvisioningService::class)->provision($this->order());

        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/images/create')
            && str_contains($r->url(), 'fromImage=animegasan%2Fmikhmon') && str_contains($r->url(), 'tag=latest'));
    }

    public function test_failure_is_recorded_and_rethrown(): void
    {
        Http::fake([
            '*/images/*/json' => Http::response([], 200),
            '*/containers/mikhmon-order-*' => Http::response('', 204),
            '*/containers/create*' => Http::response('boom', 500),
        ]);
        $order = $this->order();

        try {
            app(ProvisioningService::class)->provision($order);
            $this->fail('harus melempar');
        } catch (\RuntimeException) {
        }

        $this->assertSame('failed', ServerInstance::where('order_id', $order->id)->value('status'));
    }

    public function test_sync_stops_expired_and_restarts_renewed(): void
    {
        Http::fake(['*' => Http::response('', 204)]);
        $order = $this->order();
        $order->update(['expired_at' => now()->subDay()]);
        $i = ServerInstance::create(['order_id' => $order->id, 'status' => 'active', 'docker_container_name' => 'mikhmon-order-'.$order->id]);

        $this->artisan('mikhmon:sync')->assertSuccessful();
        $this->assertSame('suspended', $i->fresh()->status);

        $order->update(['expired_at' => now()->addMonth()]);
        $this->artisan('mikhmon:sync')->assertSuccessful();
        $this->assertSame('active', $i->fresh()->status);
    }

    public function test_customer_orders_with_chosen_subdomain_and_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('hosting.store'), ['subdomain' => 'Warnet01', 'password' => 'Sandi123'])
            ->assertRedirect(route('services.my-orders'));

        $i = ServerInstance::first();
        $this->assertSame('warnet01.mikhmonverse.my.id', $i->mikhmon_subdomain);
        $this->assertSame('Sandi123', $i->web_password);
        $this->assertSame('pending', $i->status);
        $this->assertSame('hosting', $i->order->category);
        $this->assertSame($user->id, $i->order->user_id);
    }

    public function test_provisioning_uses_the_chosen_subdomain_and_password(): void
    {
        Http::fake([
            '*/images/*/json' => Http::response([], 200),
            '*/containers/mikhmon-order-*' => Http::response('', 204),
            '*/containers/create*' => Http::response(['Id' => 'abc123'], 201),
            '*/containers/abc123/start' => Http::response('', 204),
        ]);
        $this->actingAs(User::factory()->create())
            ->post(route('hosting.store'), ['subdomain' => 'warnet01', 'password' => 'Sandi123']);
        $order = Order::first();

        $instance = app(ProvisioningService::class)->provision($order);

        $this->assertSame('warnet01.mikhmonverse.my.id', $instance->mikhmon_subdomain);
        $this->assertSame('Sandi123', $instance->web_password);
        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/containers/create')
            && $r['Labels']["traefik.http.routers.mikhmon-order-{$order->id}.rule"] === 'Host(`warnet01.mikhmonverse.my.id`)');
    }

    public function test_taken_reserved_and_invalid_subdomains_are_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('hosting.store'), ['subdomain' => 'warnet01', 'password' => 'Sandi123']);

        foreach (['warnet01', 'WARNET01', 'admin', 'abc', 'ada spasi', 'a-b-c-d'] as $bad) {
            $this->post(route('hosting.store'), ['subdomain' => $bad, 'password' => 'Sandi123'])
                ->assertSessionHasErrors('subdomain');
        }
        $this->post(route('hosting.store'), ['subdomain' => 'unik1234', 'password' => 'abc12'])
            ->assertSessionHasErrors('password');

        $this->assertSame(1, ServerInstance::count());
    }

    public function test_without_gate_there_is_no_traefik_login_and_no_password_needed(): void
    {
        config(['provisioning.docker.gate' => false]);
        Http::fake([
            '*/images/*/json' => Http::response([], 200),
            '*/containers/mikhmon-order-*' => Http::response('', 204),
            '*/containers/create*' => Http::response(['Id' => 'abc123'], 201),
            '*/containers/abc123/start' => Http::response('', 204),
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('hosting.store'), ['subdomain' => 'tanpagate'])
            ->assertSessionHasNoErrors();
        $order = Order::first();

        $instance = app(ProvisioningService::class)->provision($order);

        $this->assertSame('active', $instance->status);
        $this->assertNull($instance->web_password);
        Http::assertSent(function (Request $r) use ($order) {
            if (! str_contains($r->url(), '/containers/create')) {
                return false;
            }
            $labels = $r['Labels'];
            $n = "mikhmon-order-{$order->id}";

            return $labels["traefik.http.routers.{$n}.rule"] === 'Host(`tanpagate.mikhmonverse.my.id`)'
                && ! array_key_exists("traefik.http.routers.{$n}.middlewares", $labels)
                && ! array_key_exists("traefik.http.middlewares.{$n}-auth.basicauth.users", $labels);
        });
    }
}
