<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    protected $fillable = [
        'action',
        'mac_address',
        'details',
    ];
    protected $appends = [
        'formatted_date'
    ];
}
