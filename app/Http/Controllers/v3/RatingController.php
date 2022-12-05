<?php

namespace App\Http\Controllers\v3;

use App\Models\Event;
use App\Models\Place;
use App\Models\Rating;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    private $type = null;

    public function __construct(Request $request)
    {
        $this->type = $request->route()[1]['type'];
    }

    public function create(Request $request, $id)
    {
        $this->validateRatingRequest($request, $id);
        if ($this->type === 'event') {
            $ratable = Event::findOrFail($id);
        } else if ($this->type === 'place') {
            $ratable = Place::findOrFail($id);
        }

        $existing_rate = Rating::where([
            ['user_id', '=', Auth::id()],
            ['ratable_type', '=', $this->type],
            ['ratable_id', '=', $id]
        ])->first();
        if ($existing_rate) {
            $existing_rate->update([
                'rate' => $request->input('rate')
            ]);
        } else {
            $rating = new Rating([
                'user_id' => Auth::id(),
                'rate' => $request->input('rate')
            ]);
            $ratable->ratings()->save($rating);
        }

        return response()->json([
            'message' => 'Rating submitted successfully'
        ]);
    }

    private function validateRatingRequest(Request $request, $id)
    {
        $this->validate($request, [
            'rate' => 'required|integer|min:1|max:10'
        ]);

        $user = Auth::user();

        $hasRatedThis = $user->ratings()->where([
            ['ratable_id', '=', $id],
            ['ratable_type', '=', $this->type]
        ])->exists();

//        if ($hasRatedThis)
    }
}
