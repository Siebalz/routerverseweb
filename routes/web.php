<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\VpnController;
use App\Http\Controllers\PaymentSettingController;
use App\Http\Controllers\LoginOtpController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\HostingController;

// 🔹 Halaman utama (home/landing) - ini yang tampil pertama saat web dibuka
Route::get('/', function () {
    $produkUnggulan   = \App\Models\Product::where('is_active', true)->with('images')->latest()->take(4)->get();
    $paymentSetting   = \App\Models\PaymentSetting::current();

    return view('welcome', compact('produkUnggulan', 'paymentSetting'));
})->name('welcome');

// 🔹 Halaman login (guest = hanya bisa diakses kalau BELUM login)
// throttle:10,1 = maksimal 10 request per menit per IP, lapisan tambahan anti brute-force/bot
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1')->name('login.process');

    // 🔹 Halaman register
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:5,1');

    // 🔹 Verifikasi kedua saat login: kode OTP yang dikirim ke email
    Route::get('/login/verifikasi', [LoginOtpController::class, 'show'])->name('login.otp');
    Route::post('/login/verifikasi', [LoginOtpController::class, 'verify'])->middleware('throttle:10,1')->name('login.otp.verify');
    Route::post('/login/verifikasi/kirim-ulang', [LoginOtpController::class, 'resend'])->middleware('throttle:5,1')->name('login.otp.resend');
    Route::post('/login/verifikasi/batal', [LoginOtpController::class, 'cancel'])->name('login.otp.cancel');
});

// 🔹 Verifikasi email setelah daftar
// Tautan di email dibuka tanpa harus login (aman karena bertanda tangan dan memuat hash email).
Route::get('/email/verifikasi/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:10,1'])->name('verification.verify');
Route::middleware('auth')->group(function () {
    Route::get('/email/verifikasi', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verifikasi/kirim-ulang', [EmailVerificationController::class, 'resend'])->middleware('throttle:3,1')->name('verification.send');
});

// 🔹 Dashboard (halaman default setelah login)
Route::get('/dashboard', [HomeController::class, 'index'])
    ->name('dashboard')
    ->middleware(['auth', 'email.verified']);

// 🔹 Alias lama, arahkan ke dashboard supaya tidak 404
Route::redirect('/home', '/dashboard');

// 🔹 Layanan & Paket (katalog + pemesanan setting jaringan / hosting)
Route::middleware(['auth', 'email.verified'])->group(function () {
    // Keamanan akun: status email dan verifikasi dua langkah
    Route::get('/dashboard/keamanan', [SecurityController::class, 'index'])->name('security.index');
    Route::post('/dashboard/keamanan/otp/aktifkan', [SecurityController::class, 'enable'])->middleware('throttle:10,1')->name('security.otp.enable');
    Route::post('/dashboard/keamanan/otp/nonaktifkan', [SecurityController::class, 'disable'])->middleware('throttle:10,1')->name('security.otp.disable');

    Route::get('/dashboard/layanan', [OrderController::class, 'index'])->name('services.index');
    Route::post('/dashboard/layanan/pesan', [OrderController::class, 'store'])->name('services.store');
    Route::get('/dashboard/pesanan-saya', [OrderController::class, 'myOrders'])->name('services.my-orders');
    Route::post('/dashboard/pesanan-saya/{order}/bayar-poin', [OrderController::class, 'payWithPoints'])->middleware('throttle:20,1')->name('services.pay-points');
    Route::get('/dashboard/poin', [PointController::class, 'index'])->name('points.index');
    Route::post('/dashboard/poin/topup', [PointController::class, 'topup'])->middleware('throttle:10,1')->name('points.topup');
    Route::get('/dashboard/server-saya', [OrderController::class, 'myServers'])->name('services.my-servers');

    // Hosting Mikhmon Online: pelanggan memilih subdomain sendiri
    Route::get('/dashboard/hosting/order', [HostingController::class, 'create'])->name('hosting.create');
    Route::post('/dashboard/hosting', [HostingController::class, 'store'])->middleware('throttle:10,1')->name('hosting.store');

    // VPN Remote: daftar akun, order, kelola
    Route::get('/dashboard/vpn-remote', [VpnController::class, 'index'])->name('vpn.index');
    Route::get('/dashboard/vpn-remote/order', [VpnController::class, 'create'])->name('vpn.create');
    Route::post('/dashboard/vpn-remote', [VpnController::class, 'store'])->name('vpn.store');
    Route::get('/dashboard/vpn-remote/{vpn}', [VpnController::class, 'show'])->name('vpn.show');
    Route::patch('/dashboard/vpn-remote/{vpn}/port/{port}', [VpnController::class, 'updatePort'])->name('vpn.port');
    Route::patch('/dashboard/vpn-remote/{vpn}/password', [VpnController::class, 'updatePassword'])->name('vpn.password');
    Route::delete('/dashboard/vpn-remote/{vpn}', [VpnController::class, 'destroy'])->name('vpn.destroy');
    Route::post('/dashboard/vpn-remote/{vpn}/perpanjang', [VpnController::class, 'renew'])->name('vpn.renew');

    // Khusus admin: kelola semua pesanan masuk
    Route::middleware('admin')->group(function () {
        Route::get('/dashboard/kelola-pesanan', [OrderController::class, 'adminIndex'])->name('services.admin');
        Route::patch('/dashboard/kelola-pesanan/{order}', [OrderController::class, 'updateStatus'])->name('services.update-status');
        Route::post('/dashboard/kelola-pesanan/{order}/perpanjang', [OrderController::class, 'renew'])->name('services.renew');

        // Pengaturan pembayaran (QRIS statik)
        Route::get('/dashboard/setting-pembayaran', [PaymentSettingController::class, 'edit'])->name('settings.payment.edit');
        Route::post('/dashboard/setting-pembayaran', [PaymentSettingController::class, 'update'])->name('settings.payment.update');
    });
});

// 🔹 Produk / Template Voucher Hotspot (marketplace publik, bisa diakses tanpa login)
Route::get('/produk', [ProductController::class, 'index'])->name('products.index');
Route::get('/produk/{product}', [ProductController::class, 'show'])->name('products.show');

// 🔹 Khusus admin: kelola produk tanpa perlu sentuh code
Route::middleware(['auth', 'email.verified', 'admin'])->group(function () {
    Route::get('/produk-create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/produk', [ProductController::class, 'store'])->name('products.store');
    Route::get('/produk/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/produk/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/produk/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
});

// 🔹 Logout
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Callback Duitku: tanpa login dan tanpa CSRF (dikecualikan di bootstrap/app.php); keamanan lewat signature.
Route::post('/duitku/callback', \App\Http\Controllers\DuitkuCallbackController::class)->middleware('throttle:120,1')->name('duitku.callback');
