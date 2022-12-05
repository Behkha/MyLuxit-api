<?php

namespace App\Http\Controllers\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{

    public function __construct(Request $request)
    {
    }

    public function login(Request $request)
    {
        $this->validateLoginRequest($request);

        if ($request->has('tell'))
            $credentials = ['password'=>$request->password,'tell'=>$request->tell];
        else
            $credentials = ['password'=>$request->password,'email'=>$request->email];

        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }
    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }

    public function loginAdmin(Request $request){
        $this->validateLoginAdminRequest($request);
        $credentials = ['password'=>$request->password,'username'=>$request->username];
        if(! $token = Auth::guard('admin')->attempt($credentials))
            return response()->json(['error'=>'unauthorized'],401);

        return $this->respondWithToken($token);
    }
    public function logoutAdmin(Request $request){
        Auth::logout();
        return response()->json(['message' => 'Successfully logged out']);
    }


    private function validateLoginRequest(Request $request){
        $this->validate($request,[
            'tell'=>'required_without:email|digits:11',
            'email'=>'required_without:tell|email',
            'password'=>'required'
        ]);
    }
    private function validateLoginAdminRequest(Request $request){
        $this->validate($request,[
            'username'=>'required',
            'password'=>'required'
        ]);
    }
}