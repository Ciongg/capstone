<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutionTag extends Model
{
    use HasFactory;

    protected $fillable = ['institution_tag_category_id', 'name'];

    public function category()
    {
        return $this->belongsTo(InstitutionTagCategory::class, 'institution_tag_category_id');
    }

    public function surveys()
    {
        return $this->belongsToMany(Survey::class, 'institution_survey_tags')
            ->withPivot('tag_name')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'institution_user_tags')
            ->withPivot('tag_name')
            ->withTimestamps();
    }
}
