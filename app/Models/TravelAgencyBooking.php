<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelAgencyBooking extends Model
{
    protected $fillable = ['travel_agency_id', 'reservation_id', 'discount_percentage', 'quotation_amount'];

    public function travelAgency()
    {
        return $this->belongsTo(TravelAgency::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}