<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\RoomTypeImage;
use App\Models\FinancialTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $income = FinancialTransaction::where('type', 'income')->sum('amount');
        $expense = FinancialTransaction::where('type', 'expense')->sum('amount');
        $totalRooms = Room::count();

        // Monthly Revenue (Last 6 months)
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenue = FinancialTransaction::where('type', 'income')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');
            
            $monthlyRevenue[] = [
                'month' => $month->format('M'),
                'revenue' => (float)$revenue,
            ];
        }

        // Daily Occupancy (Last 30 days)
        $dailyOccupancy = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $occupiedCount = Booking::whereIn('status', ['confirmed', 'completed'])
                ->where('check_in', '<=', $date->format('Y-m-d'))
                ->where('check_out', '>', $date->format('Y-m-d'))
                ->count();
            
            $dailyOccupancy[] = [
                'date' => $date->format('d M'),
                'count' => $occupiedCount,
            ];
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_rooms' => $totalRooms,
                'available_rooms' => Room::where('status', 'available')->count(),
                'active_bookings' => Booking::where('status', 'confirmed')->count(),
                'total_revenue' => $income,
            ],
            'recentBookings' => Booking::with(['user', 'room.roomType'])->latest()->take(5)->get(),
            'roomTypes' => RoomType::with(['rooms', 'images'])->get(),
            'allBookings' => Booking::with(['user', 'room.roomType'])->latest()->get(),
            'allRooms' => Room::with('roomType')->get(),
            'transactions' => FinancialTransaction::with('booking')->latest()->get(),
            'financialSummary' => [
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense,
            ],
            'chartData' => [
                'monthlyRevenue' => $monthlyRevenue,
                'dailyOccupancy' => $dailyOccupancy,
                'roomTypeFavorites' => RoomType::withCount('rooms') // Counting bookings instead of rooms might be better
                    ->get()
                    ->map(fn($type) => [
                        'name' => $type->name,
                        'booking_count' => Booking::whereHas('room', fn($q) => $q->where('room_type_id', $type->id))->count()
                    ]),
            ],
            'calendarData' => Room::with(['roomType', 'bookings' => function($query) {
                $query->where('check_in', '>=', now()->startOfMonth())
                      ->where('check_out', '<=', now()->endOfMonth()->addMonth());
            }])->get(),
            'specialRequests' => Booking::with(['user', 'room.roomType'])
                ->whereNotNull('special_requests')
                ->where('special_requests', '!=', '')
                ->whereIn('status', ['pending', 'confirmed'])
                ->latest()
                ->get(),
        ]);
    }

    public function updateRoomStatus(Request $request, Room $room)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,occupied,maintenance',
        ]);

        $room->update($validated);

        return back()->with('success', 'Room status updated successfully.');
    }

    public function updateBookingStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        $booking->update($validated);
        
        // Update associated room status automatically
        if ($booking->room) {
            $roomStatus = 'available'; // Default
            if ($validated['status'] === 'confirmed') {
                $roomStatus = 'occupied';
            }
            $booking->room->update(['status' => $roomStatus]);
        }
        
        // If booking is confirmed, send a real-time notification to the user
        if ($validated['status'] === 'confirmed') {
            try {
                broadcast(new \App\Events\BookingConfirmed($booking->fresh()->load('room.roomType')));
            } catch (\Throwable $e) {
                \Log::error('BookingConfirmed broadcast failed: ' . $e->getMessage());
            }
        }

        // If booking is completed, record an income transaction automatically (if none exists)
        if ($validated['status'] === 'completed') {
            FinancialTransaction::firstOrCreate(
                ['booking_id' => $booking->id, 'type' => 'income'],
                [
                    'category' => 'Room Rental',
                    'amount' => $booking->total_price,
                    'description' => 'Revenue from room booking ' . ($booking->room ? $booking->room->room_number : ''),
                ]
            );

            // Award loyalty points (1 point per 100,000 IDR)
            $points = floor($booking->total_price / 100000); 
            $booking->user->increment('loyalty_points', $points);
        }

        return back()->with('success', 'Booking status updated successfully.');

    }

    public function clearSpecialRequest(Booking $booking)
    {
        $booking->update(['special_requests' => null]);
        return back()->with('success', 'Special request marked as handled.');
    }

    public function storeTransaction(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        FinancialTransaction::create($validated);

        return back()->with('success', 'Transaction added successfully.');
    }

    public function storeRoom(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'room_number' => 'required|string|unique:rooms',
            'status' => 'required|in:available,occupied,maintenance',
        ]);

        Room::create($validated);

        return back()->with('success', 'Room added successfully.');
    }

    public function updateRoom(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'room_number' => 'required|string|unique:rooms,room_number,' . $room->id,
            'status' => 'required|in:available,occupied,maintenance',
        ]);

        $room->update($validated);

        return back()->with('success', 'Room updated successfully.');
    }

    public function destroyRoom(Room $room)
    {
        $room->delete();
        return back()->with('success', 'Room deleted successfully.');
    }

    public function storeRoomType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_per_night' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('rooms', 'public');
            $validated['image'] = '/storage/' . $path;
        }

        RoomType::create($validated);

        return back()->with('success', 'Room type created successfully.');
    }

    public function updateRoomType(Request $request, RoomType $room_type)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_per_night' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('image')) {
            // Optionally delete old image
            if ($room_type->image) {
                $oldPath = str_replace('/storage/', '', $room_type->image);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('rooms', 'public');
            $validated['image'] = '/storage/' . $path;
        }

        $room_type->update($validated);

        return back()->with('success', 'Room type updated successfully.');
    }

    public function destroyRoomType(RoomType $room_type)
    {
        if ($room_type->image) {
            $oldPath = str_replace('/storage/', '', $room_type->image);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
        }
        $room_type->delete();
        return back()->with('success', 'Room type deleted successfully.');
    }

    public function storeRoomTypeImage(Request $request, RoomType $room_type)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $path = $request->file('image')->store('rooms', 'public');
        
        // If this is the first image, set it as thumbnail
        $isThumbnail = $room_type->images()->count() === 0;

        $room_type->images()->create([
            'image_path' => '/storage/' . $path,
            'is_thumbnail' => $isThumbnail
        ]);

        // Also update the main image field if this is the first one
        if ($isThumbnail && !$room_type->image) {
            $room_type->update(['image' => '/storage/' . $path]);
        }

        return back()->with('success', 'Image uploaded successfully.');
    }

    public function destroyRoomTypeImage(RoomTypeImage $room_type_image)
    {
        $oldPath = str_replace('/storage/', '', $room_type_image->image_path);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
        
        $room_type = $room_type_image->roomType;
        
        // If we are deleting the thumbnail, pick another one if exists
        $wasThumbnail = $room_type_image->is_thumbnail;
        $room_type_image->delete();

        if ($wasThumbnail) {
            $nextImage = $room_type->images()->first();
            if ($nextImage) {
                $nextImage->update(['is_thumbnail' => true]);
                $room_type->update(['image' => $nextImage->image_path]);
            } else {
                $room_type->update(['image' => null]);
            }
        }

        return back()->with('success', 'Image deleted successfully.');
    }

    public function setRoomTypeThumbnail(RoomTypeImage $room_type_image)
    {
        $room_type = $room_type_image->roomType;
        
        $room_type->images()->update(['is_thumbnail' => false]);
        $room_type_image->update(['is_thumbnail' => true]);
        
        $room_type->update(['image' => $room_type_image->image_path]);

        return back()->with('success', 'Thumbnail set successfully.');
    }
}
