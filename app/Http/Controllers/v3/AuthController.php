<?php

namespace App\Http\Controllers\v3;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function sendActivationCode(Request $request)
    {
        $this->validateSendActivationCode($request);
        $user = User::whereTell($request->input('tell'))->firstOrFail();
        $user->updateActivationCode();
        $user->sendActivationCodeSMS();

        return response()->json([
            'message' => 'activation code sent to ' . $user->tell
        ]);
    }

    private function validateSendActivationCode(Request $request)
    {
        $this->validate($request, [
            'tell' => [
                'required',
                'digits:11',
                Rule::exists('users', 'tell')->where(function ($query) {
                    $query->whereNull('deleted_at');
                }),
            ]
        ]);
    }

    public function login(Request $request)
    {
        $result = $this->validateLoginRequest($request);

        if ($result !== true)
            return $result;

        $user = User::whereTell($request->input('tell'))->firstOrFail();

        $user->update([
            'activation_code' => null
        ]);

        $token = Auth::login($user);

        return response()->json([
            'message' => 'user logged in successfully',
            'token' => $token
        ]);
    }

    private function validateLoginRequest(Request $request)
    {
        $this->validate($request, [
            'tell' => [
                'required',
                'digits:11',
                Rule::exists('users', 'tell')->where(function ($query) {
                    $query->whereNull('deleted_at');
                }),
            ],
            'code' => [
                'required',
                Rule::exists('users', 'activation_code')->where(function ($query) {
                    $query->whereNull('deleted_at');
                })
            ]
        ]);


        $user = User::whereTell($request->input('tell'))->firstOrFail();

        if ($user->activation_code !== $request->input('code')) {
            return response()->json([
                'message' => 'code and tell do not match'
            ], 422);
        }

        return true;
    }

    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function loginAdmin(Request $request)
    {
        $this->validateLoginAdminRequest($request);
        $credentials = ['password' => $request->password, 'username' => $request->username];
        if (!$token = Auth::guard('admin')->attempt($credentials))
            return response()->json(['error' => 'unauthorized'], 401);

        return $this->respondWithToken($token);
    }

    private function validateLoginAdminRequest(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required'
        ]);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }

    public function logoutAdmin(Request $request)
    {
        Auth::logout();
        return response()->json(['message' => 'successfully logged out']);
    }
}