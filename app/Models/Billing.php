<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    protected $fillable = [
        'reservation_id', 'user_id', 'branch_id', 'total_amount', 'payment_method',
        'payment_status', 'restaurant_charges', 'room_service_charges', 'laundry_charges',
        'telephone_charges', 'club_facility_charges'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}