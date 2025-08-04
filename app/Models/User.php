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
use App\Services\TestTimeService;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Traits\HasRoles;
use App\Models\Traits\HasUuid;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles, HasUuid;

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
        'uuid',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'points',
        'trust_score',
        'experience_points',
        'account_level', // Add this line
        'type',
        'profile_photo_path',
        'institution_id',
        'is_active',
        'email_verified_at',
        'last_active_at', // Add this line
        'demographic_tags_updated_at', // Add this new field
        'institution_demographic_tags_updated_at', // Add this field to fillable
        'profile_updated_at', // Add this new field
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
            'last_active_at' => 'datetime', // Fix typo here
            'demographic_tags_updated_at' => 'datetime', // Add this new cast
            'institution_demographic_tags_updated_at' => 'datetime', // Add this cast
            'profile_updated_at' => 'datetime', // Add this new cast
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
    

// Keep these methods as convenient proxies to the service
/**
 * Add experience points to the user and handle level-up logic
 * 
 * @param int $xp
 * @return array
 */
public function addExperiencePoints($xp)
{
    return UserExperienceService::addUserExperiencePoints($this, $xp);
}

/**
 * Get current level based on XP.
 *
 * @return int
 */
public function getLevel(): int
{
    return UserExperienceService::getUserLevel($this);
}

/**
 * Get progress to next level (percentage).
 *
 * @return float
 */
public function getLevelProgressPercentage(): float
{
    return UserExperienceService::getUserLevelProgressPercentage($this);
}

/**
 * Get XP required for next level.
 *
 * @return int
 */
public function getXpRequiredForNextLevel(): int
{
    return UserExperienceService::getXpRequiredForUserNextLevel($this);
}

    public function rewardRedemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function userVouchers(): HasMany
    {
        return $this->hasMany(UserVoucher::class);
    }

    public function vouchers(): BelongsToMany
    {
        return $this->belongsToMany(Voucher::class, 'user_vouchers')->withTimestamps()->withPivot('status', 'used_at', 'reward_redemption_id');
    }
    
    /**
     * Check if the user can update their demographic tags
     * 
     * @return bool
     */
    public function canUpdateDemographicTags(): bool
    {
        // If user has never updated demographics, they can update them
        if (!$this->demographic_tags_updated_at) {
            return true;
        }
        
        // Define the cooldown period in days (4 months)
        $cooldownDays = 120;
        
        // Check if the cooldown period has passed
        return $this->demographic_tags_updated_at->addDays($cooldownDays)->isPast(TestTimeService::now());
    }
    
    /**
     * Get days until demographic tags can be updated again
     * 
     * @return int
     */
    public function getDaysUntilDemographicTagsUpdateAvailable(): int
    {
        if ($this->canUpdateDemographicTags()) {
            return 0;
        }
        
        $cooldownDays = 120;
        $nextUpdateDate = $this->demographic_tags_updated_at->addDays($cooldownDays);
        
        return TestTimeService::now()->diffInDays($nextUpdateDate, false);
    }
    
    /**
     * Check if the user can update their profile
     * 
     * @return bool
     */
    public function canUpdateProfile(): bool
    {
        // If user has never updated profile, they can update it
        if (!$this->profile_updated_at) {
            return true;
        }
        
        // Define the cooldown period in days (120 days = 4 months)
        $cooldownDays = 120;
        
        // Check if the cooldown period has passed
        return $this->profile_updated_at->addDays($cooldownDays)->isPast(TestTimeService::now());
    }
    
    /**
     * Get days until profile can be updated again
     * 
     * @return int
     */
    public function getDaysUntilProfileUpdateAvailable(): int
    {
        if ($this->canUpdateProfile()) {
            return 0;
        }
        
        $cooldownDays = 120;
        $nextUpdateDate = $this->profile_updated_at->addDays($cooldownDays);
        
        return TestTimeService::now()->diffInDays($nextUpdateDate, false);
    }
    
    // Add these methods to your User model

    /**
     * Check if user can update institution demographic tags
     */
    public function canUpdateInstitutionDemographicTags(): bool
    {
        // If the user has never updated institution demographic tags, they can update them
        if (!$this->institution_demographic_tags_updated_at) {
            return true;
        }
        
        // Otherwise, check if the cooldown period has passed (120 days)
        $cooldownDays = 120;
        $nextUpdateDate = $this->institution_demographic_tags_updated_at->addDays($cooldownDays);
        return TestTimeService::now()->gte($nextUpdateDate);
    }

    /**
     * Get days until institution demographic tags update is available
     */
    public function getDaysUntilInstitutionDemographicTagsUpdateAvailable(): int
    {
        if ($this->canUpdateInstitutionDemographicTags()) {
            return 0;
        }
        
        $cooldownDays = 120;
        $nextUpdateDate = $this->institution_demographic_tags_updated_at->addDays($cooldownDays);
        return max(0, TestTimeService::now()->diffInDays($nextUpdateDate, false));
    }
}
