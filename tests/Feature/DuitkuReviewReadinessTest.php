<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Kesiapan untuk verifikasi Duitku. Jalankan: php artisan test --filter=DuitkuReviewReadinessTest */
class DuitkuReviewReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_prices_address_and_email(): void
    {
        config([
            'services.business.address' => 'Jl. Contoh No. 1, Dayeuhkolot, Kab. Bandung',
            'services.business.email' => 'cs@example.com',
        ]);

        $this->get('/')->assertOk()
            ->assertSee('Mulai Rp 50.000')
            ->assertSee('Rp '.number_format(config('vpn.plans.port1.price'), 0, ',', '.'))
            ->assertSee('Jl. Contoh No. 1, Dayeuhkolot, Kab. Bandung')
            ->assertSee('cs@example.com')
            ->assertDontSee('PHPNuxBill');
    }

    public function test_review_account_can_log_in_and_has_balance(): void
    {
        config(['security.turnstile.enabled' => false, 'security.email_verification' => true]);

        $this->artisan('user:review-account', ['--password' => 'UjiDuitku123', '--points' => 100000])
            ->assertSuccessful();

        $user = User::where('email', 'uji.duitku@routerverse.web.id')->first();
        $this->assertNotNull($user->email_verified_at);
        $this->assertFalse($user->requiresOtp());
        $this->assertSame(100000, (int) $user->points);

        $this->post('/login', ['email' => $user->email, 'password' => 'UjiDuitku123'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->get(route('services.index'))->assertOk();
    }

    public function test_review_account_command_is_repeatable(): void
    {
        $this->artisan('user:review-account', ['--password' => 'UjiDuitku123'])->assertSuccessful();
        $this->artisan('user:review-account', ['--password' => 'UjiDuitku456'])->assertSuccessful();

        $this->assertSame(1, User::where('email', 'uji.duitku@routerverse.web.id')->count());
        $this->assertSame(100000, (int) User::first()->points);
    }
}
