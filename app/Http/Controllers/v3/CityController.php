<?php

namespace App\Http\Controllers\v3;

use App\Http\Resources\CityResource;
use App\Models\City;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\CityCollection;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $cities = City::with('province:id,name')
            ->active()
            ->orderBy('cities.name')
            ->get(['id', 'name', 'province_id', 'image']);

        return CityResource::collection($cities);
    }

    public function create(Request $request)
    {
        $this->validateCreateCityRequest($request);
        $city = City::updateOrCreate([
            'name' => $request->input('name'),
            'is_active' => true,
        ], [
            'province_id' => $request->input('province_id'),
            'image' => ''
        ]);
        return new CityResource($city);
    }

    private function validateCreateCityRequest(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'province_id' => 'required|integer|exists:provinces,id'
        ]);
    }

    public function update(Request $request, $id = null)
    {
        $this->validateUpdateCityValidation($request, $id);

        $city = City::findOrFail($id);

        $city->update([
            'name' => $request->input('name'),
            'province_id' => $request->input('province_id')
        ]);

        return response()->json([
            'message' => 'city updated successfully'
        ]);

    }

    private function validateUpdateCityValidation($request, $id)
    {
        $this->validate($request, [
            'name' => 'string|required|min:3|max:30|unique:tags,name,' . $id,
            'province_id' => 'required|integer|exists:provinces,id'
        ]);
    }

    function search(Request $request)
    {
        $this->validateSearchRequest($request);
        $tags = City::search($request->input('keyword'))
            ->get();

        return new CityCollection($tags);
    }

    private function validateSearchRequest($request)
    {
        $this->validate($request, [
            'keyword' => 'required|string|max:255'
        ]);
    }
}
