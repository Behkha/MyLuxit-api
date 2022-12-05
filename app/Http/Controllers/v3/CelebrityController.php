<?php

namespace App\Http\Controllers\v3;

use App\Http\Resources\CelebrityResource;
use App\Http\Resources\CharacterPropertyResource;
use App\Http\Resources\EventResource;
use App\Models\Celebrity;
use App\Models\Character;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CelebrityController extends Controller
{
    public function create(Request $request)
    {
        $this->validateCreateCelebRequest($request);

        $celeb = Celebrity::create($request->only([
            'title', 'bio', 'media', 'contact'
        ]));

        $character = $celeb->character()->create();
        if ($request->input('types')) {
            $character->types()->attach($request->input('types'));
        }

        return new CelebrityResource($celeb);
    }

    private function validateCreateCelebRequest(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string|max:255',
            'bio' => 'required|string|max:4000',
            'media' => 'required|array',
            'contact' => 'required|array|min:1',
            'contact.email' => 'array',
            'contact.*.title' => 'required_with:contact.*|string|max:255',
            'contact.*.type' => ['required_with:contact.*', Rule::in(Celebrity::ContactTypes)],
            'contact.*.content' => 'required_with:contact.*|',
            'properties' => 'array|min:1',
            'properties.*.type' => Rule::in(Character::PROPERTIES),
            'properties.*.id' => ['required_with:properties.*.type'],
            'types' => 'array|min:1',
            'types.*' => 'integer|exists:character_types,id'
        ]);
        if ($request->input('properties')) {
            foreach ($request->input('properties') as $index => $property) {
                if ($property['type'] === 'event') {
                    $this->validate($request, [
                        'properties.' . $index . '.id' => 'exists:events,id'
                    ]);
                }
            }
        }
    }

    public function show(Request $request, $id = null)
    {
        $celebrity = Celebrity::getById($id);
        return new CelebrityResource($celebrity);
    }

    public function indexProperties(Request $request, $id = null)
    {
        $celebrity = Celebrity::getById($id);
        $character = $celebrity->character;
        $properties = $character->properties()->with(['propertyObject', 'propertyObject.type'])->paginate();

        return CharacterPropertyResource::collection($properties);

    }

    public function delete(Request $request, $id = null)
    {
        $celeb = Celebrity::getById($id);
        $celeb->delete();

        return response()->json([
            'message' => 'ok'
        ]);
    }

    public function update(Request $request, $id = null)
    {
        $this->validateCreateCelebRequest($request);
        $celeb = Celebrity::getById($id);

        $celeb->update($request->only([
            'title', 'bio', 'media', 'contact'
        ]));

        $celeb->clearCache();

        if ($request->input('types')) {
            $celeb->character->types()->sync($request->input('types'));
        }

        return new CelebrityResource($celeb->refresh());
    }

}
