<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $fillable = ['name', 'price', 'description', 'image'];

    public function bookings()
    {
        return $this->belongsToMany(Booking::class)->withPivot('quantity', 'price_at_booking');
    }
}
