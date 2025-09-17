<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name'];

    //One category has many blogs
    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }
}
