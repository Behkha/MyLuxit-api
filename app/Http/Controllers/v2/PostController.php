<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Models\PlaceType;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    const PostPerPage = 25;
    const PlaceTypePerPage = 25;
    const CacheIndexFor = 15;

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function index()
    {
        $page = $this->paramList->getPage();
//        $posts = Cache::remember("Posts:page:$page", self::CacheIndexFor, function (){
        $posts = Post::
        with(['tags', 'postable'])->
        where('publish_at', '<', Carbon::now())->
        orderByDesc('occur_at')->
        simplePaginate();

        foreach ($posts as $post)
            $post->updateReferenceObject();

        return $posts;
//        });

        return $posts;
    }
}
