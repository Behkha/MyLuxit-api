<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Photographer;
use Illuminate\Http\Request;

class PhotographerController extends Controller
{
    const PhotographerPerPage = 25;
    public function index(){
        $photographers = Photographer::all(['name','id']);
        return response()->json($photographers);
    }
    public function show(Request $request, $id){
        $photographer = Photographer::findOrFail($id);
        return response()->json($photographer);
    }
    public function create(Request $request){
        $this->validateCreatePhotographerValidation($request);

        $photographer = Photographer::create([
            'name'=>$request->name
        ]);

        return response()->json($photographer);
    }

    private function validateCreatePhotographerValidation($request){
        $this->validate($request,[
            'name'=>'string|required|min:3|max:60|unique:photographers,name'
        ]);
    }
}
