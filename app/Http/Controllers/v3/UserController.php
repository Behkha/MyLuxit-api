<?php

namespace App\Http\Controllers\v3;

use App\Http\Controllers\Controller;
use App\Http\Resources\CarResource;
use App\Http\Resources\UserResource;
use App\Jobs\SendSMS;
use App\Models\Referral;
use App\Models\User;
use App\Models\UserReferral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->input('pending_avatar')) {
            $query->where('is_profile_picture_accepted', true);
        }

        if ($request->input('page')) {
            $users = $query->paginate();
        } else {
            $users = $query->get();
        }

        return UserResource::collection($users);
    }

    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);

        return new UserResource($user);
    }

    public function register(Request $request)
    {
        $this->validateRegisterUserRequest($request);

        if (User::where('tell', $request->input('tell'))->exists()) {
            $user = User::where('tell', $request->input('tell'))->firstOrFail();
            $user->updateActivationCode();
        } else {
            $user = User::create([
                'status_id' => User::STATUSES['incomplete'],
                'tell' => $request->input('tell')
            ]);
        }

        $user->sendActivationCodeSMS();


        return response()->json([
            'message' => 'user registered successfully',
        ]);
    }

    private function validateRegisterUserRequest(Request $request)
    {
        $this->validate($request, [
            'tell' => 'required|digits:11'
        ]);
    }

    public function confirm(Request $request)
    {
        $this->validateConfirmRequest($request);

        $user = User::whereTell($request->input('tell'))->firstOrFail();

        $user->update([
            'activation_code' => null
        ]);

        $token = Auth::login($user);
        $needs_completion = ($user->status_id === User::STATUSES['incomplete']) ?
            true : false;
        return response()->json([
            'message' => 'tell confirmed successfully',
            'token' => $token,
            'needs_completion' => $needs_completion
        ]);
    }

    private function validateConfirmRequest(Request $request)
    {
        $this->validate($request, [
            'tell' => [
                'required',
                Rule::exists('users', 'tell')->where(function ($query) {
                    $query->whereNull('deleted_at');
                })
            ],
            'code' => [
                'required',
                Rule::exists('users', 'activation_code')->where(function ($query) {
                    $query->whereNull('deleted_at');
                })
            ]
        ]);

        $user = User::whereTell($request->input('tell'))->firstOrFail();

        $this->validate($request, [
            'code' => 'in:' . $user->activation_code
        ]);

    }

    public function update(Request $request)
    {
        $this->validateUpdateRequest($request);

        $user = Auth::user();

        DB::beginTransaction();

        if ($request->has('referral_code') && $user->status_id === User::STATUSES['incomplete']) {
            $referral = Referral::whereCode($request->input('referral_code'))->firstOrFail();

            if ($referral->status_id !== Referral::STATUSES['active']) {
                return response()->json([
                    'message' => 'referral code is not active'
                ], 403);
            }

            $user->referral()->updateOrCreate([
                'referral_id' => $referral->id
            ]);
        }

        $user->update([
            'gender' => $request->input('gender_id'),
            'name' => $request->input('name'),
            'city_id' => $request->input('city_id'),
            'status_id' => User::STATUSES['active']
        ]);

        DB::commit();

        return response()->json([
            'message' => 'user updated successfully',
        ]);
    }

    private function validateUpdateRequest($request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'gender_id' => ['required', Rule::in(User::GENDERS)],
            'referral_code' => 'exists:referrals,code'
        ]);
    }

    public function acceptAvatar(Request $request, $id)
    {
        $this->validate($request, [
            'action' => 'required|in:confirm,reject'
        ]);
        $user = User::findOrFail($id);
        $user->update([
            'is_profile_picture_accepted' => $request->input('action') == 'confirm'
        ]);

        return response()->json([
            'status' => 'ok'
        ]);
    }

    public function showSelf(Request $request)
    {
        return new UserResource(Auth::user()->load('city', 'city.province'));
    }

    public function removeProfilePicture(Request $request)
    {
        $user = Auth::guard('user')->user();
        $user->update([
            'profile_picture' => null
        ]);

        return response()->json([
            'status' => 'ok'
        ]);
    }
}
