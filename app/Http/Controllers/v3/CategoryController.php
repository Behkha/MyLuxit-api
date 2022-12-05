<?php

namespace App\Http\Controllers\v3;

use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\PostableCollection;
use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Event;
use App\Models\Language;
use App\Models\Place;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Psy\Util\Json;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::whereNotNull('position')
            ->with(['language'])
            ->language($request->input('language.id'))
            ->orderBy('position');


        if ($request->filled('depth')) {
            $query->where('depth', $request->input('depth'));
        } else {
            $query->topLevel();
        }


        if ($request->input('page') === null) {
            $categories = $query->get(['id', 'image', 'name', 'position', 'language_id']);
        } else {
            $categories = $query->paginate();
        }

        return new CategoryCollection($categories);
    }

    public function indexNearests(Request $request, $id = null)
    {
        $this->validateIndexNearestsRequest($request);
        $category = Category::findOrFail($id);
        $places_ids = $category->getAllFilteredPlaces();
        $query = Place::with(['type', 'tags'])
            ->withCount(['events'])
            ->language($request->input('language.id'))
            ->whereRaw("ST_DWithin(geo_location, ST_MakePoint($request->longitude,$request->latitude)::geography, $request->radius)")
            ->whereIn('id', $places_ids);


        if ($request->input('city_id')) {
            $query->whereHas('cities', function ($q) use ($request) {
                $q->where('city_id', $request->input('city_id'));
            });
        }

        $places = $query->get();

        return response()->json(PlaceResource::collection($places));
    }

    private function validateIndexNearestsRequest(Request $request)
    {
        $this->validate($request, [
            'latitude' => ['required', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'longitude' => ['required', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
            'radius' => 'required|numeric|max:30000',
            'city_id' => 'integer|exists:cities,id'
        ]);
    }

    public function create(Request $request)
    {
        $this->validateCreateCategoryRequest($request);

        $imageHashName = FileController::generateImageFileHashName($request->file('image'));
        $image = FileController::manipulateImageFile($request->file('image'), 500, 500, 75);
        $imagePath = FileController::uploadImageFile($image, $imageHashName, 'categories/default/', 'jpg');

        $place_filters = [
            'tags' => [],
            'types' => [],
        ];
        $event_filters = [
            'tags' => [],
            'types' => []
        ];
        $tags = [];

        foreach ($request->input('event_filters.tags', []) as $eventTag) {
            if (!in_array($eventTag, $event_filters['tags'])) {
                array_push($event_filters['tags'], $eventTag);
            }
        }

        foreach ($request->input('event_filters.types', []) as $eventType) {
            if (!in_array($eventType, $event_filters['types'])) {
                array_push($event_filters['types'], $eventType);
            }
        }

        foreach ($request->input('place_filters.tags', []) as $placeFilter) {
            if (!in_array($placeFilter, $place_filters['tags'])) {
                array_push($place_filters['tags'], $placeFilter);
            }
        }

        foreach ($request->input('place_filters.types', []) as $placeFilter) {
            if (!in_array($placeFilter, $place_filters['types'])) {
                array_push($place_filters['types'], $placeFilter);
            }
        }

        foreach ($request->input('tags', []) as $tag) {
            if (!in_array($tag, $tags)) {
                array_push($tags, $tag);
            }
        }

        if ($request->input('parent_id')) {
            $parentCategory = Category::findOrFail($request->input('parent_id'));
        }


        $category = Category::create([
            'name' => $request->input('name'),
            'image' => $imagePath,
            'tags' => $tags,
            'position' => $request->input('position') ?: null,
            'event_filters' => $event_filters,
            'place_filters' => $place_filters,
            'parent_id' => isset($parentCategory) ? $parentCategory->id : null,
            'depth' => isset($parentCategory) ? $parentCategory->depth + 1 : 0,
            'language_id' => $request->input('language_id') ?: Language::getDefaultLanguage()->id
        ]);
        $category->index();

        return new CategoryResource($category);
    }

    private function validateCreateCategoryRequest(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'image' => 'file|required',
            'tags' => 'array',
            'position' => 'numeric',
            'event_filters' => 'array',
            'place_filters' => 'array',
            'event_filters.types.*' => 'integer',
            'event_filters.tags.*' => 'integer',
            'place_filters.types.*' => 'integer',
            'place_filters.tags.*' => 'integer',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'language_id' => 'integer|exists:languages,id'
        ]);

    }

    public function show(Request $request, $id = null)
    {
        $this->validate($request, [
            'city_id' => 'integer | exists:cities,id'
        ]);
        $page = $this->paramList->getPage();
        $mashhad = City::where('name', 'مشهد')->first();

        if ($request->input('city_id')) {
            $city_id = $request->input('city_id');
//        } else if ($mashhad) {
//            $city_id = $mashhad->id;
        } else {
            $city_id = null;
        }
        $data = Category::getWithItems($request, $id, $page, $city_id);

        $category = $data['category'];
        $items = $data['items'];

        if (Auth::check())
            $category->incrementVisits($request);


        $itemsCollection = new PostableCollection(collect($items));

        $response = [
            'items' => $itemsCollection,
            'category' => $category
        ];

        if (Auth::guard('admin')->check()) {
            $response = array_merge($response, [
                'visits' => [
                    'dates' => $category->getVisitsFromCache(),
                    'total' => $category->getVisitsCountFromCache()
                ],
                'average_ratings' => $category->getAverageRatingFromCache()
            ]);
        }

        return response()->json($response);
    }

    public function update(Request $request, $id = null)
    {

        $this->validateUpdateCategoryRequest($request);

        $category = Category::findOrFail($id);
        $position = $request->input('position', 1);

        $place_filters = [
            'tags' => [],
            'types' => [],
        ];
        $event_filters = [
            'tags' => [],
            'types' => []
        ];
        $tags = [];


//        if (is_array($category->event_filters) && in_array('tags', $category->event_filters) && in_array('types', $category->event_filters)) {
//            $event_filters = $category->event_filters;
//        }
//        if (is_array($category->place_filters) && in_array('tags', $category->place_filters) && in_array('types', $category->place_filters)) {
//            $place_filters = $category->place_filters;
//        }
//
//        if (is_array($category->tags)) {
//            $tags = $category->tags;
//        }

        foreach ($request->input('event_filters.tags', []) as $eventTag) {
            if (!in_array($eventTag, $event_filters['tags'])) {
                array_push($event_filters['tags'], $eventTag);
            }
        }

        foreach ($request->input('event_filters.types', []) as $eventType) {
            if (!in_array($eventType, $event_filters['types'])) {
                array_push($event_filters['types'], $eventType);
            }
        }

        foreach ($request->input('place_filters.tags', []) as $placeFilter) {
            if (!in_array($placeFilter, $place_filters['tags'])) {
                array_push($place_filters['tags'], $placeFilter);
            }
        }

        foreach ($request->input('place_filters.types', []) as $placeFilter) {
            if (!in_array($placeFilter, $place_filters['types'])) {
                array_push($place_filters['types'], $placeFilter);
            }
        }

        foreach ($request->input('tags', []) as $tag) {
            if (!in_array($tag, $tags)) {
                array_push($tags, $tag);
            }
        }

        if ($request->input('parent_id')) {
            $parnetCategory = Category::findOrFail($request->input('parent_id'));
        }

        $category->update([
            'tags' => $tags,
            'place_filters' => $place_filters,
            'event_filters' => $event_filters,
            'name' => $request->input('name'),
            'position' => $position,
            'parent_id' => isset($parnetCategory) ? $parnetCategory->id : null,
            'depth' => isset($parnetCategory) ? $parnetCategory->depth + 1 : 0,
            'language_id' => $request->input('language_id') ?: Language::getDefaultLanguage()->id
        ]);

        return response()->json([
            'message' => 'ok'
        ]);
    }

    private function validateUpdateCategoryRequest(Request $request)
    {
        $this->validate($request, [
            'name' => 'required | string',
            'tags' => 'array',
            'position' => 'numeric',
            'event_filters' => 'array',
            'place_filters' => 'array',
            'parent_id' => 'nullable|integer|exists:categories,id,depth,0'
        ]);


//        if ($request->input('event_filters')) {
//            $this->validate($request, [
//                'event_filters . types' => 'required_without:event_filters . tags | array',
//                'event_filters . types .*' => 'exists:event_types,id',
//                'event_filters . tags' => 'required_without:event_filters . types | array',
//                'event_filters . tags .*' => 'exists:tags,id',
//            ]);
//        }
//        if ($request->input('place_filters')) {
//            $this->validate($request, [
//                'place_filters . types' => 'required_without:place_filters . tags | array',
//                'place_filters . types .*' => 'exists:place_types,id',
//                'place_filters . tags' => 'required_without:place_filters . types | array',
//                'place_filters . tags .*' => 'exists:tags,id',
//            ]);
//        }
    }

    public function updateImage(Request $request, $id = null)
    {
        $this->validateUpdateImageRequest($request);

        $category = Category::findOrFail($id);

        $imageHashName = FileController::generateImageFileHashName($request->file('image'));
        $image = FileController::manipulateImageFile($request->image, 500, 500, 75);
        $path = FileController::uploadImageFile($image, $imageHashName, 'categories/default/', 'jpg');

        $category->update([
            'image' => $path
        ]);

        return new CategoryResource($category);
    }

    private function validateUpdateImageRequest(Request $request)
    {
        $this->validate($request, [
            'image' => 'file | required'
        ]);
    }

    public function delete(Request $request, $id = null)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json([
            'message' => 'ok'
        ]);
    }

    public function indexSubcategories(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $query = $category->subcategories();

        if ($request->input('page')) {
            $categories = $query->paginate();
        } else {
            $categories = $query->get();
        }

        return CategoryResource::collection($categories);
    }
}
