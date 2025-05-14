<?php

namespace App\Services;

class UserExperienceService
{
    /**
     * Calculate user's level based on experience points.
     * Formula: Level = 1 + floor(sqrt(XP / 100))
     * This creates a gradually increasing XP requirement for each level.
     * 
     * @param float $experiencePoints
     * @return int
     */
    public static function calculateLevel(float $experiencePoints): int
    {
        return 1 + (int)floor(sqrt($experiencePoints / 100));
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
        return ($level - 1) * ($level - 1) * 100;
    }
    
    /**
     * Get rank for a specific level based on level ranges.
     * 
     * @param int $level
     * @return string
     */
    public static function getTitleForLevel(int $level): string
    {
        // Define ranks based on level ranges with max level of 30
        if ($level == 30) {
            return 'Research Deity';
        } elseif ($level >= 27) {
            return 'Legend';
        } elseif ($level >= 24) {
            return 'Ruby';
        } elseif ($level >= 20) {
            return 'Diamond';
        } elseif ($level >= 15) {
            return 'Platinum';
        } elseif ($level >= 10) {
            return 'Gold';
        } elseif ($level >= 5) {
            return 'Silver';
        } else {
            return 'Bronze';
        }
    }
    
    /**
     * Apply perks based on level reached.
     * 
     * @param \App\Models\User $user
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
                    
                case 10:
                    // Level 10 perk: 500 bonus points
                    $user->points += 500;
                    $perksApplied[] = '500 bonus points';
                    break;
                    
                // Add more perks as needed
            }
        }
        
        // Always update the user's title to match their new level
        $user->title = self::getTitleForLevel($currentLevel);
        
        // Save the user
        $user->save();
        
        return $perksApplied;
    }
    
    /**
     * Method to refresh a user's title based on their current XP/level
     * 
     * @param \App\Models\User $user
     * @return string The new title
     */
    public static function refreshUserTitle($user): string
    {
        $level = self::calculateLevel($user->experience_points);
        $title = self::getTitleForLevel($level);
        
        $user->title = $title;
        $user->save();
        
        return $title;
    }
}
