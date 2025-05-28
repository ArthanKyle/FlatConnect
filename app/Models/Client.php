<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Auth\MustVerifyEmail;

class Client extends Authenticatable implements \Illuminate\Contracts\Auth\MustVerifyEmail
{
    use HasFactory, Notifiable, MustVerifyEmail;

    protected $fillable = [
       'first_name',
        'last_name',
        'email',
        'password',
        'mac_address',
        'repeater_name',
        'ip_address',
        'status',
        'next_due_date',
        'apartment_number',
        'building',        
        'last_seen_at', 
        'block_status',        
        'repeater_status',    
        'enforcement_status',     
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'next_due_date' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    protected $appends = ['next_due_formatted', 'fullname'];

    public function payments()
    {
        return $this->hasMany(\App\Models\Payment::class);
    }
    
    public function getStatusDisplayAttribute()
    {
        return $this->status;
    }

    public function getFullnameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getNextDueFormattedAttribute(): string
    {
        return $this->next_due_date
            ? $this->next_due_date->format('F j, Y')
            : 'N/A';
    }

    public function isBlocked(): bool
    {
        return $this->block_status === 'blocked';
    }
}
