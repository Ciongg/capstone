<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name', 'tag_category_id'];

    public function category()
    {
        return $this->belongsTo(TagCategory::class, 'tag_category_id');
    }

    public function surveys()
    {
        return $this->belongsToMany(Survey::class, 'survey_tag') // Fixed: Changed 'tags' to 'survey_tags'
            ->withPivot('tag_name')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
