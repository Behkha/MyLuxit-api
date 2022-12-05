<?php

namespace App\Http\Controllers\v1;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class EventController extends Controller
{
    const EventPerPage = 25;
    const EventTypePerPage = 25;

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function index()
    {
        $events = Cache::tags(['Event', 'EventPage'])->remember('Events:Page:' . $this->paramList->getPage(), Event::CachePagePeriod, function () {
            return Event::paginate(self::EventPerPage);
        });
        return response()->json($events);
    }

    public function show(Request $request, $id)
    {
        $event = Event::getById($id);
        return response()->json($event);
    }

    public function create(Request $request)
    {
        $this->validateCreateEventValidation($request);

        $event = Event::create([
            'title' => $request->title,
            'content' => $request->get('content'),
            'place_id' => $request->place_id,
            'admin_id' => Auth::guard('admin')->id(),
            'type_id' => $request->type_id,
            'media' => $request->media,
            'links' => ($request->links['website']['link']) ? $request->links : null
        ]);

        $event->tags()->attach($request->tags);
        $event->authors()->attach($request->authors);
        $event->photographers()->attach($request->photographers);

        foreach ($request->media as $image) {
            Redis::lrem(config('cache.prefix').'uploadedImages', 0, $image['path']);
        }

        Event::flushPagesCache();
        Category::indexRelatedCategoriesTo($event, $request->tags);
        return response()->json($event);
    }

    private function validateCreateEventValidation($request)
    {
        $this->validate($request, [
            'title' => 'required|string|min:3|max:60',
            'content' => 'required|string',
            'place_id' => 'integer|min:1|exists:places,id|nullable',
            'type_id' => 'required|numeric|integer|min:1|exists:event_types,id',
            'photographers' => 'array',
            'authors' => 'array',
            'tags' => 'array',
            'media' => 'array',
            'links' => 'array',
            'meta' => 'array'
        ]);
    }

    public function search(Request $request)
    {
        $events = Event::where('title', 'ilike', '%' . $request->keyword . '%')->get(['title', 'id']);
        return response()->json($events);
    }

    public function indexTypes()
    {
        $eventTypes = EventType::all(['name', 'id']);
        return response()->json($eventTypes);
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
            'name' => 'string|required|min:3|max:30|unique:event_types,name'
        ]);
    }
}
