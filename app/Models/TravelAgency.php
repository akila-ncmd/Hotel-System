<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelAgency extends Model
{
    protected $fillable = ['name', 'contact_email', 'contact_number', 'is_verified'];

    protected $casts = [
        'is_verified' => 'boolean',
    ];
}