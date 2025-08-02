<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use App\Models\User;
use App\Models\TagCategory;
use App\Models\Tag;
use App\Models\InstitutionTagCategory;
use App\Models\InstitutionTag;
use App\Models\EmailVerification;
use App\Services\TrustScoreService;
use App\Services\BrevoService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\TestTimeService;

class ViewAbout extends Component
{
    public User $user;
    public $selectedTags = [];
    public $selectedInstitutionTags = [];
    public $tagCategories;
    public $institutionTagCategories = [];
    
    // Add these properties
    public $canUpdateDemographics = false;
    public $daysUntilDemographicsUpdateAvailable = 0;
    public $hoursUntilDemographicsUpdateAvailable = 0;
    public $minutesUntilDemographicsUpdateAvailable = 0;
    public $timeUntilUpdateText = '';
    public $demographicUpdateCooldownDays = 120; // Update to 4 months (120 days)
    
    // Trust score deduction info
    public $falseReportPenalty = 0;
    public $reportedResponseDeduction = 0;
    public $falseReportCount = 0;
    public $totalReportCount = 0;
    public $reportedResponseCount = 0;
    public $validResponseCount = 0;
    public $falseReportThresholdMet = false;
    public $reportedResponseThresholdMet = false;
    public $falseReportPercentage = 0;
    public $reportedResponsePercentage = 0;

    // OTP verification properties
    public $otp_code = '';
    public $showOtpModal = false;
    public $pendingEmail = '';
    public $showSuccess = false;
    public $resendCooldown = false;
    public $resendCooldownSeconds = 0;
    
    protected $listeners = [
        'refresh-about-view' => 'refreshAboutView',
    ];

    private TrustScoreService $trustScoreService;

    public function boot(TrustScoreService $trustScoreService)
    {
        $this->trustScoreService = $trustScoreService;
    }

    public function mount()
    {
        $this->user = Auth::user();
        $this->pendingEmail = $this->user->email;
        
        // Check if the user can update demographics
        $this->canUpdateDemographics = $this->user->canUpdateDemographicTags();
        $this->calculateTimeUntilUpdate();
        
        // Calculate potential trust score deductions using service
        $this->calculateTrustScoreDeductions();
        
        // Load regular tag categories and tags
        $this->tagCategories = TagCategory::with('tags')->get();
        
        // Load user's selected tags
        $userTags = $this->user->tags()->pluck('tags.id')->toArray();
        
        // Organize selected tags by category
        foreach ($this->tagCategories as $category) {
            $categoryTags = $category->tags->pluck('id')->toArray();
            $userTagsForCategory = array_intersect($userTags, $categoryTags);
            
            if (!empty($userTagsForCategory)) {
                $this->selectedTags[$category->id] = reset($userTagsForCategory);
            }
        }
        
        // If user belongs to an institution, load institution-specific tag categories
        if ($this->user->institution_id) {
            $this->institutionTagCategories = InstitutionTagCategory::where('institution_id', $this->user->institution_id)
                ->with('tags')
                ->get();
            
            // Load user's selected institution tags
            $userInstitutionTags = $this->user->institutionTags()->pluck('institution_tags.id')->toArray();
            
            // Organize selected institution tags by category
            foreach ($this->institutionTagCategories as $category) {
                // Get all tags belonging to this category
                $categoryTags = $category->tags->pluck('id')->toArray();
                
                // Find which of the user's tags belong to this category
                $userTagsForCategory = array_intersect($userInstitutionTags, $categoryTags);
                
                if (!empty($userTagsForCategory)) {
                    $this->selectedInstitutionTags[$category->id] = reset($userTagsForCategory);
                }
            }
        }
    }

