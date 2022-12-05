<?php

namespace App\Http\Controllers\v1;

use App\Models\Bookmark;
use App\Models\BookmarkCollection;
use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BookmarkCollectionController extends Controller
{
    const CollectionPerPage = 15;
    const BookmarkPerPage = 15;
    public function index(){
        $user = Auth::user();
        $collections = $user->bookmarkCollections()->withCount('bookmarks')->paginate(self::CollectionPerPage);


        return response()->json($collections);
    }
    public function list(){
        $events = Bookmark::where('user_id',Auth::id())->where('bookmarkable_type','event')->get(['bookmarkable_id as id','collection_id']);
        $places = Bookmark::where('user_id',Auth::id())->where('bookmarkable_type','place')->get(['bookmarkable_id as id','collection_id']);
       return response()->json([
           'events'=>$events->toArray(),
           'places'=>$places->toArray()
       ]);
    }
    public function show(Request $request, $id){
        $collection = BookmarkCollection::findOrFail($id);
        $bookmarks = $collection->bookmarks()->with(['bookmarkable','bookmarkable.authors','bookmarkable.photographers'])->paginate(self::BookmarkPerPage);
        foreach ($bookmarks->items() as $bookmark){
            if ($bookmark->bookmarkable_type === 'event' && $bookmark->bookmarkable->place_id){
                $bookmark->bookmarkable->place = Place::getById($bookmark->bookmarkable->place_id);
            }
        }

        return response()->json($bookmarks);
    }
    public function create(Request $request){
        $this->validateCreateCollectionRequest($request);
        $user = Auth::user();
        $collection = $user->bookmarkCollections()->create([
            'name'=>$request->name
        ]);

        return response()->json(['message'=>'resource created successfully','id'=>$collection->id]);
    }
    public function update(Request $request, $id){
        $this->validateCreateCollectionRequest($request);

        $collection = BookmarkCollection::findOrFail($id);

        $this->validateUserOwnership($collection);

        $collection->name = $request->name;
        $collection->save();

        return response()->json(['message'=>'resource updated successfully','id'=>$collection->id]);
    }
    public function delete(Request $request, $id){
        $collection = BookmarkCollection::findOrFail($id);

        $this->validateUserOwnership($collection);

        $collection->bookmarks()->delete();
        $collection->delete();

        return response()->json(['message'=>'resource deleted successfully','id'=>$collection->id]);
    }

    public function createBookmark(Request $request, $collectionId){
        $this->validateCreateBookmarkRequest($request);
        $collection = BookmarkCollection::findOrFail($collectionId);
        $this->validateUserOwnership($collection);

        switch ($request->type){
            case 'event':
                $this->createEventBookmark($collection, $request->id);
                break;
            case 'place':
                $this->createPlaceBookmark($collection, $request->id);
                break;
            default :
                abort(422);
        }

        $collection->updateImage();
        return response()->json(['message'=>'resource bookmarked successfully']);
    }
    public function deletePlaceBookmark(Request $request, $collectionId, $bookmarkId){
        $collection = BookmarkCollection::findOrFail($collectionId);
        $this->validateUserOwnership($collection);

        $collection->bookmarkedPlaces()->detach($bookmarkId);

        $collection->updateImage();
        return response()->json(['message'=>'resource unbookmarked successfully']);
    }
    public function deleteEventBookmark(Request $request, $collectionId, $bookmarkId){
        $collection = BookmarkCollection::findOrFail($collectionId);
        $this->validateUserOwnership($collection);

        $collection->bookmarkedEvents()->detach($bookmarkId);

        $collection->updateImage();
        return response()->json(['message'=>'resource unbookmarked successfully']);
    }

    private function createEventBookmark($collection, $id){
        $collection->bookmarkedEvents()->syncWithoutDetaching([$id => ['user_id'=>Auth::id()]]);
    }
    private function createPlaceBookmark($collection, $id){
        $collection->bookmarkedPlaces()->syncWithoutDetaching([$id => ['user_id'=>Auth::id()]]);
    }

    private function validateCreateCollectionRequest($request){
        $this->validate($request, [
            'name'=>'required|string|min:1|max:60'
        ]);
    }
    private function validateUserOwnership($collection) {
        $user = Auth::user();
        if ($user->id !== $collection->user_id)
            abort(403,"this user can't edit this collection .");
    }
    private function validateCreateBookmarkRequest($request){
        $this->validate($request, [
            'id'=>'required|integer',
            'type'=>[Rule::in(['place','event'])]
        ]);
    }
}
