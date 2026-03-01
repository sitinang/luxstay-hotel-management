<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Review;
use App\Models\Booking;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $booking = Booking::with('room.roomType')->findOrFail($validated['booking_id']);

        // Check if user owns the booking
        if ($booking->user_id !== $request->user()->id) {
            return back()->with('error', 'Unauthorized.');
        }

        // Check if booking is completed
        if ($booking->status !== 'completed') {
            return back()->with('error', 'You can only review completed stays.');
        }

        // Check if already reviewed
        if ($booking->review) {
            return back()->with('error', 'You have already reviewed this stay.');
        }

        Review::create([
            'user_id' => $request->user()->id,
            'booking_id' => $booking->id,
            'room_type_id' => $booking->room->room_type_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return back()->with('success', 'Thank you for your review!');
    }
}
