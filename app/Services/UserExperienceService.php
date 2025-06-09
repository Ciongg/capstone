<?php

namespace App\Services;

use App\Models\User;

class UserExperienceService
{
    // Constants for ranks
    public const RANK_SILVER = 'silver';
    public const RANK_GOLD = 'gold';
    public const RANK_DIAMOND = 'diamond';
    
    // Level thresholds for ranks
    public const GOLD_LEVEL_THRESHOLD = 10; // Around 30 surveys (300 points)
    public const DIAMOND_LEVEL_THRESHOLD = 20; // Around 100 surveys (1000 points)
    
    // XP multiplier for balanced progression
    private const XP_MULTIPLIER = 5.5;
    
    /**
     * Calculate user's level based on experience points.
     * Balanced formula: Level = 1 + floor(sqrt(XP / 5.5))
     * 
     * @param float $experiencePoints
     * @return int
     */
    public static function calculateLevel(float $experiencePoints): int
    {
        // Use the formula to directly calculate an approximate level
        $calculatedLevel = 1 + (int)floor(sqrt($experiencePoints / self::XP_MULTIPLIER));
        
        // Check if we're exactly at the boundary of the next level
        $nextLevel = $calculatedLevel + 1;
        $xpForNextLevel = self::xpRequiredForLevel($nextLevel);
        
        // If XP exactly matches the next level's requirement, move up one level
        if ($experiencePoints == $xpForNextLevel) {
            return $nextLevel;
        }
        
        return $calculatedLevel;
    }

    
    
    /**
     * Calculate XP required for next level.
     * 
     * @param int $level
     * @return int
     */
    public static function xpRequiredForLevel(int $level): int
    {
        // Calculate XP needed for specified level
        return (int)(($level - 1) * ($level - 1) * self::XP_MULTIPLIER);
    }
    
    /**
     * Get rank for a specific level based on level thresholds.
     * 
     * @param int $level
     * @return string
     */
    public static function getRankForLevel(int $level): string
    {
        if ($level >= self::DIAMOND_LEVEL_THRESHOLD) {
            return self::RANK_DIAMOND;
        } elseif ($level >= self::GOLD_LEVEL_THRESHOLD) {
            return self::RANK_GOLD;
        } else {
            return self::RANK_SILVER;
        }
    }
    
    /**
     * Get current level for a user based on their XP.
     *
     * @param User $user
     * @return int
     */
    public static function getUserLevel(User $user): int
    {
        return self::calculateLevel($user->experience_points);
    }
    
    /**
     * Get progress to next level (percentage) for a user.
     *
     * @param User $user
     * @return float
     */
    public static function getUserLevelProgressPercentage(User $user): float
    {
        $currentLevel = self::getUserLevel($user);
        $currentLevelXp = self::xpRequiredForLevel($currentLevel);
        $nextLevelXp = self::xpRequiredForLevel($currentLevel + 1);
        
        $xpForThisLevel = $user->experience_points - $currentLevelXp;
        $xpRequiredForNextLevel = $nextLevelXp - $currentLevelXp;
        
        return min(100, round(($xpForThisLevel / $xpRequiredForNextLevel) * 100, 1));
    }
    
    /**
     * Get XP required for user's next level.
     *
     * @param User $user
     * @return int
     */
    public static function getXpRequiredForUserNextLevel(User $user): int
    {
        $currentLevel = self::getUserLevel($user);
        return self::xpRequiredForLevel($currentLevel + 1);
    }
    
    /**
     * Add experience points to user and handle level-up logic
     * 
     * @param User $user
     * @param int $xp
     * @return array
     */
    public static function addUserExperiencePoints(User $user, $xp)
    {
        $previousLevel = self::getUserLevel($user);
        $user->experience_points += $xp;
        
        $currentLevel = self::getUserLevel($user);
        $leveled_up = $currentLevel > $previousLevel;
        
        // Check if user rank should change
        $previousRank = $user->rank;
        $newRank = self::getRankForLevel($currentLevel);
        $rankChanged = $previousRank !== $newRank;
        
        // Update account_level and rank in database
        $user->account_level = $currentLevel;
        $user->rank = $newRank;
        
        // Apply any level perks if leveled up
        if ($leveled_up) {
            $perks = self::applyLevelPerks($user, $previousLevel, $currentLevel);
        } else {
            $perks = [];
        }
        
        $user->save();
        
        return [
            'previous_level' => $previousLevel,
            'current_level' => $currentLevel,
            'leveled_up' => $leveled_up,
            'perks' => $perks,
            'rank_changed' => $rankChanged,
            'new_rank' => $newRank
        ];
    }
    
    /**
     * Apply perks based on level reached.
     * 
     * @param User $user
     * @param int $previousLevel
     * @param int $currentLevel
     * @return array Perks applied
     */
    public static function applyLevelPerks($user, int $previousLevel, int $currentLevel): array
    {
        $perksApplied = [];
        
        // Apply perks for each level gained
        for ($level = $previousLevel + 1; $level <= $currentLevel; $level++) {
            switch ($level) {
                case 2:
                    // Level 2 perk: 50 bonus points
                    $user->points += 50;
                    $perksApplied[] = '50 bonus points';
                    break;
                    
                case 5:
                    // Level 5 perk: 150 bonus points
                    $user->points += 150;
                    $perksApplied[] = '150 bonus points';
                    break;
                    
                case 10: // Gold rank threshold
                    // Level 10 perk: 500 bonus points
                    $user->points += 500;
                    $perksApplied[] = '500 bonus points';
                    $perksApplied[] = 'Gold rank achieved!';
                    break;
                    
                case 20: // Diamond rank threshold
                    // Level 20 perk: 1000 bonus points
                    $user->points += 1000;
                    $perksApplied[] = '1000 bonus points';
                    $perksApplied[] = 'Diamond rank achieved!';
                    break;
                    
                // Add more level-specific perks as needed
            }
        }
        
        return $perksApplied;
    }
    
   
}
