<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function likePost($post_id)
    {
        $existingLike = Like::where('post_id', $post_id)->where('user_id', Auth::user()->id)->first();
        $post = Post::where('post_id', $post_id);
        if ($existingLike) {
            return response()->json(['message' => 'already liked'], 400);
        }

        Like::create([
            'post_id' => $post_id,
            'user_id' => Auth::user()->id,
            'comment_id' => NULL
        ]);
        return response()->json(['message' => 'Post Liked'], 200);
    }

    public function likeComment($post_id, $comment_id)
    {
        $existingLike = Like::where('post_id', $post_id)
            ->where('comment_id', $comment_id)
            ->where('user_id', Auth::user()->id)->first();

        if ($existingLike) return response()->json(['message' => 'Already Liked'], 400);

        Like::create([
            'post_id' => $post_id,
            'user_id' => Auth::user()->id,
            'comment_id' => $comment_id
        ]);
        return response()->json(['message' => 'Comment Liked'], 200);
    }

    public function unLikePost($post_id)
    {
        $like = Like::where('post_id', $post_id)->where('user_id', Auth::user()->id)->delete();
        return response()->json(['message' => 'Post Un Liked'], 200);
    }

    public function unLikeComment($post_id, $comment_id)
    {
        $like = Like::where('post_id', $post_id)
            ->where('user_id', Auth::user()->id)
            ->where('comment_id', $comment_id)
            ->delete();
        return response()->json(['message' => 'Comment UnLiked'], 200);
    }

    public function getPostLikes($post_id)
    {
        $likesCount = Like::where('post_id', $post_id)->count();
        $userLiked = Auth::check() ? Like::where('user_id', Auth::user()->id)->where('post_id', $post_id)->exists() : false;

        if ($userLiked)
            return response(['likes Count' => $likesCount, 'user Liked' => 'yes'], 200);
        return response(['likes Count' => $likesCount, 'user Liked' => 'no'], 200);
    }

    public function getCommentLikes($post_id, $comment_id)
    {
        $likesCount = Like::where('post_id', $post_id)->where('comment_id', $comment_id)->count();
        $userLiked = Auth::check() ? Like::where('user_id', Auth::user()->id)->where('post_id', $post_id)->where('comment_id', $comment_id)->exists() : false;

        if ($userLiked)
            return response(['likes Count' => $likesCount, 'user Liked' => 'yes'], 200);
        return response(['likes Count' => $likesCount, 'user Liked' => 'no'], 200);
    }
}
