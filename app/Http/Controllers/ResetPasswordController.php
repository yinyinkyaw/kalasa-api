<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

class ResetPasswordController extends Controller
{
    // get password reset link
    public function sendPasswordResetLink(Request $request) {
        $validated = $request->validate([
            'email' => 'required|exists:users,email'
        ]);

        // send password reset lnik
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => 'success',
                'message' => 'Password reset link sent successfully.'
            ]);
        }

        return response()->json([
            'status' => 'failed',
            'message' => 'Failed sending password reset link'
        ]);
    }

    // reset password
    public function resetPassword(Request $request) {
        $validated = $request->validate([
            'token' => 'required',
            'email' => 'required|exists:users,email',
            'password' => 'required|min:8|confirmed'
        ]);

        // reset password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if($status === Password::PASSWORD_RESET) {
            return response()->json([
                'status' => 'success',
                'message' => 'Password reset successful.'
            ]);
        }

        return response()->json([
            'status' => 'failed',
            'message' => 'Password reset failed.'
        ]);
    }
}
