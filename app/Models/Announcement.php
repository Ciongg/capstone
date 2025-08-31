<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image_path',
        'target_audience',
        'institution_id',
        'active',
        'url', 
        'start_date',
        'end_date',
        'survey_id', 
    ];

    //converts attribute values when reading/writing from the database
    protected $casts = [
        'institution_id' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];


    //lifecycle hook runs when model is initialized
    protected static function boot()
    {
        parent::boot(); //calls default behavior of the parent Model

        static::creating(function ($announcement) {
            $announcement->uuid = (string) Str::uuid(); //generate uuid of the announcement when its created automatically
        });
    }

    public function institution() //one to many relationship
    {
        return $this->belongsTo(Institution::class);
    }

    public function survey() // oine to many relationship
    {
        return $this->belongsTo(Survey::class);
    }

    
}
