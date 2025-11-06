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
    public array $selectedTags = [];
    public array $originalSelectedTags = [];
    public array $demographicLocks = [];
    public array $demographicLockInfo = [];
    public bool $canUpdateDemographics = true;
    public string $timeUntilUpdateText = '';
    public $tagCategories = [];
    public array $selectedInstitutionTags = [];
    public array $originalSelectedInstitutionTags = [];
    public array $institutionLocks = [];
    public array $institutionLockInfo = [];
    public bool $canUpdateInstitutionDemographics = true;
    public string $timeUntilInstitutionUpdateText = '';
    public $institutionTagCategories = [];
    
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
    
    public $demographicUpdateCooldownDays = 120; // Update to 4 months (120 days)

    protected $listeners = [
        'refresh-about-view' => 'refreshAboutView',
    ];

    private TrustScoreService $trustScoreService;

    public function boot(TrustScoreService $trustScoreService)
    {
        $this->trustScoreService = $trustScoreService;
    }

    public function mount(User $user)
    {
        $this->user = $user;
        $this->demographicLocks = $user->demographic_tag_cooldowns ?? [];
        $this->institutionLocks = $user->institution_demographic_tag_cooldowns ?? [];
        $this->loadSelectedTags();
        $this->refreshLockStates();
        $this->calculateTrustScoreDeductions();
    }

    protected function loadSelectedTags(): void
    {
        $this->tagCategories = TagCategory::with('tags')->get();
        $this->selectedTags = [];
        $this->originalSelectedTags = [];

        $userTags = $this->user->tags()->pluck('tags.id')->toArray();

        foreach ($this->tagCategories as $category) {
            $categoryTagIds = $category->tags->pluck('id')->toArray();
            $picked = array_values(array_intersect($userTags, $categoryTagIds));

            if (!empty($picked)) {
                $this->selectedTags[$category->id] = $picked[0];
            }
        }

        $this->originalSelectedTags = $this->selectedTags;

        $this->institutionTagCategories = collect();
        $this->selectedInstitutionTags = [];
        $this->originalSelectedInstitutionTags = [];

        if ($this->user->institution_id) {
            $this->institutionTagCategories = InstitutionTagCategory::where('institution_id', $this->user->institution_id)
                ->with('tags')
                ->get();

            $userInstitutionTags = $this->user->institutionTags()->pluck('institution_tags.id')->toArray();

            foreach ($this->institutionTagCategories as $category) {
                $categoryTagIds = $category->tags->pluck('id')->toArray();
                $picked = array_values(array_intersect($userInstitutionTags, $categoryTagIds));

                if (!empty($picked)) {
                    $this->selectedInstitutionTags[$category->id] = $picked[0];
                }
            }

            $this->originalSelectedInstitutionTags = $this->selectedInstitutionTags;
        }
    }

    protected function refreshLockStates(): void
    {
        $this->demographicLockInfo = $this->formatLockCollection($this->demographicLocks);
        $this->institutionLockInfo = $this->formatLockCollection($this->institutionLocks);

        $this->canUpdateDemographics = $this->hasEditableField($this->tagCategories, $this->demographicLockInfo);
        $this->timeUntilUpdateText = $this->summarizeCooldown($this->user->demographic_tags_updated_at);

        $this->canUpdateInstitutionDemographics = $this->hasEditableField($this->institutionTagCategories, $this->institutionLockInfo);
        $this->timeUntilInstitutionUpdateText = $this->summarizeCooldown($this->user->institution_demographic_tags_updated_at);
    }

    protected function formatLockCollection(array $locks): array
    {
        $now = TestTimeService::now();

        return collect($locks)
            ->filter()
            ->map(function ($storedAt) use ($now) {
                $lockedAt = Carbon::parse($storedAt);
                $unlockAt = $lockedAt->copy()->addMonths(4);

                return [
                    'locked' => $unlockAt->greaterThan($now),
                    'locked_until' => $unlockAt->clone()->setTimezone($now->timezone),
                ];
            })
            ->toArray();
    }

    protected function hasEditableField($categories, array $info): bool
    {
        return collect($categories)->contains(function ($category) use ($info) {
            $details = $info[$category->id] ?? null;
            return !($details['locked'] ?? false);
        });
    }

    protected function summarizeCooldown(?Carbon $timestamp): string
    {
        if (!$timestamp) {
            return 'Last edited on —';
        }

        return 'Last edited on ' . $timestamp->format('M d, Y');
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
        $this->demographicLocks = $this->user->demographic_tag_cooldowns ?? [];
        $this->institutionLocks = $this->user->institution_demographic_tag_cooldowns ?? [];

        $this->loadSelectedTags();
        $this->refreshLockStates();
        $this->calculateTrustScoreDeductions();
    }

    /**
     * Check if demographic tags have been changed
     */
    public function demographicTagsChanged()
    {
        // If original and current arrays have different lengths, changes were made
        if (count($this->selectedTags) != count($this->originalSelectedTags)) {
            return true;
        }
        
        // Check for any differences between original and current selections
        foreach ($this->selectedTags as $categoryId => $tagId) {
            // Check if this category exists in original tags
            if (!isset($this->originalSelectedTags[$categoryId])) {
                return true;
            }
            
            // Check if the tag for this category has changed
            if ($this->originalSelectedTags[$categoryId] != $tagId) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if institution demographic tags have been changed
     */
    public function institutionDemographicTagsChanged()
    {
        // If original and current arrays have different lengths, changes were made
        if (count($this->selectedInstitutionTags) != count($this->originalSelectedInstitutionTags)) {
            return true;
        }
        
        // Check for any differences between original and current selections
        foreach ($this->selectedInstitutionTags as $categoryId => $tagId) {
            // Check if this category exists in original tags
            if (!isset($this->originalSelectedInstitutionTags[$categoryId])) {
                return true;
            }
            
            // Check if the tag for this category has changed
            if ($this->originalSelectedInstitutionTags[$categoryId] != $tagId) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Save regular demographic tags
     */
    public function saveDemographicTags()
    {
        $locked = collect($this->selectedTags)->filter(function ($tagId, $categoryId) {
            $details = $this->demographicLockInfo[$categoryId] ?? null;
            $locked = $details['locked'] ?? false;
            $original = $this->originalSelectedTags[$categoryId] ?? null;
            return $locked && $tagId !== $original;
        });

        if ($locked->isNotEmpty()) {
            session()->flash('error', 'The following fields are still locked: ' . $locked->keys()->implode(', ') . '.');
            return;
        }

        $changed = collect($this->selectedTags)->filter(function ($tagId, $categoryId) {
            return ($this->originalSelectedTags[$categoryId] ?? null) !== $tagId;
        });

        if ($changed->isEmpty()) {
            $this->dispatch('no-changes-detected', type: 'demographic');
            return;
        }

        $this->updateDemographicAssignments($this->selectedTags);
        $changed->keys()->each(function ($categoryId) {
            $this->demographicLocks[$categoryId] = TestTimeService::now()->toISOString();
        });

        $now = TestTimeService::now();
        $this->user->demographic_tag_cooldowns = $this->demographicLocks;
        $this->user->demographic_tags_updated_at = $now;
        $this->user->save();

        $this->user = $this->user->fresh();
        $this->originalSelectedTags = $this->selectedTags;
        $this->refreshLockStates();

        session()->flash('tags_saved', 'Demographic fields updated. Only the modified fields are locked for 4 months.');
    }

    /**
     * Save institution demographic tags
     */
    public function saveInstitutionDemographicTags()
    {
        $locked = collect($this->selectedInstitutionTags)->filter(function ($tagId, $categoryId) {
            $details = $this->institutionLockInfo[$categoryId] ?? null;
            $locked = $details['locked'] ?? false;
            $original = $this->originalSelectedInstitutionTags[$categoryId] ?? null;
            return $locked && $tagId !== $original;
        });

        if ($locked->isNotEmpty()) {
            session()->flash('error', 'Institution fields still locked: ' . $locked->keys()->implode(', ') . '.');
            return;
        }

        $changed = collect($this->selectedInstitutionTags)->filter(function ($tagId, $categoryId) {
            return ($this->originalSelectedInstitutionTags[$categoryId] ?? null) !== $tagId;
        });

        if ($changed->isEmpty()) {
            $this->dispatch('no-changes-detected', type: 'institution');
            return;
        }

        $this->updateInstitutionDemographicAssignments($this->selectedInstitutionTags);
        $changed->keys()->each(function ($categoryId) {
            $this->institutionLocks[$categoryId] = TestTimeService::now()->toISOString();
        });

        $now = TestTimeService::now();
        $this->user->institution_demographic_tag_cooldowns = $this->institutionLocks;
        $this->user->institution_demographic_tags_updated_at = $now;
        $this->user->save();

        $this->user = $this->user->fresh();
        $this->originalSelectedInstitutionTags = $this->selectedInstitutionTags;
        $this->refreshLockStates();

        session()->flash('tags_saved', 'Institution demographics updated. Modified fields are locked individually for 4 months.');
    }

    protected function updateDemographicAssignments(array $selections): void
    {
        foreach ($this->tagCategories as $category) {
            $categoryTagIds = $category->tags->pluck('id')->toArray();

            if (empty($categoryTagIds)) {
                continue;
            }

            $this->user->tags()->detach($categoryTagIds);

            $chosen = $selections[$category->id] ?? null;
            if ($chosen) {
                $tag = Tag::find($chosen);
                if ($tag) {
                    $this->user->tags()->attach($tag->id, ['tag_name' => $tag->name]);
                }
            }
        }
    }

    protected function updateInstitutionDemographicAssignments(array $selections): void
    {
        if ($this->institutionTagCategories->isEmpty()) {
            return;
        }

        foreach ($this->institutionTagCategories as $category) {
            $categoryTagIds = $category->tags->pluck('id')->toArray();

            if (empty($categoryTagIds)) {
                continue;
            }

            $this->user->institutionTags()->detach($categoryTagIds);

            $chosen = $selections[$category->id] ?? null;
            if ($chosen) {
                $tag = InstitutionTag::find($chosen);
                if ($tag) {
                    $this->user->institutionTags()->attach($tag->id, ['tag_name' => $tag->name]);
                }
            }
        }
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
            'timeUntilUpdateText' => $this->timeUntilUpdateText,
            'canUpdateInstitutionDemographics' => $this->canUpdateInstitutionDemographics,
            'timeUntilInstitutionUpdateText' => $this->timeUntilInstitutionUpdateText,
            'tagCategories' => $this->tagCategories,
            'institutionTagCategories' => $this->institutionTagCategories,
            'demographicLockInfo' => $this->demographicLockInfo,
            'institutionLockInfo' => $this->institutionLockInfo,
        ]);
    }
}