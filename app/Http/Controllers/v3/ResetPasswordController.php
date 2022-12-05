<?php

namespace App\Http\Controllers\v3;

use App\Models\ResetPassword;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{

    public function create(Request $request)
    {
        $this->validateResetPasswordRequest($request);

        if ($request->has('tell')) {
            $user = User::whereTell($request->tell)->firstOrFail();
            $data = ['user_id' => $user->id];
        } else {
            $user = User::whereEmail($request->email)->firstOrFail();
            $data = ['user_id' => $user->id];
        }

        // TODO: Generate better random token
        $data['reset_token'] = rand(1000, 9999);

        // TODO: Send SMS/Email
        ResetPassword::create($data);

        return response()->json([
            'message' => 'reset token sent.'
        ]);
    }

    private function validateResetPasswordRequest($request)
    {
        $this->validate($request, [
            'tell' => 'digits:11|exists:users'
        ]);
    }

    public function tokenValidation(Request $request)
    {
        $this->validate($request, [
            'email' => 'required_without:tell|email|exists:users',
            'tell' => [
                'required_without:email',
                'digits:11',
                'exists:users'
            ],
            'reset_token' => 'required|exists:reset_passwords'
        ]);

        if ($request->has('tell')) {
            $user = User::whereTell($request->tell)->firstOrFail();
        } else {
            $user = User::whereEmail($request->email)->firstOrFail();
        }
        $reset = ResetPassword::whereResetToken($request->reset_token)->firstOrFail();

        if ($user->id !== $reset->user_id) {
            return response()->json([
                'message' => 'User credentials does not match reset token'
            ], 422);
        }

        return response()->json([
            'message' => 'Reset token confirmed'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $result = $this->validateResetRequest($request);

        if ($result !== true) {
            return $result;
        }

        if ($request->has('tell')) {
            $user = User::whereTell($request->tell)->firstOrFail();
        } else {
            $user = User::whereEmail($request->email)->firstOrFail();
        }

        $reset = ResetPassword::whereResetToken($request->reset_token)->firstOrFail();
        $reset->delete();

        $user->update(['password' => Hash::make($request->new_password)]);

        $token = Auth::login($user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }

    private function validateResetRequest(Request $request)
    {
        $this->validate($request, [
            'email' => 'required_without:tell|email|exists:users',
            'tell' => [
                'required_without:email',
                'digits:11',
                'exists:users'
            ],
            'reset_token' => 'required|exists:reset_passwords',
            'new_password' => 'required|confirmed|min:6|max:32'
        ]);

        if ($request->has('tell')) {
            $user = User::whereTell($request->tell)->firstOrFail();
        } else {
            $user = User::whereEmail($request->email)->firstOrFail();
        }
        $reset = ResetPassword::whereResetToken($request->reset_token)->firstOrFail();

        if ($user->id !== $reset->user_id) {
            return response()->json([
                'message' => 'User credentials does not match reset token'
            ], 422);
        }

        return true;
    }
}
