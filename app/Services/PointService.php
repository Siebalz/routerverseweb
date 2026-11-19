<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PointLedger;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Saldo poin (1 poin = Rp 1). Semua perubahan lewat sini: satu transaksi DB, baris user dikunci,
 * ledger hanya ditambah (tidak pernah diubah), saldo tidak bisa negatif.
 * Poin tidak kedaluwarsa dan tidak bisa ditarik; pengembalian hanya berupa poin (type refund).
 */
class PointService
{
    public function credit(User|int $user, int $amount, string $type, ?string $note = null, ?int $orderId = null): PointLedger
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Jumlah poin harus lebih dari 0.');
        }

        return $this->move($user, $amount, $type, $note, $orderId);
    }

    public function debit(User|int $user, int $amount, string $type, ?string $note = null, ?int $orderId = null): PointLedger
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Jumlah poin harus lebih dari 0.');
        }

        return $this->move($user, -$amount, $type, $note, $orderId);
    }

    /**
     * Bayar pesanan dengan poin: potong saldo dan tandai pesanan lunas, dalam satu transaksi.
     * Harga dibaca dari kolom orders.price (diisi server), bukan dari input pengguna.
     *
     * @throws InsufficientPointsException bila saldo kurang
     * @throws \DomainException bila pesanan tidak bisa dibayar dengan poin
     */
    public function payOrder(Order $order, User $user): Order
    {
        return DB::transaction(function () use ($order, $user) {
            $o = Order::whereKey($order->id)->lockForUpdate()->firstOrFail(); // cegah bayar ganda
            if ((int) $o->user_id !== (int) $user->id) {
                throw new \DomainException('Pesanan ini bukan milik Anda.');
            }
            if ($o->status !== 'pending' || $o->paid_at !== null) {
                throw new \DomainException('Pesanan ini sudah dibayar atau tidak menunggu pembayaran.');
            }
            if (! $o->price || $o->price <= 0) {
                throw new \DomainException('Pesanan ini belum punya harga tetap. Hubungi admin untuk pembayaran.');
            }

            $this->move($user, -$o->price, 'purchase', $o->service_name, $o->id);
            $o->update(['paid_with' => 'points', 'paid_at' => now()]);

            return $o;
        });
    }

    protected function move(User|int $user, int $delta, string $type, ?string $note, ?int $orderId): PointLedger
    {
        return DB::transaction(function () use ($user, $delta, $type, $note, $orderId) {
            $id = $user instanceof User ? $user->id : $user;
            $row = User::whereKey($id)->lockForUpdate()->firstOrFail();
            $new = (int) $row->points + $delta;
            if ($new < 0) {
                throw new InsufficientPointsException(-$delta, (int) $row->points);
            }
            $row->forceFill(['points' => $new])->save();
            if ($user instanceof User) {
                $user->points = $new; // jaga objek pemanggil tetap segar
            }

            return PointLedger::create([
                'user_id' => $id, 'type' => $type, 'amount' => $delta,
                'balance_after' => $new, 'order_id' => $orderId, 'note' => $note ? mb_substr($note, 0, 255) : null,
            ]);
        });
    }
}
