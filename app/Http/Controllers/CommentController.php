<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Mail\NewCommentMail;
use App\Models\Comment;
use App\Models\Post;
use Dotenv\Exception\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, $id)
    {
        $post = Post::findOrFail($id);

        $validatedComment = $request->validated();
        $validatedComment['post_id'] = $post->id;
        $validatedComment['user_id'] = Auth::user()->id;
        $validatedComment['parent_id'] = null;

        $comment = Comment::create($validatedComment);

        if ($comment->post->user->email !== $comment->user->email) {
            Mail::to($comment->post->user->email)
                ->send(new NewCommentMail($comment));
        }

        return response()->json(['message' => 'comment created successfuly', 'name' => Auth::user()->name, 'comment' => $comment], 201);
    }

    public function update(Request $request, $post_id, $comment_id)
    {
        try {

            $post = Post::findOrFail($post_id);
            $comment = Comment::where('id', $comment_id)
                ->where('post_id', $post_id)
                ->firstOrFail();

            if ($comment->user_id !== Auth::user()->id) {
                return response()->json([
                    'message' => 'Unauthorized: You can only update your own comments'
                ], 403);
            }

            $validatedData = $request->validate([
                'content' => 'required|string|max:255'
            ]);

            if ($comment->parent_id === null) {
                $comment->update($validatedData);

                return response()->json([
                    'message' => 'Comment updated successfully',
                    'comment' => [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'user_id' => $comment->user_id
                    ]
                ], 200);
            } else {
                try {
                    $comment->update($validatedData);

                    return response()->json([
                        'message' => 'Reply updated successfully',
                        'reply' => $comment->load('user')
                    ], 200);
                } catch (ModelNotFoundException $e) {
                    return response()->json([
                        'message' => 'Reply not found or this is not a reply'
                    ], 404);
                }
            }
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Comment or post not found'
            ], 404);
        } catch (ValidationException $e) {

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->getMessage()
            ], 422);
        }
    }

    public function delete($post_id, $comment_id)
    {
        $post = Post::findOrFail($post_id);
        $comment = Comment::findOrFail($comment_id);

        if ($comment->post_id === $post->id && $comment->user_id === Auth::user()->id) {
            $comment->delete();
            return response()->json(['message' => 'Comment Deleted Successfuly'], 200);
        }
        return response()->json(['message' => 'Comment could not be found or you are not authorized'], 401);
    }

    public function storeReply(StoreCommentRequest $request, $post_id, $comment_id)
    {
        $post = Post::findOrFail($post_id);
        $comment = Comment::findOrFail($comment_id);

        $validatedComment = $request->validated();
        $validatedComment['user_id'] = Auth::user()->id;
        $validatedComment['post_id'] = $post->id;
        $validatedComment['parent_id'] = $comment->id;

        if ($comment->post_id === $post->id) {
            $reply = Comment::create($validatedComment);
            return response(['message' => 'reply created successfuly', 'reply' => $reply], 201);
        }
        return response(['message' => 'error happened'], 422);
    }

    public function getPostComments($post_id)
    {
        $comments = Comment::with(['user', 'replies.user'])
            ->where('post_id', $post_id)
            ->whereNull('parent_id')
            ->get();

        return response()->json([
            'comments' => $comments
        ], 200);
    }
}
