<?php

namespace App\Http\Controllers\v3;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentCollection;
use App\Http\Resources\EventResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\PostableCollection;
use App\Http\Resources\PostableResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\SearchableResource;
use App\Models\Event;
use App\Models\Place;
use App\Models\PlaceType;
use App\Models\Post;
use App\Models\Searchable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Morilog\Jalali\Jalalian;

class PostController extends Controller
{
    const PostPerPage = 25;
    const PlaceTypePerPage = 25;
    const CacheIndexFor = 15;


    public function index(Request $request)
    {
        $page = $this->paramList->getPage();
        $languageAbbr = $request->input('language.abbreviation');

        if ($request->has('city_id')) {
            $cityId = $request->input('city_id');
            $cacheName = "Posts:$languageAbbr:City:$cityId:Page:$page";
        } else {
            $cacheName = "Posts:$languageAbbr:Page:$page";
        }


        $posts = Post::with(['tags', 'postable', 'postable.type'])
            ->where('publish_at', '<', Carbon::now())
            ->language($request->input('language.id'))
            ->orderByDesc('occur_at');

        if ($request->input('city_id')) {
            $posts->whereHas('cities', function ($query) use ($request) {
                $query->where('city_id', $request->input('city_id'));
            });
        }

        $posts = Cache::tags(['Posts'])->remember($cacheName, Post::CachePagePeriod, function () use ($posts) {
            return $posts->paginate();
        });


        return PostResource::collection($posts);
    }

    public function indexNearests(Request $request)
    {
        $this->validateIndexNearestsRequest($request);

        $places = Place::withCount(['events'])
            ->with(['type', 'tags'])
            ->language($request->input('language.id'))
            ->whereRaw("ST_DWithin(geo_location, ST_MakePoint($request->longitude,$request->latitude), $request->radius)")
            ->get();

        return PlaceResource::collection($places);
    }

    private
    function validateIndexNearestsRequest(Request $request)
    {
        $this->validate($request, [
            'latitude' => ['required', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'longitude' => ['required', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
            'radius' => 'required|numeric|min:100|max:30000'
        ]);
    }

    public
    function show(Request $request, $id)
    {
        $post = Post::getById($id);

        return new PostResource($post);
    }

    public
    function create(Request $request)
    {
        $this->validateCreatePostValidation($request);

        DB::beginTransaction();

        $post = Post::create([
            'postable_id' => $request->postable_id,
            'postable_type' => $request->postable_type,
            'admin_id' => Auth::guard('admin')->id(),
            'publish_at' => Carbon::createFromFormat('Y-m-d H:i', $request->publish_at),
            'occur_at' => Carbon::createFromFormat('Y-m-d H:i', $request->occur_at),
            'subtitle' => $request->input('subtitle'),
        ]);


        if ($request->has('cities')) {
            $cities = $request->input('cities');
        } else {
            $cities = $post->postable->cities()->pluck('city_id')->toArray();
        }

        foreach ($cities as $cityId) {
            $post->cities()->create([
                'city_id' => $cityId
            ]);
        }

        $post->update([
            'language_id' => $post->postable->language_id
        ]);

        $post->tags()->sync($request->input('tags'));

        DB::commit();

        return new PostResource($post);
    }

    private function validateCreatePostValidation(Request $request)
    {
        $this->validate($request, [
            'postable_id' => 'required|integer',
            'postable_type' => ['required', Rule::in(['event', 'place'])],
            'publish_at' => 'required|date',
            'occur_at' => 'required|date',
            'subtitle' => 'required|string|max:255',
            'tags' => 'array',
            'cities' => 'array',
            'cities.*' => 'integer|exists:cities,id',
        ]);

        if ($request->input('postable_type') === 'event') {
            $this->validate($request, [
                'postable_id' => 'exists:events,id'
            ]);
        } else if ($request->input('postable_type') === 'place') {
            $this->validate($request, [
                'postable_id' => 'exists:places,id'
            ]);
        }
    }

    public
    function delete(Request $request, $id = null)
    {
        $post = Post::findOrFail($id);
        $post->update([
            'deleted_by' => Auth::guard('admin')->id()
        ]);
        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully'
        ]);
    }

    public
    function update(Request $request, $id = null)
    {
        $this->validateCreatePostValidation($request);

        $post = Post::findOrFail($id);

        $post->update([
            'postable_id' => $request->input('postable_id'),
            'postable_type' => $request->input('postable_type'),
            'subtitle' => $request->input('subtitle'),
            'publish_at' => Carbon::createFromFormat('Y-m-d H:i', $request->publish_at),
            'occur_at' => Carbon::createFromFormat('Y-m-d H:i', $request->occur_at),
        ]);

        if ($request->has('cities')) {
            $cities = $request->input('cities');
        } else {
            $cities = $post->postable->cities()->pluck('city_id')->toArray();
        }

        foreach ($cities as $cityId) {
            $post->cities()->updateOrCreate([
                'city_id' => $cityId
            ]);
        }
        $post->cities()->whereNotIn('city_id', $request->input('cities'))->delete();
        $post->tags()->sync($request->input('tags'));
        $post->update(['updated_at' => Carbon::now()]);

        return response()->json([
            'message' => 'post updated successfully'
        ]);
    }

    public function search(Request $request)
    {
        $this->validateSearchRequest($request);
        if ($request->input('any')) {
            $searchables = Searchable::search($request->input('query'))->paginate();
            return SearchableResource::collection($searchables);
        } else {
            $posts = Post::search($request->input('query'))->paginate();
            $posts->load(['tags', 'postable', 'postable.type']);
            return PostResource::collection($posts);
        }
    }

    private
    function validateSearchRequest(Request $request)
    {
        $this->validate($request, [
            'query' => 'required|string|max:255|min:1'
        ]);
    }
}
