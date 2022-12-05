<?php

namespace App\Http\Controllers\v3;

use App\Http\Resources\AuthorCollection;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AuthorController extends Controller
{
    const AuthorPerPage = 15;
    const CachePagePeriod = 25;

    public function index(Request $request)
    {
        $query = Author::orderBy('id');
        if ($request->input('page') !== null)
            $authors = $query->paginate();
        else
            $authors = $query->get(['id', 'name']);

        return new AuthorCollection($authors);
    }

    public function show(Request $request, $id)
    {
        $author = Author::getById($id);

        return new AuthorResource($author);
    }

    public function create(Request $request)
    {
        $this->validateCreateAuthorValidation($request);

        $author = Author::create([
            'name' => $request->input('name')
        ]);

        return response()->json([
            'message' => 'author created successfully',
            'data' => $author
        ]);
    }

    private function validateCreateAuthorValidation($request)
    {
        $this->validate($request, [
            'name' => 'string|required|min:3|max:60|unique:authors,name'
        ]);
    }

    public function update(Request $request, $id = null)
    {
        $this->validateUpdateAuthorValidation($request, $id);

        $author = Author::getById($id);

        $author->update($request->only(['name']));

        return response()->json([
            'message' => 'author updated successfully'
        ]);
    }

    private function validateUpdateAuthorValidation($request, $id)
    {
        $this->validate($request, [
            'name' => 'string|required|min:3|max:60|unique:authors,name,' . $id
        ]);
    }

    public function delete(Request $request, $id = null)
    {
        $author = Author::getById($id);

        $author->delete();

        return response()->json([
            'message' => 'author deleted successfully'
        ]);
    }
}
