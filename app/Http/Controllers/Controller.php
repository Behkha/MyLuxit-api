<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\ParamList;
use App\Http\Resources\EventResource;
use App\Http\Resources\PlaceResource;
use App\Models\Event;
use App\Models\Place;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected $paramList = null;

    public function __construct(Request $request)
    {
        $this->paramList = new ParamList($request);
    }
}
