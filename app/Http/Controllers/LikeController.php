<?php

namespace App\Http\Controllers;

use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function likePost($post_id)
    {
        $existingLike = Like::where('post_id', $post_id)->where('user_id', Auth::user()->id)->first();

        if ($existingLike) {
            return response()->json(['message' => 'already liked'], 400);
        }

        Like::create([
            'post_id' => $post_id,
            'user_id' => Auth::user()->id
        ]);
        return response()->json(['message' => 'Post Liked'], 200);
    }

    public function unLikePost($post_id)
    {
        $like = Like::where('post_id', $post_id)->where('user_id', Auth::user()->id)->delete();
        return response()->json(['message' => 'Post Un Liked'], 200);
    }

    public function getPostLikes($post_id)
    {
        $likesCount = Like::where('post_id', $post_id)->count();
        $userLiked = Auth::check() ? Like::where('user_id', Auth::user()->id)->where('post_id', $post_id)->exists() : false;

        if ($userLiked)
            return response(['likes Count' => $likesCount, 'user Liked' => 'yes'], 200);
        return response(['likes Count' => $likesCount, 'user Liked' => 'no'], 200);
    }
}
