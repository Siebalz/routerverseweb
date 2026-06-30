{{--
    SECTION TESTIMONI — Routerverse
    Sisipkan di welcome.blade.php tepat sebelum section #kontak-kami:

        @include('components.testimoni')

    atau pakai component syntax:
        <x-testimoni />
--}}

<section id="testimoni" class="bg-canvas py-24 overflow-hidden">
    <div class="mx-auto max-w-6xl px-6 lg:px-8">

        {{-- Header --}}
        <div class="reveal mx-auto mb-16 max-w-2xl text-center">
            <span class="section-label border border-line bg-white text-indigo shadow-soft">
                <i class="bi bi-stars"></i> Testimoni
            </span>
            <h2 class="font-display mt-5 text-[34px] font-bold tracking-tight text-ink lg:text-[42px]">
                Kata Mereka yang Sudah Pakai
            </h2>
            <p class="mt-4 text-[16px] leading-[1.7] text-muted">
                Dari warnet, RT/RW Net, sampai kantor — mereka percayakan jaringannya ke Routerverse.
            </p>
        </div>

        {{-- Grid testimoni --}}
        @php
        $testimonials = [
            [
                'name'      => 'Hendra Gunawan',
                'role'      => 'Pemilik Warnet',
                'city'      => 'Bandung',
                'avatar'    => 'HG',
                'color'     => '#3B46F2',
                'rating'    => 5,
                'text'      => 'Load balance 2 ISP langsung jalan mulus setelah diremote sama tim Routerverse. Ga perlu dateng ke lokasi, semua beres dalam 1 jam. Koneksi warnet sekarang jauh lebih stabil.',
                'badge'     => 'Load Balance',
                'badge_icon'=> 'bi-diagram-3',
            ],
            [
                'name'      => 'Pak Rudi',
                'role'      => 'Ketua RT/RW Net',
                'city'      => 'Cimahi',
                'avatar'    => 'RD',
                'color'     => '#00C2A8',
                'rating'    => 5,
                'text'      => 'PPPoE server sudah aktif buat 30 pelanggan, lengkap dengan limit per user. Mikhmon Online-nya gampang banget dipake dari HP. Mantap layanannya, respon cepet!',
                'badge'     => 'PPPoE + Mikhmon',
                'badge_icon'=> 'bi-plug',
            ],
            [
                'name'      => 'Dini Rahayu',
                'role'      => 'IT Admin Perusahaan',
                'city'      => 'Jakarta Selatan',
                'avatar'    => 'DR',
                'color'     => '#D98A3D',
                'rating'    => 5,
                'text'      => 'Setup VPN L2TP buat akses remote kantor. Sekarang team bisa connect ke server lokal dari rumah tanpa ribet. Setup-nya rapi dan dikasih dokumentasi yang lengkap.',
                'badge'     => 'Remote VPN',
                'badge_icon'=> 'bi-shield-lock',
            ],
            [
                'name'      => 'Budi Santoso',
                'role'      => 'Owner Kafe',
                'city'      => 'Depok',
                'avatar'    => 'BS',
                'color'     => '#E6446A',
                'rating'    => 5,
                'text'      => 'Hotspot voucher kafe saya sekarang punya halaman login custom dengan logo sendiri. Voucher bisa di-generate dari HP. Pelanggan kafe makin happy karena WiFi-nya cepet dan teratur.',
                'badge'     => 'Hotspot Voucher',
                'badge_icon'=> 'bi-wifi',
            ],
            [
                'name'      => 'Fajar Nugroho',
                'role'      => 'Teknisi Jaringan',
                'city'      => 'Bekasi',
                'avatar'    => 'FN',
                'color'     => '#7C85F5',
                'rating'    => 5,
                'text'      => 'Butuh pisah trafik game sama streaming — langsung dikerjain, mangle rules-nya bersih dan terdokumentasi. Sekarang latency game pelanggan jauh turun. Highly recommended!',
                'badge'     => 'Pisah Trafik',
                'badge_icon'=> 'bi-signpost-split',
            ],
            [
                'name'      => 'Lia Permatasari',
                'role'      => 'Pemilik Kos-kosan',
                'city'      => 'Bandung',
                'avatar'    => 'LP',
                'color'     => '#00C2A8',
                'rating'    => 5,
                'text'      => 'Awalnya bingung gimana kasih WiFi ke 20 kamar tanpa boros bandwidth. Setelah konsultasi dan di-setting Routerverse, semua kamar dapat jatah fair. Penghuni puas, saya tenang.',
                'badge'     => 'Hotspot + Queue',
                'badge_icon'=> 'bi-people',
            ],
        ];
        @endphp

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($testimonials as $i => $t)
            <div class="reveal card-lift flex flex-col rounded-3xl border border-line bg-white p-7 shadow-card"
                 style="transition-delay: {{ $i * 60 }}ms">

                {{-- Quote dekoratif --}}
                <div class="mb-4 font-display text-[52px] font-bold leading-none select-none"
                     style="color: {{ $t['color'] }}; opacity: 0.15;" aria-hidden="true">"</div>

                {{-- Teks testimoni --}}
                <p class="flex-1 text-[14.5px] leading-[1.75] text-ink/75 -mt-4">
                    {{ $t['text'] }}
                </p>

                {{-- Badge layanan yang digunakan --}}
                <div class="mt-5 mb-5">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 font-mono text-[11px] font-semibold"
                          style="background: {{ $t['color'] }}18; color: {{ $t['color'] }};">
                        <i class="bi {{ $t['badge_icon'] }}" style="font-size: 10px;" aria-hidden="true"></i>
                        {{ $t['badge'] }}
                    </span>
                </div>

                {{-- Divider --}}
                <div class="border-t border-line mb-5"></div>

                {{-- Author + Rating --}}
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        {{-- Avatar inisial --}}
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full font-display text-[12px] font-bold text-white"
                             style="background: {{ $t['color'] }};"
                             aria-hidden="true">
                            {{ $t['avatar'] }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-[13.5px] font-semibold text-ink truncate">{{ $t['name'] }}</div>
                            <div class="mt-0.5 text-[12px] text-muted truncate">{{ $t['role'] }} · {{ $t['city'] }}</div>
                        </div>
                    </div>

                    {{-- Bintang rating --}}
                    <div class="flex flex-shrink-0 gap-0.5" aria-label="{{ $t['rating'] }} bintang dari 5">
                        @for($s = 0; $s < 5; $s++)
                            <i class="bi bi-star-fill text-[11px]"
                               style="color: {{ $s < $t['rating'] ? '#F3B873' : '#E6E8F0' }};"
                               aria-hidden="true"></i>
                        @endfor
                    </div>
                </div>

            </div>
            @endforeach
        </div>

        {{-- CTA bawah --}}
        <div class="reveal mt-14 text-center">
            <p class="text-[15px] text-muted mb-6">
                Bergabung dengan <strong class="font-semibold text-ink">50+ klien</strong>
                yang sudah mempercayakan jaringannya ke Routerverse.
            </p>
            <a href="https://wa.me/6285173484715?text=Halo%20Routerverse%2C%20saya%20mau%20konsultasi%20kebutuhan%20jaringan%20saya"
               target="_blank"
               class="inline-flex items-center gap-2.5 rounded-full bg-indigo px-7 py-3.5 text-[14.5px] font-semibold text-white shadow-card transition hover:bg-indigo-deep">
                <i class="bi bi-whatsapp" aria-hidden="true"></i>
                Konsultasi Gratis Sekarang
            </a>
        </div>

    </div>
</section>
