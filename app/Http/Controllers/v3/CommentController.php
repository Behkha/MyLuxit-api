<?php

namespace App\Http\Controllers\v3;

use App\Http\Resources\CommentCollection;
use App\Http\Resources\CommentResource;
use App\Models\Celebrity;
use App\Models\Comment;
use App\Models\CommentDetail;
use App\Models\Event;
use App\Models\Imagable;
use App\Models\Place;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    private $type = null;

    public function __construct(Request $request)
    {
        $function_name = explode('@', $request->route()[1]['uses'])[1];
        $needs_type = [
            'create',
            'createAdminComment',
            'indexCommentableComments'
        ];

        if (in_array($function_name, $needs_type))
            $this->type = $request->route()[1]['type'];
        else
            $this->type = null;

        parent::__construct($request);
    }

    public function index(Request $request)
    {
        $page = max($request->input('page', 1), 1);

        $comments = Comment::orderByDesc('id')
            ->simplePaginate();

        return new CommentCollection($comments);
    }

    public function indexPending(Request $request)
    {
        $page = max($request->input('page', 1), 1);

        $comments = Comment::orderByDesc('id')
            ->pending()
            ->simplePaginate();

        return new CommentCollection($comments);
    }

    public function show(Request $request, $id = null)
    {
        $comment = Comment::with(['user', 'detail', 'commentable'])->findOrFail($id);
        return new CommentResource($comment);
    }


    public function indexCommentableComments(Request $request, $id = null)
    {
        if ($this->type === 'event')
            $commentable = Event::getById($id);
        else if ($this->type === 'place')
            $commentable = Place::getById($id);
        else if ($this->type === 'celebrity')
            $commentable = Celebrity::getById($id);

        $comments = $commentable->comments()
            ->orderBy('id', 'desc')
            ->confirmed()
            ->paginate();

        return new CommentCollection($comments);
    }

    public function create(Request $request, $id = null)
    {
        $this->validateCreateCommentRequest($request);

        $this->storeComment($request, $id);

        return response()->json([
            'message' => 'comment submitted successfully'
        ]);
    }

    private function validateCreateCommentRequest(Request $request)
    {

        // This check is better in a middleware
        if (Auth::guest() === false && Auth::guard('admin')->guest() === false) {
            throw new AuthorizationException('You can not access this endpoint');
        }


        if (Auth::guard('admin')->check()) {
            $rules = [
                'content' => 'required|max:255',
                'name' => 'required_if:show_type,' . CommentDetail::SHOW_TYPES['USER'] . '|string|max:255',
                'show_type' => [
                    'required',
                    Rule::in(CommentDetail::SHOW_TYPES)
                ]
            ];
        } else if (Auth::guard('user')->check()) {
            $rules = [
                'content' => 'required|max:255'
            ];
        }

        $this->validate($request, $rules);
    }

    private function storeComment(Request $request, $id)
    {

        if ($this->type === 'event') {
            $commentable = Event::getById($id);
        } else if ($this->type === 'place') {
            $commentable = Place::getById($id);
        } else if ($this->type === 'celebrity') {
            $commentable = Celebrity::findOrFail($id);
        } else {
            return null;
        }

        $comment = new Comment([
            'user_id' => Auth::guard('user')->id() ?: null,
            'admin_id' => Auth::guard('admin')->id() ?: null,
            'status_id' => Comment::STATUSES['CONFIRMED'],
            'content' => $request->input('content')
        ]);

        $commentable->comments()->save($comment);

        return $comment;
    }

    public function createAdminComment(Request $request, $id = null)
    {
        $this->validateCreateCommentRequest($request);

        $comment = $this->storeComment($request, $id);

        $comment->detail()->create([
            'show_type_id' => $request->input('show_type'),
            'name' => $request->input('show_type') === CommentDetail::SHOW_TYPES['USER'] ?
                $request->input('name') : null
        ]);

        return response()->json([
            'message' => 'comment submitted successfully'
        ]);
    }

    public function delete(Request $request, $id = null)
    {
        $comment = Comment::findOrFail($id);

        $comment->delete();

        return response()->json([
            'message' => 'comment deleted successfully'
        ]);
    }

    public function updateStatus(Request $request, $id = null)
    {
        $this->validateUpdateStatusRequest($request);

        $comment = Comment::findOrFail($id);

        $comment->update($request->only('status_id'));

        return response()->json([
            'message' => 'comment updated successfully'
        ]);
    }

    private function validateUpdateStatusRequest(Request $request)
    {
        $this->validate($request, [
            'status_id' => ['required', Rule::in(Comment::STATUSES)]
        ]);
    }
}
