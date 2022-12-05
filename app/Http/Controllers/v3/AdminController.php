<?php

namespace App\Http\Controllers\v3;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminCollection;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AdminController extends Controller
{
    public function index(Request $request)
    {
//        if (Auth::guard('admin')->user()->cannot('index-admins')) {
//            return response()->json([
//                'can\'t index admins'
//            ], 403);
//        }

        $admins = Admin::paginate();

        return new AdminCollection($admins);
    }

    public function create(Request $request)
    {
        $this->validateCreateAdminRequest($request);

//        $privileges = $request->input('privileges');

//        foreach ($privileges as $key => $value)
//            $privileges[$key] = intval($value);

        $data = [
            'name' => $request->input('name'),
            'username' => $request->input('username'),
            'password' => Hash::make($request->input('password')),
            'privileges' => []
        ];

        $admin = Admin::create($data);

        return response()->json([
            'message' => 'admin created successfully'
        ]);
    }

    private function validateCreateAdminRequest(Request $request)
    {

        $admin = Auth::guard('admin')->user();

//        if ($admin->cant('create', Admin::class)) {
//            throw new UnauthorizedHttpException('Unauthorized');
//        }

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:admins',
            'password' => 'required|string|min:5|max:255|confirmed',
//            'privileges' => 'required|array',
//            'privileges.*' => Rule::in(Admin::PRIVILEGES)
        ]);
    }

    public function update(Request $request, $id = null)
    {
        $admin = Admin::findOrFail($id);

        $this->validateUpdateAdminRequest($request, $admin);

//        $privileges = $request->input('privileges');
//
//        foreach ($privileges as $key => $value)
//            $privileges[$key] = intval($value);

        $data = [
            'name' => $request->input('name'),
            'username' => $request->input('username'),
            'privileges' => []
        ];

        if ($request->has('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $admin->update($data);

        return response()->json([
            'message' => 'admin updated successfully'
        ]);
    }

    private function validateUpdateAdminRequest(Request $request, Admin $target_admin)
    {
        $admin = Auth::guard('admin')->user();

//        if ($admin->cant('update', Admin::class)) {
//            throw new UnauthorizedHttpException('Unauthorized');
//        }

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:admins,username,' . $target_admin->id,
            'password' => 'string|min:5|max:255|confirmed',
//            'privileges' => 'required|array',
//            'privileges.*' => Rule::in(Admin::PRIVILEGES)
        ]);
    }

    public function delete(Request $request, $id = null)
    {
        $admin = Admin::findOrFail($id);
//        if (Auth::guard('admin')->user()->cant('delete', Admin::class)) {
//            throw new UnauthorizedHttpException('Unauthorized');
//        }

        $admin->delete();

        return response()->json([
            'message' => 'Admin deleted successfully'
        ]);
    }

    public function self(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        return new AdminResource($admin);
    }

    public function show(Request $request, $id = null)
    {
        $admin = Admin::findOrFail($id);
        return new AdminResource($admin);
    }
}
