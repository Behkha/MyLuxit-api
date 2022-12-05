<?php

namespace App\Http\Controllers\v3;

use App\Models\Bookmark;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Http\Resources\BookmarkCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookmarkController extends Controller
{
    const CollectionPerPage = 15;
    const BookmarkPerPage = 15;

    public function index(Request $request)
    {
        $user = Auth::user();
        $bookmarks = $user->bookmarks()
            ->orderBy('created_at', 'desc')
            ->with(['bookmarkable', 'bookmarkable.type:id,name'])->paginate(self::BookmarkPerPage);

        return new BookmarkCollection($bookmarks);
    }

    public function create(Request $request)
    {
        $this->validateCreateBookmarkRequest($request);

        $type = $request->input('type');
        $id = $request->input('id');

        Bookmark::updateOrCreate([
            'user_id' => Auth::id(),
            'bookmarkable_type' => $type,
            'bookmarkable_id' => $id
        ]);

        return response()->json(['message' => 'resource bookmarked successfully']);
    }

    private function validateCreateBookmarkRequest($request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
            'type' => [Rule::in(['place', 'event'])]
        ]);

        $type = $request->input('type');
        $id = $request->input('id');

        if ($type === 'event')
            Event::getById($id);
        else if ($type === 'place')
            Place::getById($id);

    }

    public function deleteEventBookmark(Request $request, $id = null)
    {
        $user = Auth::user();

        $bookmark = $user->bookmarkedEvents()->where('bookmarkable_id', '=', $id)->firstOrFail();
        $bookmark->removeFromUserCache();
        $user->bookmarkedEvents()->where('bookmarkable_id', '=', $id)->delete();

        return response()->json([
            'message' => 'bookmark deleted successfully'
        ]);
    }

    public function deletePlaceBookmark(Request $request, $id = null)
    {
        $user = Auth::user();

        $bookmark = $user->bookmarkedPlaces()->where('bookmarkable_id', '=', $id)->firstOrFail();
        $bookmark->removeFromUserCache();
        $user->bookmarkedPlaces()->where('bookmarkable_id', '=', $id)->delete();

        return response()->json([
            'message' => 'bookmark deleted successfully'
        ]);
    }

}
