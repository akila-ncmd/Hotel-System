<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'room_type_id',
        'room_id',
        'check_in_date',
        'check_out_date',
        'duration_type',
        'duration_value',
        'number_of_occupants',
        'credit_card_details',
        'status',
    ];

    protected $casts = [
        'check_in_date' => 'datetime',
        'check_out_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function billing()
    {
        return $this->hasOne(Billing::class);
    }

    public function travelAgencyBooking()
    {
        return $this->hasOne(TravelAgencyBooking::class);
    }
}