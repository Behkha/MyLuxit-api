<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function self(Request $request){
        $admin = Auth::guard('admin')->user();
        return response()->json($admin);
    }
}
