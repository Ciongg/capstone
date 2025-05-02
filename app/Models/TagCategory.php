<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagCategory extends Model
{
    protected $fillable = ['name'];

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }
}
