<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return response()->json(['message' => 'comment created successfuly', 'name' => Auth::user()->name, 'comment' => $comment], 201);
    }

    public function update(Request $request, $post_id, $comment_id)
    {
        $updatedComment = $request->validate(['content' => 'string|required|max:255']);
        $post = Post::findOrFail($post_id);
        $comment = Comment::findOrFail($comment_id);


        if (!($comment->user_id === Auth::user()->id)) {
            return response()->json(['message' => 'you are not authorized to change the comment'], 401);
        }

        if ($comment->post_id === $post->id) {
            $new_comment = $comment->update($updatedComment);
            return response()->json(['message' => 'comment updated successfuly', 'name' => Auth::user()->name, 'updated comment' => $new_comment], 200);
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
}
