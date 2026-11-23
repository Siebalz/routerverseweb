<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Konstruktor DuitkuPop butuh string, jadi container perlu tahu cara membuatnya.
        $this->app->bind(\App\Services\DuitkuPop::class, fn () => \App\Services\DuitkuPop::make());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        // Di belakang Cloudflare Tunnel/proxy, pastikan tautan (termasuk tautan verifikasi email) selalu https.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Email verifikasi dalam bahasa Indonesia.
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            $minutes = (int) config('auth.verification.expire', 60);

            return (new MailMessage)
                ->subject('Verifikasi email akun Routerverse')
                ->greeting('Halo '.$notifiable->name.',')
                ->line('Terima kasih sudah mendaftar di Routerverse. Klik tombol di bawah untuk memastikan email ini milik Anda.')
                ->action('Verifikasi Email', $url)
                ->line('Tautan berlaku '.$minutes.' menit. Jika Anda tidak merasa mendaftar, abaikan email ini.')
                ->salutation('Salam, Routerverse');
        });
    }
}
