<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// user authentication
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->middleware("auth:sanctum");

Route::middleware('auth:sanctum')->group(function () {

    // POSTS CRUD OPERATIONS
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/most_liked', [PostController::class, 'getMostLikesPosts']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::get('/posts', [PostController::class, 'index']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    Route::get('/posts/{id}', [PostController::class, 'show']);

    // Comments CRUD OPERATIONS
    Route::post('/posts/{id}/comments', [CommentController::class, 'store']);
    Route::put('/posts/{post_id}/comments/{comment_id}', [CommentController::class, 'update']);
    Route::delete('/posts/{post_id}/comments/{comment_id}', [CommentController::class, 'delete']);
    Route::post('/posts/{post_id}/comments/{comment_id}', [CommentController::class, 'storeReply']);


    // CATEGORY CRUD OPERATIONS
    Route::post('/categories', [CategoryController::class, 'store'])->middleware('checkUser');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->middleware('checkUser');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->middleware('checkUser');
    Route::get('/categories', [CategoryController::class, 'show']);

    // CATEGORIES-POSTS LINKING FUNCTIONS
    Route::post('/posts/{post_id}/categories', [PostController::class, 'addCategoryToPost']);
    Route::delete('/posts/{post_id}/categories', [PostController::class, 'deleteCategoryFromPost']);
    Route::get('/posts/{post_id}/categories', [PostController::class, 'showPostCategories']);
    Route::get('/categories/{id}/posts', [PostController::class, 'getCategoryPosts']);

    // LIKE - DISLIKE - GET LIKES OF POSTS
    Route::post('/posts/{post_id}/like', [LikeController::class, 'likePost']);
    Route::delete('/posts/{post_id}/like', [LikeController::class, 'unLikePost']);
    Route::get("/posts/{post_id}/likes", [LikeController::class, 'getPostLikes']);

    // LIKE - DISLIKE - GET LIKES OF COMMENTS
    Route::post('/posts/{post_id}/comments/{comment_id}/like', [LikeController::class, 'likeComment']);
    Route::delete('/posts/{post_id}/comments/{comment_id}/like', [LikeController::class, 'unLikeComment']);
    Route::get("/posts/{post_id}/comments/{comment_id}/likes", [LikeController::class, 'getCommentLikes']);

    // ADD POSTS TO FAVORITES
    Route::post('/posts/{post_id}/favorites', [PostController::class, 'addToFavorites']);
    Route::delete('/posts/{post_id}/favorites', [PostController::class, 'removeFromFavorites']);
    Route::get('/user/{user_id}/favoritesPosts', [PostController::class, 'getUserFavoritePosts']);

    // POSTS STATUS OPERATIONS
    Route::post('/posts/{post_id}/submit_review', [PostController::class, 'submitReview']);
    Route::get("/admin/pending_posts", [PostController::class, 'getPendingPosts'])->middleware('checkUser');
    Route::post("/admin/posts/{post_id}/approve", [PostController::class, 'approvePost'])->middleware('checkUser');
    Route::post("/admin/posts/{post_id}/reject", [PostController::class, 'rejectPost'])->middleware('checkUser');
});


Route::get('/posts/{post_id}/comments', [CommentController::class, 'getPostComments']);
