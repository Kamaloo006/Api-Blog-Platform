<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->middleware("auth:sanctum");

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::get('/posts', [PostController::class, 'index']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    Route::get('/posts/{id}', [PostController::class, 'show']);

    Route::post('/posts/{id}/comments', [CommentController::class, 'store']);
    Route::put('/posts/{post_id}/comments/{comment_id}', [CommentController::class, 'update']);
    Route::delete('/posts/{post_id}/comments/{comment_id}', [CommentController::class, 'delete']);

    Route::post('/posts/{post_id}/comments/{comment_id}', [CommentController::class, 'storeReply']);

    Route::put('/posts/{post_id}/comments/{comment_id}/reply', [CommentController::class, 'updateReply']);
});


Route::get('/posts/{post_id}/comments', [CommentController::class, 'getPostComments']);
