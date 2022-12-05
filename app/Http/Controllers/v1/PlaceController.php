<?php

namespace App\Http\Controllers\v1;

use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Models\PlaceType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class PlaceController extends Controller
{
    const PlacePerPage = 25;

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function index()
    {
        $places = Cache::tags(['Place', 'PlacePage'])->remember('Places:Page:' . $this->paramList->getPage(), Place::CachePagePeriod, function () {
            return Place::paginate(self::PlacePerPage);
        });
        return response()->json($places);
    }

    public function show(Request $request, $id)
    {
        $place = Place::getById($id);
        return response()->json($place);
    }

    public function create(Request $request)
    {
        $this->validateCreatePlaceValidation($request);

        $place = Place::create([
            'name' => $request->name,
            'content' => $request->get('content'),
            'address' => $request->address,
            'location' => ($request->has('location') && $request->location) ? $request->location : null,
            'city_id' => $request->city_id,
            'admin_id' => Auth::guard('admin')->id(),
            'type_id' => $request->type_id,
            'media' => $request->media,
            'links' => ($request->links['website']['link']) ? $request->links : null
        ]);

        $place->tags()->attach($request->tags);
        $place->authors()->attach($request->authors);
        $place->photographers()->attach($request->photographers);

        foreach ($request->media as $image) {
            Redis::lrem(config('cache.prefix').'uploadedImages', 0, $image['path']);
        }

        Place::flushPagesCache();
        Category::indexRelatedCategoriesTo($place, $request->tags);
        return response()->json($place);
    }

    private function validateCreatePlaceValidation($request)
    {
        $this->validate($request, [
            'name' => 'required|string|min:3|max:60',
            'content' => 'required|string',
            'address' => 'required|string',
            'location' => 'string',
            'city_id' => 'required|numeric|integer|min:1|exists:cities,id',
            'type_id' => 'required|numeric|integer|min:1|exists:place_types,id',
            'photographers' => 'array',
            'authors' => 'array',
            'tags' => 'array',
            'media' => 'array',
            'links' => 'array'
        ]);
    }

    public function search(Request $request)
    {
        $tags = Place::where('name', 'ilike', '%' . $request->keyword . '%')->get(['name', 'id']);
        return response()->json($tags);
    }

    public function indexTypes()
    {
        $placeTypes = PlaceType::all(['name', 'id']);
        return response()->json($placeTypes);
    }

    public function showType(Request $request, $id)
    {
        $placeType = PlaceType::findOrFail($id);
        return response()->json($placeType);
    }

    public function createType(Request $request)
    {
        $this->validateCreatePlaceTypeValidation($request);

        $placeType = PlaceType::create([
            'name' => $request->name
        ]);

        return response()->json($placeType);
    }

    private function validateCreatePlaceTypeValidation($request)
    {
        $this->validate($request, [
            'name' => 'string|required|min:3|max:30|unique:event_types,name'
        ]);
    }

}
