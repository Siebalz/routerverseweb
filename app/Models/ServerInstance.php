<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServerInstance extends Model
{
    protected $fillable = [
        'order_id',
        'vpn_username',
        'vpn_password',
        'vpn_ip',
        'docker_container_id',
        'docker_container_name',
        'mikhmon_subdomain',
        'docker_internal_port',
        'status',
        'last_error',
        'provisioned_at',
    ];

    protected $casts = [
        'provisioned_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
