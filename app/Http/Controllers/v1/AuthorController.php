<?php

namespace App\Http\Controllers\v1;

use App\Models\Author;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    const AuthorPerPage = 25;
    public function index(){
        $authors = Author::all(['name','id']);
        return response()->json($authors);
    }
    public function show(Request $request, $id){
        $author = Author::findOrFail($id);
        return response()->json($author);
    }
    public function create(Request $request){
        $this->validateCreateAuthorValidation($request);

        $author = Author::create([
            'name'=>$request->name
        ]);

        return response()->json($author);
    }

    private function validateCreateAuthorValidation($request){
        $this->validate($request,[
            'name'=>'string|required|min:3|max:60|unique:authors,name'
        ]);
    }
}
