<?php

namespace App\Http\Controllers\v3;

use App\Console\Commands\ReindexAllPositionedCateogries;
use App\Http\Resources\EventResource;
use App\Http\Resources\ImagableResource;
use App\Http\Resources\PlaceImageResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\PlaceTypeResource;
use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Language;
use App\Models\Place;
use App\Models\Imagable;
use App\Models\PlaceType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PlaceController extends Controller
{
    const PlacePerPage = 15;

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function index(Request $request)
    {

        $query = Place::with(['type', 'language'])
            ->orderBy('id', 'desc')
            ->language($request->input('language.id'));

        if ($request->input('page')) {
            $places = $query->paginate(self::PlacePerPage);
        } else {
            $places = $query->get();
        }

        return PlaceResource::collection($places);
    }

    public function show(Request $request, $id = null)
    {
        $place = Place::getById($id);

        if (Auth::check())
            $place->incrementVisits($request);

        return new PlaceResource($place);
    }

    public function create(Request $request)
    {
        $this->validateCreatePlaceValidation($request);
        $inputDetails = $request->input('details');
        $details = empty($inputDetails) ? null : collect();
        foreach ($inputDetails as $detail) {
            $title = Place::DetailTypes[$detail['type']];
            if (empty($title)) {
                $title = $detail['title'];
            }

            $type = $detail['type'];
            if ($type !== "schedule") {
                $content = $detail['content'];
                $details->put($type, compact('title', 'type', 'content'));
            } else {
                $week_days = $detail['week_days'];
                $details->put($type, compact('title', 'type', 'week_days'));
            }
        }


        $links = $request->input('links');

        DB::beginTransaction();

        $place = Place::create([
            'name' => $request->input('name'),
            'content' => $request->get('content'),
            'address' => $request->input('address'),
//            'location' => ($request->input('location')) ? $request->input('location') : null,
            'city_id' => $request->input('city_id'),
            'admin_id' => Auth::guard('admin')->id(),
            'type_id' => $request->input('type_id'),
            'media' => $request->input('media'),
            'meta' => $request->input('meta', []),
            'links' => ($links && $links['website']['link']) ? $links : null,
            'subtitle' => $request->input('subtitle'),
	    'price' => $request->input('price'),
            'details' => $details,
            'language_id' => $request->input('language_id') ?: Language::getDefaultLanguage()->id
        ]);

        if ($request->input('location')) {
            $long_lat = explode(',', $request->input('location'));
            $l = $long_lat[1] . ' ' . $long_lat[0];
            $id = $place->id;

            DB::statement("UPDATE places SET geo_location = ST_GeogFromText('POINT($l)') WHERE id = $id;");
        }

        $place->tags()->attach($request->input('tags'));
  //      $place->authors()->attach($request->input('authors'));
  //      $place->photographers()->attach($request->input('photographers'));
        foreach ($request->input('cities') as $cityId) {
            $place->cities()->updateOrCreate([
                'city_id' => $cityId
            ]);
        }

        Category::indexRelatedCategoriesTo($place, $request->input('tags'));
        DB::commit();

        return response()->json($place);
    }

    private function validateCreatePlaceValidation($request)
    {
        $weekDays = collect(Carbon::getDays())->map(function ($day) {
            return strtolower($day);
        });

        $this->validate($request, [
            'name' => 'required|string|min:3|max:60',
            'content' => 'required|string',
            'address' => 'required|string',
    //        'location' => 'string',
            'city_id' => 'required|numeric|integer|min:1|exists:cities,id',
            'type_id' => 'required|numeric|integer|min:1|exists:place_types,id',
    //        'photographers' => 'array',
    //        'authors' => 'array',
            'tags' => 'array',
            'media' => 'array',
            'links' => 'array',
            'subtitle' => 'required|string|max:255',
	        'price' => 'required|string',
            'details' => 'array',
            'details.custom_link' => 'array',
            'details.custom_link.type' => ['string', Rule::in(array_keys(Place::DetailTypes)), "required_with:details.custom_link"],
            'details.custom_link.title' => 'string|max:255',
            'details.custom_link.content' => 'string|max:500',
            'details.tell' => 'array',
            'details.tell.type' => ['string', Rule::in(array_keys(Place::DetailTypes)), "required_with:details.tell"],
            'details.tell.content' => 'string|max:255|required_with:details.tell',
            //'details.schedule.type' => ['string', Rule::in(array_keys(Place::DetailTypes)), 'required_with:details.schedule'],
            //'details.schedule.week_days' => 'array|required_with:details.schedule',
            //'details.schedule.week_days.*' => ['array'],
            //'details.schedule.week_days.*.*' => ['array'],
            //'details.schedule.week_days.*.*.start' => 'date_format:H:i|required_with:details.schedule.week_days.*.*.end',
            //'details.schedule.week_days.*.*.end' => 'date_format:H:i|required_with:details.schedule.week_days.*.*.start',
            'cities' => 'array|required',
            'cities.*' => 'integer|exists:cities,id',
            'language_id' => 'integer|exists:languages,id'
        ]);

        if ($request->input('details') && $request->input('details.schedule')) {
            foreach ($request->input('details.schedule.week_days') as $weekDay => $hours) {
                if (!in_array($weekDay, $weekDays->toArray())) {
                    throw new \Exception('Invalid day', 422);
                };
            }
        }
    }

    public function update(Request $request, $id = null)
    {
        $this->validateCreatePlaceValidation($request);

        $place = Place::getById($id);
        $inputDetails = $request->input('details');
        $details = empty($inputDetails) ? null : collect();
        foreach ($inputDetails as $detail) {
            $title = Place::DetailTypes[$detail['type']];

            if (empty($title)) {
                $title = $detail['title'];
            }

            $type = $detail['type'];
            if ($type !== "schedule") {
                $content = $detail['content'];
                $details->put($type, compact('title', 'type', 'content'));
            } else {
                $week_days = $detail['week_days'];
                $details->put($type, compact('title', 'type', 'week_days'));
            }
        }

        $place->update([
            'name' => $request->input('name'),
            'content' => $request->input('content'),
            'address' => $request->input('address'),
            'location' => $request->input('location'),
            'city_id' => $request->input('city_id'),
            'type_id' => $request->input('type_id'),
            'media' => $request->input('media'),
            'links' => $request->input('links'),
            'subtitle' => $request->input('subtitle'),
	        'price' => $request->input('price'),
            'details' => $details,
            'language_id' => $request->input('language_id') ?: Language::getDefaultLanguage()->id
        ]);

        if ($request->has('tags'))
            $place->tags()->sync($request->tags);
        if ($request->has('authors'))
            $place->authors()->sync($request->authors);
        if ($request->has('photographers'))
            $place->photographers()->sync($request->photographers);
        foreach ($request->input('cities') as $cityId) {
            $place->cities()->updateOrCreate([
                'city_id' => $cityId
            ]);
        }

        if ($request->input('location')) {
            $long_lat = explode(',', $request->input('location'));
            $l = $long_lat[1] . ' ' . $long_lat[0];
            $id = $place->id;

            DB::statement("UPDATE places SET geo_location = ST_GeogFromText('SRID=4326;POINT($l)') WHERE id = $id;");
        }
        $place->cities()->whereNotIn('city_id', $request->input('cities'))->delete();
        $place->update([
            'updated_at' => Carbon::now()
        ]);

        Artisan::call(ReindexAllPositionedCateogries::class);
        return response()->json([
            'message' => 'place updated successfully',
            'data' => new PlaceResource($place)
        ]);
    }

    public function delete(Request $request, $id = null)
    {
        $place = Place::findOrFail($id);
        $place->update([
            'deleted_by' => Auth::guard('admin')->id()
        ]);
        $place->delete();

        return response()->json([
            'message' => 'place deleted successfully'
        ]);
    }

    public function search(Request $request)
    {
        $this->validate($request, [
            'query' => 'required|min:1|max:255'
        ]);
        $places = Place::search($request->input('query'))
            ->get();
        $places->load(['type']);

        return PlaceResource::collection($places);
    }

    public function showEvents(Request $request, $id = null)
    {
        $place = Place::getById($id);
        $place->load('events.type');

        return EventResource::collection($place->events);
    }

    public function indexTypes(Request $request)
    {
        if ($request->input('page') === null) {
            $placeTypes = PlaceType::all(['name', 'id']);
        } else {
            $placeTypes = PlaceType::paginate();
        }
        return PlaceTypeResource::collection($placeTypes);
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
            'name' => 'string|required|min:3|max:50|unique:event_types,name'
        ]);
    }

    public function updateType(Request $request, $id = null)
    {
        $this->validateCreatePlaceTypeValidation($request);
        $type = PlaceType::findOrFail($id);

        $type->update($request->only('name'));

        return response()->json([
            'message' => 'place type updated successfully'
        ]);
    }

    public function deleteType(Request $request, $id = null)
    {
        $type = PlaceType::findOrFail($id);

        if ($type->places()->count()) {
            return response()->json([
                'message' => 'there are event associated with this type',
                'data' => $type->places()->pluck('id')
            ]);
        } else {
            $type->delete();
            return response()->json([
                'message' => 'type deleted successfully'
            ]);
        }
    }

    public function indexConfirmedImages(Request $request, $id = null)
    {
        $place = Place::getById($id);
        $images = $place->images()->with(['user'])->confirmed()->paginate();
        return ImagableResource::collection($images);
    }

    public function indexImages(Request $request, $id = null)
    {
        $place = Place::getById($id);
        $images = $place->images()->with(['user'])->orderBy('status_id', 'asc')->paginate();

        return ImagableResource::collection($images);
    }

    public function changeImageStatus(Request $request, $placeId = null, $imageId = null)
    {
        $this->validate($request, [
            'status' => ['required', Rule::in(array_keys(Imagable::Statuses))]
        ]);
        $image = Imagable::findOrFail($imageId);

        $image->update([
            'status_id' => Imagable::Statuses[$request->input('status')]
        ]);

        return new ImagableResource($image);
    }

}
