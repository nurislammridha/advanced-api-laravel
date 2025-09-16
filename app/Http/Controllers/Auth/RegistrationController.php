<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Generate OTP
        $otp = rand(100000, 999999);
        UserOtp::create([
            'user_id' => $user->id,
            'otp' => $otp,
        ]);

        // Send email (basic example)
        Mail::raw("Your verification code is: $otp", function ($message) use ($user) {
            $message->to($user->email)->subject('Verify your email');
        });

        return response()->json(['message' => 'OTP sent to your email']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        $user = User::where('email', $request->email)->firstOrFail();
        $userOtp = UserOtp::where('user_id', $user->id)
            ->where('otp', $request->otp)
            ->where('is_used', false)
            ->first();

        if (!$userOtp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        $user->email_verified_at = now();
        $user->save();

        $userOtp->is_used = true;
        $userOtp->save();

        return response()->json(['message' => 'Email verified successfully']);
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->email_verified_at) {
            return response()->json(['message' => 'Please verify your email first'], 403);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json(['token' => $token]);
    }
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->firstOrFail();

        $otp = rand(100000, 999999);
        UserOtp::create([
            'user_id' => $user->id,
            'otp' => $otp,
        ]);

        Mail::raw("Your password reset code is: $otp", function ($message) use ($user) {
            $message->to($user->email)->subject('Reset Password');
        });

        return response()->json(['message' => 'OTP sent']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
            'password' => 'required|min:6'
        ]);

        $user = User::where('email', $request->email)->firstOrFail();
        $userOtp = UserOtp::where('user_id', $user->id)
            ->where('otp', $request->otp)
            ->where('is_used', false)
            ->first();

        if (!$userOtp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        $userOtp->is_used = true;
        $userOtp->save();

        return response()->json(['message' => 'Password reset successfully']);
    }
}
