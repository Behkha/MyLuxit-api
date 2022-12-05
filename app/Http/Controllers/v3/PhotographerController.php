<?php

namespace App\Http\Controllers\v3;

use App\Http\Controllers\Controller;
use App\Http\Resources\PhotographerCollection;
use App\Http\Resources\PhotographerResource;
use App\Models\Photographer;
use Illuminate\Http\Request;

class PhotographerController extends Controller
{
    const PhotographerPerPage = 25;

    public function index(Request $request)
    {
        $query = Photographer::orderBy('id');
        if ($request->input('page') !== null)
            $photographers = $query->paginate();
        else
            $photographers = $query->get(['id', 'name']);

        return new PhotographerCollection($photographers);
    }

    public function show(Request $request, $id)
    {
        $photographer = Photographer::findOrFail($id);

        return new PhotographerResource($photographer);
    }

    public function create(Request $request)
    {
        $this->validateCreatePhotographerValidation($request);

        $photographer = Photographer::create($request->only([
            'name'
        ]));

        return response()->json([
            'message' => 'photographer created successfully',
            'data' => $photographer
        ]);
    }

    private function validateCreatePhotographerValidation($request)
    {
        $this->validate($request, [
            'name' => 'string|required|min:3|max:60|unique:photographers,name'
        ]);
    }

    public function update(Request $request, $id = null)
    {
        $this->validateUpdatePhotographerValidation($request, $id);

        $photographer = Photographer::findOrFail($id);

        $photographer->update($request->only(['name']));

        return response()->json([
            'message' => 'photographer updated successfully'
        ]);
    }

    private function validateUpdatePhotographerValidation(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'string|required|min:3|max:60|unique:photographers,name,' . $id
        ]);
    }

    public function delete(Request $request, $id = null)
    {
        $photographer = Photographer::findOrFail($id);

        $photographer->delete();

        return response()->json([
            'message' => 'photographer deleted successfully'
        ]);
    }
}