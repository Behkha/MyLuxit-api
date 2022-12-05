<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $this->validateRegisterRequest($request);
        $user = User::create([
            'name' => $request->name,
            'email' => ($request->has('email') && $request->email) ? $request->email : null,
            'tell' => ($request->has('tell') && $request->tell) ? $request->tell : null,
            'password' => Hash::make($request->password)
        ]);

        $token = Auth::login($user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }


    private function validateRegisterRequest($request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:50',
            'email' => 'required_without:tell|email|unique:users,email',
            'tell' => [
                'required_without:email',
                'digits:11',
                'unique:users,tell'
            ],
            'password' => 'string|min:6|max:32'
        ]);
    }
}
