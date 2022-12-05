<?php

namespace App\Http\Controllers\v1;

use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Psy\Util\Json;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::whereNotNull('position')->orderBy('position')->get(['id', 'image', 'name', 'position']);

        return response()->json($categories);
    }

    public function create(Request $request)
    {
        $this->validateCreateCategoryRequest($request);

        $imageHashName = FileController::generateImageFileHashName($request->image);
        $image = FileController::manipulateImageFile($request->image, 500, 500, 75);
        $imagePath = FileController::uploadImageFile($image, $imageHashName, 'categories/default', 'jpg');

        $category = Category::create([
            'name' => $request->name,
            'image' => $imagePath,
            'tags' => json_decode($request->tags),
            'position' => $request->has('position') && $request->position ? $request->position : null,
            'event_filters' => json_decode($request->event_filters),
            'place_filters' => json_decode($request->place_filters)
        ]);

        $category->index();
        Category::deleteCategoriesIndexCache();
        return response()->json($category->id);
    }

    private function validateCreateCategoryRequest($request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'image' => 'file|required',
            'tags' => 'json|nullable',
            'position' => 'numeric|nullable',
            'event_filters' => 'json|nullable',
            'place_filters' => 'json|nullable'
        ]);
    }

    public function show(Request $request, $id)
    {
        $page = $this->paramList->getPage();
        $data = Category::getWithItems($request, $id, $page);
        $items = $data['items'];

        return response()->json($items);
    }
}
