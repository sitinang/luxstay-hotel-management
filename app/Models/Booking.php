<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                // Generate a random uppercase alphanumeric string like BKG-XXXXXX
                $model->{$model->getKeyName()} = 'BKG-' . strtoupper(\Illuminate\Support\Str::random(6));
            }
        });
    }

    protected $fillable = [
        'user_id',
        'room_id',
        'coupon_id',
        'check_in',
        'check_out',
        'total_price',
        'status',
        'guest_status',
        'actual_check_in',
        'actual_check_out',
        'phone',
        'special_requests',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function addons()
    {
        return $this->belongsToMany(Addon::class, 'booking_addon')->withPivot('quantity', 'price_at_booking');
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
