<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\RoomType;
use Inertia\Inertia;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $checkIn = $request->input('check_in');
        $checkOut = $request->input('check_out');
        $guests = $request->input('guests');

        $roomTypes = RoomType::with(['images'])
            ->withCount('rooms')
            ->available($checkIn, $checkOut, $guests)
            ->get();

        return Inertia::render('Rooms/Index', [
            'roomTypes' => $roomTypes,
            'filters' => $request->only(['check_in', 'check_out', 'guests']),
        ]);
    }

    public function show(Request $request, RoomType $roomType)
    {
        return Inertia::render('Rooms/Show', [
            'roomType' => $roomType->load(['rooms', 'images', 'reviews' => function ($query) {
                $query->with('user')->latest();
            }]),
            'filters' => $request->only(['check_in', 'check_out', 'guests']),
            'addons' => \App\Models\Addon::all(),
        ]);
    }
}
