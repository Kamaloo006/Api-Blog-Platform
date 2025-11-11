<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Category;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function Laravel\Prompts\search;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::where('status', 'published')->get();

        if (!$posts->isEmpty()) {
            return PostResource::collection($posts);
        }

        return response()->json(['message' => 'No published posts found'], 404);
    }

    public function getMostLikesPosts()
    {
        $posts = Post::withCount('likes')->orderBy('likes_count', 'desc')->get();

        return PostResource::collection($posts);
    }

    public function show(int $id)
    {
        $post = Post::findOrFail($id);

        if ($post->status === 'published') {
            return new PostResource($post);
        }

        if (Auth::check() && Auth::id() === $post->user_id) {
            return new PostResource($post);
        }

        return response()->json(['message' => 'you are not authorized'], 401);
    }


    public function store(StorePostRequest $request)
    {
        $validated_data = $request->validated();
        $current_user = Auth::user();
        $validated_data['user_id'] = $current_user->id;

        $post_status = $request->status;
        // check if user not admin and post status isn't draft
        if ($current_user->role !== 'admin' && ($post_status === 'pending' || $post_status === 'archived' || $post_status === 'published')) {
            return response()->json(['message' => 'the post should be as draft then it will be apporved'], 400);
        }

        $validated_data['status'] = 'draft';


        if ($current_user->role === 'admin') {
            $validated_data['status'] = 'published';
        }


        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
            $validated_data['image'] = $path;
        }

        $post = Post::create($validated_data);
        $user = $post->user;
        $user->role = 'author';
        $user->save();


        return response()->json([
            'message' => 'Post Created Successfully',
            'post' => new PostResource($post),
            'image_url' => isset($path) ? asset('storage/' . $path) : null,
        ], 201);
    }


    public function update(UpdatePostRequest $reqeust, int $id)
    {
        try {

            $post = Post::findOrFail($id);

            if ($post->user_id === Auth::user()->id) {
                $post->update($reqeust->validated());
                return response()->json([
                    'message' => 'Post Updated Successfully',
                    'new_post' => new PostResource($post)
                ], 200);
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
            return response()->json([
                'category_posts' => PostResource::collection($category_posts)
            ], 200);
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
                return response()->json([
                    'user' => $current_user->name,
                    'favorite_posts' => PostResource::collection($user_favorites)
                ], 200);
            }
            return response()->json("user not authorized", 400);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'id not found', 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json('error happened', 403);
        }
    }

    // user submit review, it goes pending. after that the admin will approve or reject
    public function submitReview($post_id)
    {
        $post = Post::findOrFail($post_id);
        $current_user = Auth::user();

        if ($current_user->id !== $post->user_id) {
            return response()->json(['message' => 'you are not authorized'], 403);
        }

        if ($current_user->role === 'admin') return response()->json(['message' => 'your post already been published'], 200);

        if ($post->status === 'pending') {
            return response()->json(['message' => 'post is already pending review'], 400);
        }

        if ($post->status === 'published') {
            return response()->json(['message' => 'post is already published'], 400);
        }

        $post->status = 'pending';
        $post->save();

        return response()->json(['message' => 'your post has been submitted successfuly, waiting for the admin to approve'], 200);
    }

    // admin gets pending posts
    public function getPendingPosts()
    {
        $posts = Post::where('status', 'pending')->get();
        return PostResource::collection($posts);
    }

    // approve post by admin
    public function approvePost($post_id)
    {
        $post = Post::findOrFail($post_id);
        $user = $post->user;

        if ($user->role === 'user') {
            $user->role = 'author';
            $user->save();
        }

        if ($post->status === 'published')
            return response()->json(['message' => 'post is already published'], 400);

        if ($post->status === 'draft' || $post->status === 'archived')
            return response()->json(['message' => 'post is not in pending review yet'], 400);

        $post->status = 'published';
        $post->save();

        return response()->json([
            'user' => $post->user->name,
            'post' => new PostResource($post),
            'message' => 'post has been published successfully'
        ], 200);
    }


    // reject post and send it to draft
    public function rejectPost($post_id)
    {
        $post = Post::findOrFail($post_id);

        if ($post->status === 'published')
            return response()->json(['message' => 'post is already published'], 400);

        if ($post->status === 'draft' || $post->status === 'archived')
            return response()->json(['message' => 'post is not in pending review yet'], 400);

        $post->status = 'draft';
        $post->save();

        return response()->json(['user' => $post->user->name, 'message' => 'the admin has rejected your post, please update it and try again'], 400);
    }




    public function search(Request $request)
    {
        try {

            $search = $request->query('q');
            $categoryId = $request->query('category');


            Category::findOrFail($categoryId);
            if (!$search && !$categoryId) {
                return response()->json([
                    'message' => 'Please provide a search query using ?q=keyword or ?category=id'
                ], 400);
            }


            $query = Post::where('status', 'published');


            // check by title or content
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")->orWhere('content', 'LIKE', "%{$search}%");
                });
            }


            // check by category id
            if ($categoryId) {
                $query->whereHas('categories', function ($q) use ($categoryId) {
                    $q->where('categories.id', $categoryId);
                });
            }


            $posts = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'search_query' => $search,
                'category_id' => $categoryId,
                'results_count' => $posts->count(),
                'posts' => PostResource::collection($posts)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'category not found exception', 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'error happened while searching'], 400);
        }
    }
}
