<?php

namespace App\Services;

class RouterOsException extends \RuntimeException
{
}

/**
 * Klien RouterOS API (port 8728, atau 8729 untuk TLS) tanpa dependensi.
 * Port dari pantau/app/ros.py. Jalan di RouterOS v6 dan v7.
 *
 * Pakai:  $c = RouterOsApi::connect($host, $port, $ssl, $user, $pass);
 *         [$rows, $done] = $c->call('/ppp/secret/print', [], ['?name=budi']);
 *         $c->close();
 */
class RouterOsApi
{
    /** @var resource */
    private $sock;

    private float $deadline;

    public function __construct(string $host, int $port = 8728, bool $ssl = false, float $timeout = 15.0)
    {
        $this->deadline = microtime(true) + $timeout;
        $ctx = stream_context_create(['ssl' => [
            'verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true,
            'ciphers' => 'ALL:@SECLEVEL=0', // RouterOS lama
        ]]);
        $s = @stream_socket_client(($ssl ? 'ssl://' : 'tcp://')."{$host}:{$port}", $errno, $errstr, min(6.0, $timeout), STREAM_CLIENT_CONNECT, $ctx);
        if (! $s) {
            throw new RouterOsException("Tidak bisa konek ke {$host}:{$port} ({$errstr})");
        }
        $this->sock = $s;
    }

    public static function connect(string $host, int $port, bool $ssl, string $user, string $password, float $timeout = 15.0): self
    {
        $c = new self($host, $port, $ssl, $timeout);
        try {
            $c->login($user, $password);
        } catch (RouterOsException $e) {
            $c->close();
            throw new RouterOsException('Login gagal: '.$e->getMessage());
        }

        return $c;
    }

    // ---------------------------------------------------------------- low level
    private function recv(int $n): string
    {
        $buf = '';
        while (strlen($buf) < $n) {
            $left = $this->deadline - microtime(true);
            if ($left <= 0) {
                throw new RouterOsException('Timeout menunggu respon router');
            }
            stream_set_timeout($this->sock, (int) floor($left), (int) (($left - floor($left)) * 1e6));
            $chunk = fread($this->sock, $n - strlen($buf));
            if ($chunk === false || $chunk === '') {
                $meta = stream_get_meta_data($this->sock);
                throw new RouterOsException(! empty($meta['timed_out']) ? 'Timeout menunggu respon router' : 'Koneksi ditutup oleh router');
            }
            $buf .= $chunk;
        }

        return $buf;
    }

    public static function encodeLength(int $n): string
    {
        if ($n < 0x80) {
            return chr($n);
        }
        if ($n < 0x4000) {
            return pack('n', $n | 0x8000);
        }
        if ($n < 0x200000) {
            $v = $n | 0xC00000;

            return chr(($v >> 16) & 0xFF).chr(($v >> 8) & 0xFF).chr($v & 0xFF);
        }
        if ($n < 0x10000000) {
            return pack('N', $n | 0xE0000000);
        }

        return "\xF0".pack('N', $n);
    }

    private function readLength(): int
    {
        $c = ord($this->recv(1));
        if ($c < 0x80) {
            return $c;
        }
        if (($c & 0xC0) === 0x80) {
            return (($c & 0x3F) << 8) | ord($this->recv(1));
        }
        if (($c & 0xE0) === 0xC0) {
            $b = $this->recv(2);

            return (($c & 0x1F) << 16) | (ord($b[0]) << 8) | ord($b[1]);
        }
        if (($c & 0xF0) === 0xE0) {
            $b = $this->recv(3);

            return (($c & 0x0F) << 24) | (ord($b[0]) << 16) | (ord($b[1]) << 8) | ord($b[2]);
        }
        if ($c === 0xF0) {
            return unpack('N', $this->recv(4))[1];
        }
        throw new RouterOsException('Format panjang kata tidak valid');
    }

    private function writeSentence(array $words): void
    {
        $out = '';
        foreach ($words as $w) {
            $out .= self::encodeLength(strlen($w)).$w;
        }
        $out .= "\x00";
        $left = max(0.5, $this->deadline - microtime(true));
        stream_set_timeout($this->sock, (int) floor($left), (int) (($left - floor($left)) * 1e6));
        $len = strlen($out);
        for ($sent = 0; $sent < $len;) {
            $n = fwrite($this->sock, substr($out, $sent));
            if ($n === false || $n === 0) {
                throw new RouterOsException('Gagal mengirim ke router');
            }
            $sent += $n;
        }
    }

    private function readSentence(): array
    {
        $words = [];
        while (true) {
            $n = $this->readLength();
            if ($n === 0) {
                return $words;
            }
            $words[] = $this->recv($n);
        }
    }

    // ---------------------------------------------------------------- api
    /**
     * @param  array<string,string>  $attrs  atribut "=k=v"
     * @param  string[]  $queries  mis. ['?name=budi']
     * @param  string[]  $proplist
     * @return array{0: array<int,array<string,string>>, 1: array<string,string>}
     */
    public function call(string $command, array $attrs = [], array $queries = [], array $proplist = []): array
    {
        $words = [$command];
        foreach ($attrs as $k => $v) {
            $words[] = "={$k}={$v}";
        }
        foreach ($queries as $q) {
            $words[] = $q;
        }
        if ($proplist) {
            $words[] = '.proplist='.implode(',', $proplist);
        }
        $this->writeSentence($words);

        $rows = [];
        $done = [];
        $err = null;
        while (true) {
            $s = $this->readSentence();
            if (! $s) {
                continue;
            }
            $kind = array_shift($s);
            $d = [];
            foreach ($s as $w) {
                if ($w !== '' && $w[0] === '=' && strpos($w, '=', 1) !== false) {
                    $pos = strpos($w, '=', 1);
                    $d[substr($w, 1, $pos - 1)] = substr($w, $pos + 1);
                }
            }
            if ($kind === '!re') {
                $rows[] = $d;
            } elseif ($kind === '!done') {
                $done = $d;
                break;
            } elseif ($kind === '!trap') {
                $err = $d['message'] ?? 'trap';
            } elseif ($kind === '!fatal') {
                throw new RouterOsException('Fatal: '.implode(' ', $s));
            }
        }
        if ($err !== null) {
            throw new RouterOsException($err);
        }

        return [$rows, $done];
    }

    public function login(string $user, string $password): void
    {
        [, $done] = $this->call('/login', ['name' => $user, 'password' => $password]);
        if (isset($done['ret'])) { // RouterOS < 6.43 (challenge)
            $ch = hex2bin($done['ret']);
            $h = md5("\x00".$password.$ch);
            $this->call('/login', ['name' => $user, 'response' => '00'.$h]);
        }
    }

    public function close(): void
    {
        if (is_resource($this->sock)) {
            @fclose($this->sock);
        }
    }
}
