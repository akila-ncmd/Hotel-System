<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id', 'room_type_id', 'room_number', 'status'];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'room_id');
    }
}