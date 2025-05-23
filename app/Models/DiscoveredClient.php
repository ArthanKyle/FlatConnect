<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscoveredClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'mac_address',
        'ip_address',
        'repeater_name',
        'rssi',
        'rate',
        'active_time',
        'upload',
        'download',
    ];
}
