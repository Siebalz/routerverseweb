@php($turnstileKey = \App\Services\Turnstile::enabled() ? \App\Services\Turnstile::siteKey() : null)
@if ($turnstileKey)
    @once
        @push('head')
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        @endpush
    @endonce
    <div class="field">
        <div class="cf-turnstile" data-sitekey="{{ $turnstileKey }}" data-theme="light" data-size="flexible" data-language="id"></div>
        @error('captcha') <p class="field-error" role="alert">{{ $message }}</p> @enderror
    </div>
@endif
