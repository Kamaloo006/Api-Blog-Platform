<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Category;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        // show post category when sending request to show the post

        $posts = Post::all();
        if (!$posts->isEmpty())
            return response()->json($posts, 200);
        return response()->json(['message' => 'you are not authorized'], 401);
    }

    public function getMostLikesPosts()
    {
        $posts = Post::withCount('likes')->orderBy('likes_count', 'desc')->get();

        return response()->json(['posts ordered by likes count' => $posts], 200);
    }

    public function show(int $id)
    {
        $post = Post::findOrFail($id);
        if ($post->user_id === Auth::user()->id)
            return response()->json($post, 200);
        return response()->json(['message' => 'you are not authorized'], 401);
    }


    public function store(StorePostRequest $request)
    {
        $validated_data = $request->validated();
        $validated_data['user_id'] = Auth::id();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
            $validated_data['image'] = $path;
        }

        $post = Post::create($validated_data);

        return response()->json([
            'message' => 'Post Created Successfully',
            'post' => $post,
            'image_url' => isset($path) ? asset('storage/' . $path) : null,
        ], 201);
    }


    public function update(UpdatePostRequest $reqeust, int $id)
    {
        try {
            $post = Post::findOrFail($id);
            if ($post->user_id === Auth::user()->id) {
                $post->update($reqeust->validated());
                return response()->json(['message' => 'Post Updated Successfuly', 'new post' => $post], 200);
            }
            return response()->json(['message' => 'You are not authorized'], 401);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'id not found', 'message' => $e->getMessage()], 401);
        } catch (Exception $e) {
            return response()->json('error happened when updating', 401);
        }
    }
    public function destroy(int $id)
    {
        try {
            $post = Post::findOrFail($id);
            if ($post->user_id === Auth::user()->id) {
                $post->delete();
                return response()->json(['message' => "post deleted Successfuly"]);
            }
            return response()->json(['message' => 'You are not authorized'], 401);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'id not found', 'message' => $e->getMessage()]);
        } catch (Exception $e) {
            return response()->json('error happened when deleting');
        }
    }



    public function addCategoryToPost(Request $request, $post_id)
    {
        try {

            $post = Post::findOrFail($post_id);
            if (Auth::user()->id === $post->user_id) {
                $category = Category::findOrFail($request->category_id);
                $post->categories()->attach($request->category_id);
                return response()->json(['message' => 'category added successfuly'], 200);
            }
            return response()->json(['message' => 'You are not authorized'], 401);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'id not found', 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json('error happened', 403);
        }
    }

    public function deleteCategoryFromPost(Request $request, $post_id)
    {
        try {
            $post = Post::findOrFail($post_id);
            $category = Category::findOrFail($request->category_id);

            if (Auth::id() !== $post->user_id) {
                return response()->json(['message' => 'You are not authorized to modify this post'], 403);
            }


            if ($post->categories()->where('category_id', $request->category_id)->exists()) {
                $post->categories()->detach($request->category_id);

                return response()->json([
                    'message' => 'Category removed from post successfully'
                ], 200);
            }

            return response()->json([
                'message' => 'Category is not exist with this post'
            ], 404);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'id not found', 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json('error happened', 403);
        }
    }

    public function showPostCategories($post_id)
    {
        try {
            $post = Post::findOrFail($post_id);
            $post_categories = $post->categories()->get();
            return response()->json(['post categories' => $post_categories], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'id not found', 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json('error happened', 403);
        }
    }

    public function getCategoryPosts($category_id)
    {
        try {
            $category = Category::findOrFail($category_id);
            $category_posts = $category->posts()->get();
            return response()->json(['category posts' => $category_posts], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'id not found', 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json('error happened', 403);
        }
    }


    public function addToFavorites($post_id)
    {
        try {

            $current_user = Auth::user();
            $post = Post::where('id', $post_id)->firstOrFail();



            if (!$current_user->favoritePosts()->where('post_id', $post_id)->exists()) {
                $current_user->favoritePosts()->syncWithoutDetaching([$post->id]);
                return response()->json(['message' => 'post added to favorites'], 201);
            }
            return response()->json(['message' => 'post already in favorites'], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'id not found', 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json('error happened', 403);
        }
    }


    public function removeFromFavorites($post_id)
    {
        try {

            $current_user = Auth::user();
            $post = Post::where('id', $post_id)->firstOrFail();
            if (!$current_user->favoritePosts()->where('post_id', $post_id)->exists()) {
                return response()->json(['message' => 'post not added to favorites'], 400);
            }
            $current_user->favoritePosts()->detach([$post->id]);
            return response()->json(['message' => 'post removed from favorites'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'id not found', 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json('error happened', 403);
        }
    }

    public function getUserFavoritePosts($user_id)
    {
        try {

            $user = User::findOrFail($user_id);
            $current_user = Auth::user();
            if ($current_user->id === $user->id) {
                $user_favorites = $current_user->favoritePosts()->get();
                return response()->json(['user' => $current_user->name, 'favorite posts' => $user_favorites], 200);
            }
            return response()->json("user not authorized", 400);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'id not found', 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json('error happened', 403);
        }
    }
}
