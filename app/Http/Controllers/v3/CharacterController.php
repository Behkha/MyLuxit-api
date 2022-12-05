<?php

namespace App\Http\Controllers\v3;

use App\Http\Resources\CharacterCollection;
use App\Http\Resources\CharacterTypeResource;
use App\Models\Character;
use App\Models\CharacterType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CharacterController extends Controller
{
    public function index(Request $request)
    {
        $query = Character::with(['character'])
            ->orderBy('id', 'desc');
        if ($request->input('page')) {
            $chars = $query->paginate();
        } else {
            $chars = $query->get();
        }

        return new CharacterCollection($chars);
    }

    public function indexTypes(Request $request)
    {
        if ($request->input('page')) {
            $types = CharacterType::orderBy('id', 'desc')->paginate();
        } else {
            $types = CharacterType::orderBy('id', 'desc')->get();
        }
        return CharacterTypeResource::collection($types);
    }

    public function createType(Request $request)
    {
        $this->validateCreateTypeRequest($request);
        $type = CharacterType::create([
            'title' => $request->input('title')
        ]);

        return response()->json([
            'message' => 'ok',
            'data' => $type
        ]);
    }

    private function validateCreateTypeRequest(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string|max:255'
        ]);
    }

    public function showType(Request $request, $id = null)
    {
        $type = CharacterType::findOrFail($id);
        return new CharacterTypeResource($type);
    }

    public function deleteType(Request $request, $id = null)
    {
        $type = CharacterType::findOrFail($id);
        $type->delete();

        return response()->json([
            'message' => 'character type deleted successfully'
        ]);
    }

    public function updateType(Request $request, $id = null)
    {
        $this->validateCreateTypeRequest($request);

        $type = CharacterType::findOrFail($id);
        $type->update([
            'title' => $request->input('title')
        ]);

        return response()->json([
            'message' => 'character type updated successfully'
        ]);
    }

}
