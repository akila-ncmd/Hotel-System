<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelAgency extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'contact_email', 'contact_number', 'is_verified'];

    protected $casts = [
        'is_verified' => 'boolean',
    ];
}