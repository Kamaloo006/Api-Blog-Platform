<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isEmpty;

class PostController extends Controller
{
    public function index()
    {


        $posts = Post::all()->where('user_id', Auth::user()->id);
        if (!$posts->isEmpty())
            return response()->json($posts, 200);
        return response()->json(['message' => 'you are not authorized'], 401);
    }

    public function show(int $id)
    {
        $post = Post::findOrFail($id);
        if ($post->user_id === Auth::user()->id)
            return response()->json($post, 200);
        return response()->json(['message' => 'you are not authorized'], 401);
    }

    public function store(StorePostRequest $reqeust)
    {

        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthorized. Please log in first.'
            ], 401);
        }

        $user_id = Auth::user()->id;
        $validated_data = $reqeust->validated();
        $validated_data['user_id'] = $user_id;

        $post = Post::create($validated_data);

        return response()->json([
            'message' => 'Post Created Successfully',
            'post' => $post
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
}