    /**
     * Calculate time until demographic update is available in a more human-readable format
     */
    protected function calculateTimeUntilUpdate()
    {
        if ($this->canUpdateDemographics) {
            $this->timeUntilUpdateText = 'Available now';
            return;
        }

        $cooldownDays = 120;
        $nextUpdateDate = $this->user->demographic_tags_updated_at->addDays($cooldownDays);
        $now = TestTimeService::now();
        
        // Check if we're very close to the target time (less than 30 seconds away)
        if ($now->diffInSeconds($nextUpdateDate, false) < 30) {
            $this->canUpdateDemographics = true;
            $this->timeUntilUpdateText = 'Available now';
            return;
        }
        
        // Calculate time differences and round to integers
        $this->daysUntilDemographicsUpdateAvailable = (int) floor($now->diffInDays($nextUpdateDate, false));
        $this->hoursUntilDemographicsUpdateAvailable = (int) floor($now->diffInHours($nextUpdateDate, false) % 24);
        $this->minutesUntilDemographicsUpdateAvailable = (int) floor($now->diffInMinutes($nextUpdateDate, false) % 60);
        
        // Create human-readable text with proper rounding
        if ($this->daysUntilDemographicsUpdateAvailable > 0) {
            $this->timeUntilUpdateText = "Available in {$this->daysUntilDemographicsUpdateAvailable} " . 
                ($this->daysUntilDemographicsUpdateAvailable == 1 ? 'day' : 'days');
        } elseif ($now->diffInHours($nextUpdateDate, false) > 0) {
            $hours = (int) floor($now->diffInHours($nextUpdateDate, false));
            $this->timeUntilUpdateText = "Available in {$hours} " . ($hours == 1 ? 'hour' : 'hours');
        } else {
            // If we're less than 1 minute away, just show "Available now"
            if ($now->diffInMinutes($nextUpdateDate, false) < 1) {
                $this->canUpdateDemographics = true;
                $this->timeUntilUpdateText = 'Available now';
            } else {
                $minutes = max(1, (int) ceil($now->diffInMinutes($nextUpdateDate, false)));
                $this->timeUntilUpdateText = "Available in {$minutes} " . ($minutes == 1 ? 'minute' : 'minutes');
            }
        }
    }
    
    protected function otpRules(): array
    {
        return [
            'otp_code' => 'required|string|size:6',
        ];
    }

    public function sendOtp()
    {
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        EmailVerification::updateOrCreate(
            ['email' => $this->pendingEmail],
            [
                'otp_code' => $otpCode,
                'expires_at' => TestTimeService::now()->addMinutes(10),
            ]
        );
        $brevoService = new BrevoService();
        $emailSent = $brevoService->sendOtpEmail($this->pendingEmail, $otpCode);
        if (!$emailSent) {
            session()->flash('error', 'Failed to send verification email. Please try again.');
            return;
        }
        $this->dispatch('open-modal', name: 'otp-verification');
        session()->flash('success', 'Verification code sent to your email!');
    }

    public function verifyOtp()
    {
        $this->validate($this->otpRules());
        $emailVerification = EmailVerification::where('email', $this->pendingEmail)
            ->where('otp_code', $this->otp_code)
            ->first();
        if (!$emailVerification || $emailVerification->isExpired()) {
            $this->addError('otp_code', 'Invalid or expired verification code. Please try again.');
            return;
        }
        // Mark user as verified
        $this->user->email_verified_at = TestTimeService::now();
        $this->user->save();
        $emailVerification->delete();
        $this->showSuccess = true;
        $this->dispatch('otp-verified-success');
        $this->dispatch('refresh-profile-view');
    }

    public function resendOtp()
    {
        if ($this->resendCooldown) {
            return;
        }
        if (empty($this->pendingEmail)) {
            session()->flash('error', 'No pending email verification found.');
            return;
        }
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        EmailVerification::updateOrCreate(
            ['email' => $this->pendingEmail],
            [
                'otp_code' => $otpCode,
                'expires_at' => TestTimeService::now()->addMinutes(10),
            ]
        );
        $brevoService = new BrevoService();
        $emailSent = $brevoService->sendOtpEmail($this->pendingEmail, $otpCode);
        if ($emailSent) {
            session()->flash('success', 'New verification code sent to your email!');
            $this->startResendCooldown();
        } else {
            session()->flash('error', 'Failed to send verification email. Please try again.');
        }
    }

    public function startResendCooldown()
    {
        $this->resendCooldown = true;
        $this->resendCooldownSeconds = 60;
        $this->dispatch('start-resend-cooldown');
    }

    public function decrementCooldown()
    {
        if ($this->resendCooldown && $this->resendCooldownSeconds > 0) {
            $this->resendCooldownSeconds--;
            if ($this->resendCooldownSeconds <= 0) {
                $this->resendCooldown = false;
            }
        }
    }

    /**
     * Calculate all trust score deductions for the current user
     */
    private function calculateTrustScoreDeductions()
    {
        // Get false report calculations
        $falseReportCalc = $this->trustScoreService->calculateFalseReportPenalty($this->user->id);
        $this->falseReportCount = $falseReportCalc['dismissed_reports'];
        $this->totalReportCount = $falseReportCalc['total_reports'];
        $this->falseReportPercentage = $falseReportCalc['percentage'];
        $this->falseReportThresholdMet = $falseReportCalc['threshold_met'];
        $this->falseReportPenalty = abs($falseReportCalc['penalty_amount']);
        
        // Get reported response calculations
        $reportedResponseCalc = $this->trustScoreService->calculateReportedResponseDeduction($this->user->id);
        $this->reportedResponseCount = $reportedResponseCalc['valid_reports'];
        $this->validResponseCount = $reportedResponseCalc['total_responses'];
        $this->reportedResponsePercentage = $reportedResponseCalc['percentage'];
        $this->reportedResponseThresholdMet = $reportedResponseCalc['threshold_met'];
        $this->reportedResponseDeduction = abs($reportedResponseCalc['penalty_amount']);
    }

