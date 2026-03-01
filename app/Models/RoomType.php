<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price_per_night',
        'capacity',
        'amenities',
        'image',
    ];

    protected $casts = [
        'amenities' => 'array',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function images()
    {
        return $this->hasMany(RoomTypeImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('is_visible', true);
    }

    public function getAverageRatingAttribute()
    {
        return round($this->reviews()->avg('rating'), 1) ?: 0;
    }

    /**
     * Scope a query to only include room types that have available rooms
     * for the given date range and capacity.
     */
    public function scopeAvailable($query, $checkIn, $checkOut, $guests = null)
    {
        if ($guests) {
            $query->where('capacity', '>=', $guests);
        }

        if (!$checkIn || !$checkOut) {
            return $query->whereHas('rooms', function ($q) {
                $q->where('status', '!=', 'maintenance');
            });
        }

        return $query->whereHas('rooms', function ($query) use ($checkIn, $checkOut) {
            $query->where('status', '!=', 'maintenance')
                ->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                    $q->where('status', '!=', 'cancelled')
                        ->where(function ($q2) use ($checkIn, $checkOut) {
                            $q2->where('check_in', '<', $checkOut)
                                ->where('check_out', '>', $checkIn);
                        });
                });
        });
    }
}
