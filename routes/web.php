<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\RoomController;
use App\Http\Controllers\BookingController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'roomTypes' => \App\Models\RoomType::all(),
        'recentReviews' => \App\Models\Review::with(['user', 'roomType'])->where('is_visible', true)->latest()->take(6)->get(),
    ]);
})->name('home');

Route::get('/tentang-kami', function () {
    return Inertia::render('About');
})->name('about');

Route::get('/fasilitas', function () {
    return Inertia::render('Facilities');
})->name('facilities');

Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
Route::get('/rooms/{roomType}', [RoomController::class, 'show'])->name('rooms.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
        Route::patch('/dashboard/rooms/{room}/status', [\App\Http\Controllers\DashboardController::class, 'updateRoomStatus'])->name('dashboard.rooms.status');
        Route::post('/dashboard/rooms', [\App\Http\Controllers\DashboardController::class, 'storeRoom'])->name('dashboard.rooms.store');
        Route::put('/dashboard/rooms/{room}', [\App\Http\Controllers\DashboardController::class, 'updateRoom'])->name('dashboard.rooms.update');
        Route::delete('/dashboard/rooms/{room}', [\App\Http\Controllers\DashboardController::class, 'destroyRoom'])->name('dashboard.rooms.destroy');

        Route::post('/dashboard/room-types', [\App\Http\Controllers\DashboardController::class, 'storeRoomType'])->name('dashboard.room-types.store');
        Route::put('/dashboard/room-types/{room_type}', [\App\Http\Controllers\DashboardController::class, 'updateRoomType'])->name('dashboard.room-types.update');
        Route::delete('/dashboard/room-types/{room_type}', [\App\Http\Controllers\DashboardController::class, 'destroyRoomType'])->name('dashboard.room-types.destroy');

        Route::post('/dashboard/room-types/{room_type}/images', [\App\Http\Controllers\DashboardController::class, 'storeRoomTypeImage'])->name('dashboard.room-types.images.store');
        Route::delete('/dashboard/room-types/images/{room_type_image}', [\App\Http\Controllers\DashboardController::class, 'destroyRoomTypeImage'])->name('dashboard.room-types.images.destroy');
        Route::patch('/dashboard/room-types/images/{room_type_image}/thumbnail', [\App\Http\Controllers\DashboardController::class, 'setRoomTypeThumbnail'])->name('dashboard.room-types.images.thumbnail');

        Route::patch('/dashboard/bookings/{booking}/status', [\App\Http\Controllers\DashboardController::class, 'updateBookingStatus'])->name('dashboard.bookings.status');
        Route::patch('/dashboard/bookings/{booking}/clear-request', [\App\Http\Controllers\DashboardController::class, 'clearSpecialRequest'])->name('dashboard.bookings.clear-request');
        Route::post('/dashboard/transactions', [\App\Http\Controllers\DashboardController::class, 'storeTransaction'])->name('dashboard.transactions.store');
    });
    
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::patch('/bookings/{booking}/mark-arrived', [BookingController::class, 'markArrived'])->name('bookings.mark-arrived');
    Route::patch('/bookings/{booking}/request-checkout', [BookingController::class, 'requestCheckout'])->name('bookings.request-checkout');

    Route::post('/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/coupons/validate', [App\Http\Controllers\CouponController::class, 'validateCoupon'])->name('coupons.validate');
});

Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'id'])) {
        session()->put('locale', $locale);
        // Also set currency in session immediately
        session()->put('currency', $locale === 'id' ? 'IDR' : 'USD');
    }
    
    // Use Inertia::location() for proper full-page redirect that Inertia.js can handle.
    // A regular redirect() doesn't trigger a full page re-render in Inertia apps.
    return \Inertia\Inertia::location(url()->previous());
})->name('language.switch');

require __DIR__.'/auth.php';
