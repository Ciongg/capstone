<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasUuid;

class Response extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'survey_id',
        'user_id',
        'reported',
    ];

    protected $casts = [
        'reported' => 'boolean',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Get the snapshot record associated with this response.
     */
    public function snapshot()
    {
        return $this->hasOne(ResponseSnapshot::class);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
}
