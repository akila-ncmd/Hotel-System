<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    protected $fillable = ['name', 'description', 'price_per_night', 'weekly_rate', 'monthly_rate', 'max_occupants', 'is_suite'];

    protected $casts = [
        'is_suite' => 'boolean',
    ];
}