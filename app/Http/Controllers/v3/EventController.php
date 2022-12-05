<?php

namespace App\Http\Controllers\v3;

use App\Console\Commands\ReindexAllPositionedCateogries;
use App\Http\Resources\EventResource;
use App\Http\Resources\EventTypeResource;
use App\Http\Resources\ImagableResource;
use App\Models\Category;
use App\Models\Citiable;
use App\Models\Event;
use App\Models\EventType;
use App\Http\Controllers\Controller;
use App\Models\Imagable;
use App\Models\Language;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    const EventPerPage = 25;
    const EventTypePerPage = 25;

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $query = Event::with(['type', 'language'])
            ->orderBy('id', 'desc')
            ->language($request->input('language.id'));

        if ($request->input('page') !== null) {
            $events = $query->paginate(self::EventPerPage);
        } else {
            $events = Event::with(['type'])->get();
        }

        return EventResource::collection($events);
    }

    public function show(Request $request, $id)
    {
        $event = Event::getById($id);

        if (Auth::check())
            $event->incrementVisits($request);

        return new EventResource($event);
    }

    public function create(Request $request)
    {
        $this->validateCreateEventValidation($request);
        $links = $request->input('links');

        $inputDetails = $request->input('details');
        $details = $this->mapDetailsForDB($inputDetails);

        $event = Event::create([
            'title' => $request->input('title'),
            'content' => $request->get('content'),
            'place_id' => $request->input('place_id'),
            'admin_id' => Auth::guard('admin')->id(),
            'type_id' => $request->input('type_id'),
            'media' => $request->input('media'),
            'meta' => $request->input('meta', []),
            'links' => ($links && $links['website']['link']) ? $links : null,
            'starts_at' => Carbon::createFromFormat('Y-m-d H:i', $request->input('starts_at')),
            'ends_at' => Carbon::createFromFormat('Y-m-d H:i', $request->input('ends_at')),
            'subtitle' => $request->input('subtitle'),
            'details' => $details,
            'language_id' => $request->input('language_id') ?: Language::getDefaultLanguage()->id
        ]);


        $event->tags()->sync($request->input('tags'));
        $event->authors()->sync($request->input('authors'));
        $event->photographers()->sync($request->input('photographers'));
        $event->characters()->sync($request->input('characters'));

        foreach ($request->input('cities') as $cityId) {
            $event->cities()->create([
                'city_id' => $cityId
            ]);
        }

        Category::indexRelatedCategoriesTo($event, $request->input('tags'));

        return response()->json($event);
    }

    private function validateCreateEventValidation(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string|min:3|max:60',
            'content' => 'required|string',
            'place_id' => 'integer|min:1|exists:places,id|nullable',
            'type_id' => 'required|numeric|integer|min:1|exists:event_types,id',
            'photographers' => 'array|min:1',
            'photographers.*' => 'exists:photographers,id',
            'authors' => 'array|min:1',
            'authors.*' => 'exists:authors,id',
            'tags' => 'array',
            'media' => 'array',
            'links' => 'array',
            'meta' => 'array',
            'starts_at' => 'required|date_format:Y-m-d H:i',
            'ends_at' => 'required|date_format:Y-m-d H:i',
            'subtitle' => 'required|string|max:255',
            'characters' => 'array',
            'characters.*' => 'integer|exists:characters,id',
            'details' => 'array',
            'details.custom_link' => 'array',
            'details.custom_link.type' => ['string', Rule::in(array_keys(Event::DetailTypes)), "required_with:details.custom_link"],
            'details.custom_link.title' => 'string|max:255',
            'details.custom_link.content' => 'string|max:500',
            'details.service_provider' => 'array',
            'details.service_provider.type' => ['string', Rule::in(array_keys(Event::DetailTypes)), "required_with:details.service_provider"],
            'details.service_provider.title' => 'string|max:255',
            'details.service_provider.content' => 'string|max:500',
            'details.address' => 'array',
            'details.address.type' => ['string', Rule::in(array_keys(Event::DetailTypes)), 'required_with:details.address'],
            'details.address.content' => 'string|max:500|required_with:details.address',
            'details.tell' => 'array',
            'details.tell.type' => ['string', Rule::in(array_keys(Event::DetailTypes)), 'required_with:details.tell'],
            'details.tell.content' => 'string|max:255|required_with:details.tell',
            'details.price' => 'array',
            'details.price.type' => ['string', Rule::in(array_keys(Event::DetailTypes)), 'required_with:details.price'],
            'details.price.content' => 'numeric|required_with:details.price',
            'details.duration' => 'array',
            'details.duration.type' => ['string', 'in:duration', 'required_with:details.duration'],
            'cities' => 'required|array',
            'cities.*' => 'integer|exists:cities,id',
            'language_id' => 'integer|exists:languages,id'
        ]);
    }

    private function mapDetailsForDB($inputDetails)
    {
        $details = empty($inputDetails) ? null : collect();
        foreach ($inputDetails as $detail) {
            $title = Event::DetailTypes[$detail['type']];
            if (empty($title)) {
                $title = $detail['title'];
            }
            $type = $detail['type'];
            if ($detail['type'] == 'duration') {
                $starts_at = $detail['starts_at'];
                $ends_at = $detail['ends_at'];
                $details->put($type, compact('title', 'type', 'starts_at', 'ends_at'));
            } else {
                $content = $detail['content'];
                $details->put($type, compact('title', 'type', 'content'));
            }
        }

        return $details;
    }

    public function delete(Request $request, $id = null)
    {
        $event = Event::getById($id);
        $event->update([
            'deleted_by' => Auth::guard('admin')->id()
        ]);
        $event->delete();

        return response()->json([
            'message' => 'event deleted successfully'
        ]);
    }

    public function update(Request $request, $id = null)
    {
        $this->validateCreateEventValidation($request);
        $event = Event::getById($id);
        $inputDetails = $request->input('details', null);
        $details = $this->mapDetailsForDB($inputDetails);

        DB::beginTransaction();
        $event->update([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'place_id' => $request->input('place_id'),
            'type_id' => $request->input('type_id'),
            'media' => $request->input('media'),
            'meta' => $request->input('meta', []),
            'links' => $request->input('links'),
            'starts_at' => Carbon::createFromFormat('Y-m-d H:i', $request->input('starts_at')),
            'ends_at' => Carbon::createFromFormat('Y-m-d H:i', $request->input('ends_at')),
            'subtitle' => $request->input('subtitle'),
            'details' => $details,
            'language_id' => $request->input('language_id') ?: Language::getDefaultLanguage()->id
        ]);

        if ($request->has('tags'))
            $event->tags()->sync($request->input('tags'));
        if ($request->has('authors'))
            $event->authors()->sync($request->authors);
        if ($request->has('photographers'))
            $event->photographers()->sync($request->photographers);
        if ($request->has('characters'))
            $event->characters()->sync($request->input('characters'));

        foreach ($request->input('cities') as $cityId) {
            $event->cities()->updateOrCreate([
                'city_id' => $cityId
            ]);
        }
        $event->cities()->whereNotIn('city_id', $request->input('cities'))->delete();

        $event->update(['updated_at' => Carbon::now()]);
        DB::commit();
        Artisan::call(ReindexAllPositionedCateogries::class);

        return response()->json([
            'message' => 'event updated successfully'
        ]);
    }

    public function search(Request $request)
    {
        $this->validate($request, [
            'query' => 'required|min:1|max:255'
        ]);
        $events = Event::search($request->input('query'))
            ->get();
        $events->load(['type']);
        return EventResource::collection($events);
    }

    public function indexTypes(Request $request)
    {
        if ($request->input('page') === null) {
            $eventTypes = EventType::all(['name', 'id']);
        } else {
            $eventTypes = EventType::paginate();
        }
        return EventTypeResource::collection($eventTypes);
    }

    public function showType(Request $request, $id)
    {
        $eventType = EventType::findOrFail($id);
        return response()->json($eventType);
    }

    public function createType(Request $request)
    {
        $this->validateCreateEventTypeValidation($request);

        $eventType = EventType::create([
            'name' => $request->name
        ]);

        return response()->json($eventType);
    }

    private function validateCreateEventTypeValidation($request)
    {
        $this->validate($request, [
            'name' => 'string|required|min:3|max:50|unique:event_types,name'
        ]);
    }

    public function deleteType(Request $request, $id = null)
    {
        $type = EventType::findOrFail($id);

        if ($type->events()->count()) {
            return response()->json([
                'message' => 'there are event associated with this type',
                'data' => $type->events()->pluck('id')
            ], 403);
        } else {
            $type->delete();
            return response()->json([
                'message' => 'type deleted successfully'
            ]);
        }

        return response()->json([
            'message' => 'ok'
        ]);
    }

    public function updateType(Request $request, $id = null)
    {
        $this->validateCreateEventTypeValidation($request);
        $type = EventType::findOrFail($id);

        $type->update($request->only('name'));

        return response()->json([
            'message' => 'event type updated successfully'
        ]);
    }

    public function indexConfirmedImages(Request $request, $id = null)
    {
        $event = Event::getById($id);
        $images = $event->images()->with(['user'])->confirmed()->paginate();
        return ImagableResource::collection($images);
    }

    public function indexImages(Request $request, $id = null)
    {
        $event = Event::getById($id);
        $images = $event->images()->with(['user'])->orderBy('status_id', 'asc')->paginate();
        return ImagableResource::collection($images);
    }

    public function changeImageStatus(Request $request, $eventId = null, $imageId = null)
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
