<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class EmailOTPController extends Controller
{
    /**
     * Display the OTP verification view.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(route('home', absolute: false))
            : Inertia::render('Auth/VerifyEmail', [
                'status' => session('status'),
            ]);
    }

    /**
     * Handle the OTP verification request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if ($user->isValidOTP($request->code)) {
            $user->markEmailAsVerified();
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();

            return redirect()->intended(route('home', absolute: false) . '?verified=1');
        }

        throw ValidationException::withMessages([
            'code' => 'The provided verification code is invalid or has expired.',
        ]);
    }

    /**
     * Resend the OTP verification code.
     */
    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home', absolute: false));
        }

        $user = $request->user();
        $user->generateOTP();
        
        try {
            Mail::to($user->email)->send(new OtpMail($user->otp_code, $user->name));
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email during resend: ' . $e->getMessage());
            Log::info('Resent OTP for ' . $user->email . ' is: ' . $user->otp_code);
        }

        return back()->with('status', 'otp-sent');
    }
}
