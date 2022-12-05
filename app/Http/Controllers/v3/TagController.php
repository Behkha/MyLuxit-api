<?php

namespace App\Http\Controllers\v3;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagCollection;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TagController extends Controller
{
    const TagPerPage = 25;
    const CachePagePeriod = 50;
    const CacheItemPeriod = 30;

    public function index(Request $request)
    {
        $query = Tag::orderBy('id', 'desc');
        if ($request->input('page') !== null)
            $tags = $query->paginate();
        else {
            $tags = $query->get();
        }

        return new TagCollection($tags);
    }

    public function show(Request $request, $id)
    {
        $tag = Tag::findOrFail($id);

        return new TagResource($tag);
    }

    public function create(Request $request)
    {
        $this->validateCreateTagValidation($request);

        $tag = Tag::create([
            'name' => $request->input('name')
        ]);

        return response()->json([
            'message' => 'tag created successfully',
            'data' => new TagResource($tag)
        ]);
    }

    private function validateCreateTagValidation($request)
    {
        $this->validate($request, [
            'name' => 'string|required|min:3|max:30|unique:tags,name'
        ]);
    }

    public function update(Request $request, $id = null)
    {
        $this->validateUpdateTagValidation($request, $id);

        $tag = Tag::findOrFail($id);

        $tag->update($request->only(['name']));

        return response()->json([
            'message' => 'tag updated successfully'
        ]);

    }

    private function validateUpdateTagValidation($request, $id)
    {
        $this->validate($request, [
            'name' => 'string|required|min:3|max:30|unique:tags,name,' . $id
        ]);
    }

    function search(Request $request)
    {
        $this->validateSearchRequest($request);
        $tags = Tag::search($request->input('keyword'))
            ->get();

        return new TagCollection($tags);
    }

    private function validateSearchRequest($request)
    {
        $this->validate($request, [
            'keyword' => 'required|string|max:255'
        ]);
    }

    public function delete(Request $request, $id = null)
    {
        $tag = Tag::findOrFail($id);
        $tag->delete();

        return response()->json([
            'message' => 'tag deleted successfully'
        ]);
    }
}
