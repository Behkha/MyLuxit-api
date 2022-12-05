<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    const TagPerPage = 25;
    public function __construct (Request $request) {
        parent::__construct($request);
    }

    public function index(){
        $tags = Tag::paginate(self::TagPerPage);
        return response()->json($tags);
    }
    public function show(Request $request, $id){
        $tag = Tag::findOrFail($id);
        return response()->json($tag);
    }
    public function create(Request $request){
        $this->validateCreateTagValidation($request);

        $tag = Tag::create([
            'name'=>$request->name
        ]);

        return response()->json($tag);
    }
    public function search(Request $request){
        $tags = Tag::where('name','ilike','%'.$request->keyword.'%')->get(['name','id']);
        return response()->json($tags);
    }

    private function validateCreateTagValidation($request){
        $this->validate($request,[
            'name'=>'string|required|min:3|max:30|unique:tags,name'
        ]);
    }
    private function validateSearchRequest($request){
        $this->validate($request, [
            'keyword'=>'required|string'
        ]);
    }
}
