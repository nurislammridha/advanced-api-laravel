<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = ['title', 'description', 'category_id', 'user_id', 'image'];

    //One blog belongs to one category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    //One blog belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    //One blog has many comments
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    //One blog has many likes
    public function likes()
    {
        return $this->hasMany(Like::class);
    }
}
