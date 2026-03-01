<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Bookings/Index', [
            'bookings' => $request->user()->bookings()->with('room.roomType')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'phone' => 'required|string',
            'special_requests' => 'nullable|string',
            'selected_addons' => 'nullable|array',
            'selected_addons.*' => 'exists:addons,id',
            'coupon_code' => 'nullable|string',
        ]);

        // Find an available room for the given room type and dates
        $room = Room::where('room_type_id', $validated['room_type_id'])
            ->where('status', '!=', 'maintenance')
            ->whereDoesntHave('bookings', function ($query) use ($validated) {
                $query->where('status', '!=', 'cancelled')
                      ->where(function ($q) use ($validated) {
                          $q->where('check_in', '<', $validated['check_out'])
                            ->where('check_out', '>', $validated['check_in']);
                      });
            })
            ->first();

        if (!$room) {
            return back()->with('error', 'No rooms available for the selected dates.');
        }

        // Calculate Room Price
        $roomType = RoomType::find($validated['room_type_id']);
        $nights = (new \DateTime($validated['check_in']))->diff(new \DateTime($validated['check_out']))->days;
        $roomSubtotal = $nights * $roomType->price_per_night;

        // Calculate Addons Price
        $addonSubtotal = 0;
        $addonsData = [];
        if (!empty($validated['selected_addons'])) {
            $addons = \App\Models\Addon::whereIn('id', $validated['selected_addons'])->get();
            foreach ($addons as $addon) {
                $addonSubtotal += $addon->price;
                $addonsData[$addon->id] = ['quantity' => 1, 'price_at_booking' => $addon->price];
            }
        }

        $subtotal = $roomSubtotal + $addonSubtotal;

        // Handle Coupon
        $couponId = null;
        $discountAmount = 0;
        if (!empty($validated['coupon_code'])) {
            $coupon = \App\Models\Coupon::where('code', $validated['coupon_code'])
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('expired_at')->orWhere('expired_at', '>', now());
                })
                ->first();
            
            if ($coupon) {
                $couponId = $coupon->id;
                if ($coupon->discount_type === 'percent') {
                    $discountAmount = $subtotal * ($coupon->discount_amount / 100);
                } else {
                    $discountAmount = $coupon->discount_amount;
                }
            }
        }

        $totalBeforeTax = max(0, $subtotal - $discountAmount);
        $tax = $totalBeforeTax * 0.1;
        $totalPrice = $totalBeforeTax + $tax;

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'room_id' => $room->id,
            'coupon_id' => $couponId,
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'total_price' => $totalPrice,
            'status' => 'pending',
            'phone' => $validated['phone'],
            'special_requests' => $validated['special_requests'],
        ]);

        // Attach addons
        if (!empty($addonsData)) {
            $booking->addons()->attach($addonsData);
        }

        try {
            $booking->load(['user', 'room.roomType']);
            broadcast(new \App\Events\BookingCreated($booking));
            // Send to admin too if needed
        } catch (\Throwable $e) {
            \Log::error('Broadcast failed: ' . $e->getMessage());
        }

        return Redirect::route('bookings.index')->with('success', 'Booking successful!');
    }

    public function markArrived(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if ($booking->status !== 'confirmed') {
            return back()->with('error', 'Only confirmed bookings can be marked as arrived.');
        }

        $booking->update([
            'guest_status' => 'arrived',
            'actual_check_in' => now(),
        ]);

        try {
            $bookingAction = $booking->fresh()->load(['user', 'room.roomType']);
            broadcast(new \App\Events\GuestArrived($bookingAction));
        } catch (\Throwable $e) {
            \Log::error('GuestArrived broadcast failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Welcome! Your arrival has been notified to our staff.');
    }

    public function requestCheckout(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if ($booking->guest_status !== 'arrived') {
            return back()->with('error', 'You must be checked in to request check-out.');
        }

        $booking->update([
            'guest_status' => 'checkout_requested',
            'actual_check_out' => now(),
        ]);

        try {
            $bookingAction = $booking->fresh()->load(['user', 'room.roomType']);
            broadcast(new \App\Events\CheckoutRequested($bookingAction));
        } catch (\Throwable $e) {
            \Log::error('CheckoutRequested broadcast failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Check-out request sent. Please visit the reception to finalize.');
    }
}
