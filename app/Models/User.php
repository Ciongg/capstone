<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_RESPONDENT = 'respondent';
    public const ROLE_RESEARCHER = 'researcher';
    public const ROLE_INSTITUTION_ADMIN = 'institution_admin';
    public const ROLE_SUPER_ADMIN = 'super_admin';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'type',
        'institution_id',
        'profile_photo_path',
        'points',
        'trust_score',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function responses()
    {
        return $this->hasMany(Response::class);
    }

    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }

    public function tags()
    {
        return $this->belongsToMany(\App\Models\Tag::class)
                   ->withPivot('tag_name')
                   ->withTimestamps();
    }

    /**
     * The institution tags associated with the user
     */
    public function institutionTags()
    {
        return $this->belongsToMany(\App\Models\InstitutionTag::class, 'institution_user_tags')
                    ->withPivot('tag_name') // For denormalization
                    ->withTimestamps();
    }
    
    /**
     * Get the URL to the user's profile photo.
     *
     * @return string
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo_path) {
            // Use the asset() helper, assuming storage is linked
            // The path stored (e.g., 'profile-photos/image.jpg') needs '/storage/' prepended
            return asset('storage/' . $this->profile_photo_path); 
        }

        // Fallback to default image
        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF'; 
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function isRespondent(): bool
    {
        return $this->type === self::ROLE_RESPONDENT;
    }

    public function isResearcher(): bool
    {
        return $this->type === self::ROLE_RESEARCHER;
    }

    public function isInstitutionAdmin(): bool
    {
        return $this->type === self::ROLE_INSTITUTION_ADMIN;
    }

    public function isSuperAdmin(): bool
    {
        return $this->type === self::ROLE_SUPER_ADMIN;
    }

    // Add this method to your User model
    public function getNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if the user has a valid institution
     * @return bool
     */
    public function hasValidInstitution(): bool
    {
        return $this->institution_id && Institution::where('id', $this->institution_id)->exists();
    }
    
    /**
     * Check if the user is an institution admin with an invalid institution
     * @return bool
     */
    public function hasInvalidInstitution(): bool
    {
        if ($this->type !== 'institution_admin') {
            return false;
        }
        
        // Check if institution_id is null or institution doesn't exist
        return $this->institution_id === null || !Institution::where('id', $this->institution_id)->exists();
    }
    
    /**
     * Check if this user is a researcher with a .edu email but from an unrecognized institution
     */
    public function isDowngradedResearcher(): bool
    {
        if ($this->type !== 'respondent') {
            return false;
        }
        
        $emailDomain = Str::after($this->email, '@');
        return Str::endsWith($emailDomain, '.edu') || Str::endsWith($emailDomain, '.edu.ph');
    }
}
