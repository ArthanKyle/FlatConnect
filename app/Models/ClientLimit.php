<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'download_limit',
        'upload_limit',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