    public function refreshAboutView()
    {
        $this->user = $this->user->fresh();
        
        // Update demographic update status
        $this->canUpdateDemographics = $this->user->canUpdateDemographicTags();
        $this->calculateTimeUntilUpdate();
        
        // Reload tag and institution data as in mount
        $this->tagCategories = TagCategory::with('tags')->get();
        $userTags = $this->user->tags()->pluck('tags.id')->toArray();
        $this->selectedTags = [];
        foreach ($this->tagCategories as $category) {
            $categoryTags = $category->tags->pluck('id')->toArray();
            $userTagsForCategory = array_intersect($userTags, $categoryTags);
            if (!empty($userTagsForCategory)) {
                $this->selectedTags[$category->id] = reset($userTagsForCategory);
            }
        }
        $this->institutionTagCategories = [];
        if ($this->user->institution_id) {
            $this->institutionTagCategories = InstitutionTagCategory::where('institution_id', $this->user->institution_id)
                ->with('tags')
                ->get();
            $userInstitutionTags = $this->user->institutionTags()->pluck('institution_tags.id')->toArray();
            $this->selectedInstitutionTags = [];
            foreach ($this->institutionTagCategories as $category) {
                $categoryTags = $category->tags->pluck('id')->toArray();
                $userTagsForCategory = array_intersect($userInstitutionTags, $categoryTags);
                if (!empty($userTagsForCategory)) {
                    $this->selectedInstitutionTags[$category->id] = reset($userTagsForCategory);
                }
            }
        }
    }

    public function saveTags()
    {
        // Check if user can update tags
        if (!$this->canUpdateDemographics) {
            session()->flash('error', 'You cannot update your demographic information at this time. Please try again in ' . 
                $this->daysUntilDemographicsUpdateAvailable . ' days.');
            return;
        }
        
        // Process regular tags
        $tagsToSync = [];
        foreach ($this->selectedTags as $categoryId => $tagId) {
            if (!empty($tagId)) {
                // Get tag name for denormalization
                $tag = Tag::find($tagId);
                if ($tag) {
                    $tagsToSync[$tagId] = ['tag_name' => $tag->name];
                }
            }
        }
        
        // Sync regular tags
        $this->user->tags()->sync($tagsToSync);
        
        // Process institution tags if applicable
        if ($this->user->institution_id && !empty($this->selectedInstitutionTags)) {
            $institutionTagsToSync = [];
            foreach ($this->selectedInstitutionTags as $categoryId => $tagId) {
                if (!empty($tagId)) {
                    // Get tag name for denormalization
                    $tag = InstitutionTag::find($tagId);
                    if ($tag) {
                        $institutionTagsToSync[$tagId] = ['tag_name' => $tag->name];
                    }
                }
            }
            
            // Sync institution tags
            $this->user->institutionTags()->sync($institutionTagsToSync);
        }
        
        // Update the demographic_tags_updated_at timestamp
        $this->user->demographic_tags_updated_at = TestTimeService::now();
        $this->user->save();
        
        // Update local properties
        $this->canUpdateDemographics = $this->user->canUpdateDemographicTags();
        $this->daysUntilDemographicsUpdateAvailable = $this->user->getDaysUntilDemographicTagsUpdateAvailable();
        
        session()->flash('tags_saved', 'Your demographic information has been updated successfully! You can update it again in ' . 
            $this->demographicUpdateCooldownDays . ' days.');
    }

    public function render()
    {
        return view('livewire.profile.view-about', [
            'falseReportPenalty' => $this->falseReportPenalty,
            'reportedResponseDeduction' => $this->reportedResponseDeduction,
            'falseReportCount' => $this->falseReportCount,
            'totalReportCount' => $this->totalReportCount,
            'reportedResponseCount' => $this->reportedResponseCount,
            'validResponseCount' => $this->validResponseCount,
            'falseReportThresholdMet' => $this->falseReportThresholdMet,
            'reportedResponseThresholdMet' => $this->reportedResponseThresholdMet,
            'falseReportPercentage' => $this->falseReportPercentage,
            'reportedResponsePercentage' => $this->reportedResponsePercentage,
            'canUpdateDemographics' => $this->canUpdateDemographics,
            'daysUntilDemographicsUpdateAvailable' => $this->daysUntilDemographicsUpdateAvailable,
            'timeUntilUpdateText' => $this->timeUntilUpdateText,
        ]);
    }
}