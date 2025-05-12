<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutionTagCategory extends Model
{
    use HasFactory;

    protected $fillable = ['institution_id', 'name'];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function tags()
    {
        return $this->hasMany(InstitutionTag::class, 'institution_tag_category_id');
    }
}