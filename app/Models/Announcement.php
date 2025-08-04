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
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'institution_id' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($announcement) {
            $announcement->uuid = (string) Str::uuid();
        });
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)
            ->where(function($q) {
                $now = Carbon::now();
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', $now);
            })
            ->where(function($q) {
                $now = Carbon::now();
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $now);
            });
    }

    public function scopeForUser($query, $user)
    {
        if (!$user) {
            return $query->where('target_audience', 'public');
        }

        return $query->where(function ($query) use ($user) {
            $query->where('target_audience', 'public');
            
            if ($user->institution_id) {
                $query->orWhere(function ($subquery) use ($user) {
                    $subquery->where('target_audience', 'institution_specific')
                             ->where('institution_id', $user->institution_id);
                });
            }
        });
    }
}
