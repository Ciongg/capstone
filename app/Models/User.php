<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Services\UserExperienceService;

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
        'password',
        'phone_number',
        'points',
        'trust_score',
        'experience_points', // Add experience_points to fillable
        'type',
        'profile_photo_path',
        'institution_id',
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
    
    /**
     * Add experience points to the user.
     *
     * @param int $amount
     * @return array Information about level change, perks, etc.
     */
    public function addExperiencePoints(int $amount): array
    {
        $previousXp = $this->experience_points;
        $previousLevel = UserExperienceService::calculateLevel($previousXp);
        
        // Add XP
        $this->experience_points += $amount;
        
        // Calculate new level
        $currentLevel = UserExperienceService::calculateLevel($this->experience_points);
        
        $result = [
            'previous_xp' => $previousXp,
            'current_xp' => $this->experience_points,
            'previous_level' => $previousLevel,
            'current_level' => $currentLevel,
            'leveled_up' => $currentLevel > $previousLevel,
            'perks' => [],
        ];
        
        // If leveled up, update title and apply perks
        if ($result['leveled_up']) {
            // Update title
            $this->title = UserExperienceService::getTitleForLevel($currentLevel);
            
            // Apply perks
            $result['perks'] = UserExperienceService::applyLevelPerks($this, $previousLevel, $currentLevel);
        }
        
        // Save changes
        $this->save();
        
        return $result;
    }
    
    /**
     * Get current level based on XP.
     *
     * @return int
     */
    public function getLevel(): int
    {
        return UserExperienceService::calculateLevel($this->experience_points);
    }
    
    /**
     * Get progress to next level (percentage).
     *
     * @return float
     */
    public function getLevelProgressPercentage(): float
    {
        $currentLevel = $this->getLevel();
        $currentLevelXp = UserExperienceService::xpRequiredForLevel($currentLevel);
        $nextLevelXp = UserExperienceService::xpRequiredForLevel($currentLevel + 1);
        
        $xpForThisLevel = $this->experience_points - $currentLevelXp;
        $xpRequiredForNextLevel = $nextLevelXp - $currentLevelXp;
        
        return min(100, round(($xpForThisLevel / $xpRequiredForNextLevel) * 100, 1));
    }
    
    /**
     * Get XP required for next level.
     *
     * @return int
     */
    public function getXpRequiredForNextLevel(): int
    {
        $currentLevel = $this->getLevel();
        return UserExperienceService::xpRequiredForLevel($currentLevel + 1);
    }
    
    /**
     * Update user's title based on current XP level.
     *
     * @return bool True if title was updated
     */
    public function updateTitle(): bool
    {
        $currentLevel = $this->getLevel();
        $newTitle = UserExperienceService::getTitleForLevel($currentLevel);
        
        if ($this->title !== $newTitle) {
            $this->title = $newTitle;
            $this->save();
            return true;
        }
        
        return false;
    }
}
