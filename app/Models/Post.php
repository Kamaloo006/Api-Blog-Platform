<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';
    protected $fillable = ['title', 'content', 'image', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_post');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function favoritesByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }
}
