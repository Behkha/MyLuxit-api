<?php

namespace App\Http\Controllers\v3;

use App\Http\Resources\LanguageResource;
use App\Models\Language;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LanguageController extends Controller
{
    public function index(Request $request)
    {
        $query = Language::query();

        if ($request->input('page')) {
            $langs = $query->paginate();
        } else {
            $langs = $query->get();
        }

        return LanguageResource::collection($langs);
    }
}
