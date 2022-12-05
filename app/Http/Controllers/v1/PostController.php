<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    const PostPerPage = 25;
    const PlaceTypePerPage = 25;
    public function __construct (Request $request) {
        parent::__construct($request);
    }

    public function index(){
        $weekPosts = Post::getThisWeekPosts();
        return response()->json($weekPosts);
    }
    public function show(Request $request, $id){
        $post =Post::getById($id);
        return response()->json($post);
    }
    public function create(Request $request){
        $this->validateCreatePostValidation($request);

        $post = Post::create([
            'postable_id'=>$request->postable_id,
            'postable_type'=>$request->postable_type,
            'admin_id'=> Auth::guard('admin')->id(),
            'publish_at'=>Carbon::createFromFormat('Y-m-d H:i',$request->publish_at),
            'occur_at'=>Carbon::createFromFormat('Y-m-d H:i',$request->occur_at)
        ]);

        $post->tags()->attach($request->tags);

        Post::flushThisWeekCache();

        return response()->json($post);
    }

    private function validateCreatePostValidation($request){
        $this->validate($request,[
            'postable_id'=>'required|integer',
            'postable_type'=>['required',Rule::in(['event','place'])],
            'publish_at'=>'required|date',
            'occur_at'=>'required|date',
            'tags'=>'array'
        ]);
    }

}
