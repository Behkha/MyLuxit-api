<?php

namespace App\Http\Controllers\v3;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProvinceCollection;
use App\Http\Resources\ProvinceResource;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProvinceController extends Controller
{
    const ProvincePerPage = 25;
    const CachePagePeriod = 50;
    const CacheItemPeriod = 30;

    public function index(Request $request)
    {
        $query = Province::orderBy('id', 'desc');
        if ($request->input('page') !== null)
            $provinces = $query->paginate();
        else {
            $provinces = $query->get();
        }

        return new ProvinceCollection($provinces);
    }

    public function show(Request $request, $id)
    {
        $province = Province::findOrFail($id);

        return new ProvinceResource($province);
    }

    public function create(Request $request)
    {
        $this->validateCreateProvinceValidation($request);

        $province = Province::create([
            'name' => $request->input('name')
        ]);

        return response()->json([
            'message' => 'province created successfully',
            'data' => new ProvinceResource($province)
        ]);
    }

    private function validateCreateProvinceValidation($request)
    {
        $this->validate($request, [
            'name' => 'string|required|min:1|max:30|unique:provinces,name'
        ]);
    }

    public function update(Request $request, $id = null)
    {
        $this->validateUpdateProvinceValidation($request, $id);

        $province = Province::findOrFail($id);

        $province->update($request->only(['name']));

        return response()->json([
            'message' => 'province updated successfully'
        ]);

    }

    private function validateUpdateProvinceValidation($request, $id)
    {
        $this->validate($request, [
            'name' => 'string|required|min:1|max:30|unique:provinces,name,' . $id
        ]);
    }

    function search(Request $request)
    {
        $this->validateSearchRequest($request);
        $provinces = Province::search($request->input('keyword'))
            ->get();

        return new ProvinceCollection($provinces);
    }

    private function validateSearchRequest($request)
    {
        $this->validate($request, [
            'keyword' => 'required|string|max:255'
        ]);
    }

    public function delete(Request $request, $id = null)
    {
        $province = Province::findOrFail($id);
        $province->delete();

        return response()->json([
            'message' => 'province deleted successfully'
        ]);
    }
}
