<?php

namespace Tests\Feature;

use App\Mail\LoginOtpMail;
use App\Models\LoginOtp;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Turnstile;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Verifikasi email saat daftar dan kode OTP saat login.
 * Jalankan: php artisan test --filter=AuthSecurityTest
 */
class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'security.email_verification' => true,
            'security.otp.enabled' => true,
            'security.otp.admin_required' => true,
            'security.otp.max_attempts' => 5,
            'security.turnstile.enabled' => false,
        ]);
    }

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'email' => 'admin@example.com']);
    }

    /** Login sebagai admin dan kembalikan kode OTP yang dikirim. */
    protected function loginAsAdminUntilOtp(): string
    {
        Mail::fake();
        $this->admin();

        $this->post('/login', ['email' => 'admin@example.com', 'password' => 'password'])
            ->assertRedirect(route('login.otp'));
        $this->assertGuest();

        $code = null;
        Mail::assertSent(LoginOtpMail::class, function (LoginOtpMail $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        return $code;
    }

    public function test_register_sends_verification_email_and_blocks_dashboard(): void
    {
        Notification::fake();
        User::factory()->create(); // supaya pendaftar berikutnya bukan admin pertama

        $this->post('/register', [
            'name' => 'Budi', 'email' => 'budi@example.com',
            'password' => 'Rahasia123', 'password_confirmation' => 'Rahasia123',
        ])->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'budi@example.com')->first();
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->assertNull($user->email_verified_at);

        $this->get('/dashboard')->assertRedirect(route('verification.notice'));
    }

    public function test_signed_link_verifies_email_without_login(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id, 'hash' => sha1($user->email),
        ]);

        $this->get($url)->assertRedirect(route('login'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_link_with_wrong_hash_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id, 'hash' => sha1('lain@example.com'),
        ]);

        $this->get($url)->assertForbidden();
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_customer_logs_in_without_otp(): void
    {
        User::factory()->create(['email' => 'budi@example.com']);

        $this->post('/login', ['email' => 'budi@example.com', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_admin_must_enter_otp_to_log_in(): void
    {
        $code = $this->loginAsAdminUntilOtp();

        $this->post('/login/verifikasi', ['code' => $code])->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseMissing('login_otps', ['user_id' => User::where('email', 'admin@example.com')->first()->id]);
    }

    public function test_wrong_code_does_not_log_in(): void
    {
        $code = $this->loginAsAdminUntilOtp();
        $wrong = $code === '000000' ? '000001' : '000000';

        $this->post('/login/verifikasi', ['code' => $wrong])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_code_is_locked_after_too_many_wrong_attempts(): void
    {
        $code = $this->loginAsAdminUntilOtp();
        $wrong = $code === '000000' ? '000001' : '000000';

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login/verifikasi', ['code' => $wrong]);
        }

        // Kode yang benar pun sudah tidak berlaku.
        $this->post('/login/verifikasi', ['code' => $code]);
        $this->assertGuest();
    }

    public function test_expired_code_is_rejected(): void
    {
        $code = $this->loginAsAdminUntilOtp();
        LoginOtp::query()->update(['expires_at' => now()->subMinute()]);

        $this->post('/login/verifikasi', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_code_cannot_be_reused(): void
    {
        $code = $this->loginAsAdminUntilOtp();
        $this->post('/login/verifikasi', ['code' => $code]);
        $this->assertAuthenticated();

        $this->post('/logout');
        $this->post('/login/verifikasi', ['code' => $code]);
        $this->assertGuest();
    }

    public function test_otp_page_needs_a_correct_password_first(): void
    {
        $this->get('/login/verifikasi')->assertRedirect(route('login'));
    }

    public function test_customer_can_enable_and_then_needs_otp(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'budi@example.com']);

        $this->actingAs($user)->post(route('security.otp.enable'), ['password' => 'password'])
            ->assertSessionHasNoErrors();
        $this->assertTrue($user->fresh()->otp_enabled);

        $this->post('/logout');
        $this->post('/login', ['email' => 'budi@example.com', 'password' => 'password'])
            ->assertRedirect(route('login.otp'));
        $this->assertGuest();
    }

    public function test_enabling_otp_needs_the_right_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('security.otp.enable'), ['password' => 'salah'])
            ->assertSessionHasErrors('password');
        $this->assertFalse($user->fresh()->otp_enabled);
    }

    public function test_admin_cannot_turn_otp_off(): void
    {
        $admin = $this->admin();
        $admin->forceFill(['otp_enabled' => true])->save();

        $this->actingAs($admin)->post(route('security.otp.disable'), ['password' => 'password']);
        $this->assertTrue($admin->fresh()->requiresOtp());
    }

    public function test_global_switch_turns_otp_off(): void
    {
        config(['security.otp.enabled' => false]);
        $this->admin();

        $this->post('/login', ['email' => 'admin@example.com', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_mail_failure_does_not_open_a_session(): void
    {
        $this->admin();
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));

        $this->post('/login', ['email' => 'admin@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // ---- Cloudflare Turnstile ----

    protected function enableTurnstile(): void
    {
        config([
            'security.turnstile.enabled' => true,
            'services.turnstile.site_key' => '1x00000000000000000000AA',
            'services.turnstile.secret_key' => '1x0000000000000000000000000000000AA',
        ]);
    }

    public function test_login_without_turnstile_token_is_rejected_before_checking_password(): void
    {
        $this->enableTurnstile();
        Http::fake();
        User::factory()->create(['email' => 'budi@example.com']);

        $this->post('/login', ['email' => 'budi@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('captcha');
        $this->assertGuest();
        Http::assertNothingSent();
    }

    public function test_login_with_valid_turnstile_token_passes(): void
    {
        $this->enableTurnstile();
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true])]);
        User::factory()->create(['email' => 'budi@example.com']);

        $this->post('/login', [
            'email' => 'budi@example.com', 'password' => 'password', 'cf-turnstile-response' => 'token-uji',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        Http::assertSent(fn ($request) => $request->url() === Turnstile::VERIFY_URL
            && $request['secret'] === '1x0000000000000000000000000000000AA'
            && $request['response'] === 'token-uji');
    }

    public function test_login_is_rejected_when_cloudflare_rejects_the_token(): void
    {
        $this->enableTurnstile();
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => false, 'error-codes' => ['timeout-or-duplicate']])]);
        User::factory()->create(['email' => 'budi@example.com']);

        $this->post('/login', [
            'email' => 'budi@example.com', 'password' => 'password', 'cf-turnstile-response' => 'token-bekas',
        ])->assertSessionHasErrors('captcha');
        $this->assertGuest();
    }

    public function test_login_is_rejected_when_cloudflare_is_unreachable(): void
    {
        $this->enableTurnstile();
        Http::fake(function () {
            throw new ConnectionException('down');
        });
        User::factory()->create(['email' => 'budi@example.com']);

        $this->post('/login', [
            'email' => 'budi@example.com', 'password' => 'password', 'cf-turnstile-response' => 'token-uji',
        ])->assertSessionHasErrors('captcha');
        $this->assertGuest();
    }

    public function test_register_needs_a_turnstile_token_when_enabled(): void
    {
        $this->enableTurnstile();
        Http::fake();
        User::factory()->create();

        $this->post('/register', [
            'name' => 'Budi', 'email' => 'budi@example.com',
            'password' => 'Rahasia123', 'password_confirmation' => 'Rahasia123',
        ])->assertSessionHasErrors('captcha');
        $this->assertDatabaseMissing('users', ['email' => 'budi@example.com']);
    }

    public function test_turnstile_is_skipped_when_keys_are_empty(): void
    {
        config(['security.turnstile.enabled' => true, 'services.turnstile.site_key' => null, 'services.turnstile.secret_key' => null]);
        User::factory()->create(['email' => 'budi@example.com']);

        $this->post('/login', ['email' => 'budi@example.com', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
    }
}