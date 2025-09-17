<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = ['title', 'description', 'category_id', 'user_id'];

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
}
