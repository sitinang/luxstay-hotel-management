<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function validateCoupon(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        $coupon = \App\Models\Coupon::where('code', $validated['code'])
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expired_at')
                    ->orWhere('expired_at', '>', now());
            })
            ->first();

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Kupon tidak valid atau sudah kedaluwarsa.',
            ], 404);
        }

        return response()->json([
            'valid' => true,
            'coupon' => $coupon,
            'message' => 'Kupon berhasil diterapkan!',
        ]);
    }
}
