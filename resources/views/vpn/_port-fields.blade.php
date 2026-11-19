<fieldset class="portbox">
    <legend>Port tujuan</legend>
    <div class="radios">
        @foreach ($ports as $key => $p)
            <label class="radio">
                <input type="radio" name="port_type" value="{{ $key }}" @checked($current === $key) data-port-radio>
                <span>{{ $p['label'] }}@if ($p['port']) ({{ $p['port'] }})@else (1 sampai 65535)@endif</span>
            </label>
        @endforeach
    </div>
    <div class="field" data-custom-port @if ($current !== 'custom') hidden @endif style="margin:12px 0 0">
        <label for="custom_port">Nomor port custom</label>
        <input class="input" type="number" id="custom_port" name="custom_port" min="1" max="65535" inputmode="numeric" value="{{ $custom }}" @if ($current === 'custom') required @endif>
    </div>
</fieldset>
