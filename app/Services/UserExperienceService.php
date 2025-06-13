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
    public const GOLD_LEVEL_THRESHOLD = 11;  // Level 11-20 is Gold
    public const DIAMOND_LEVEL_THRESHOLD = 21; // Level 21-30 is Diamond
    public const MAX_LEVEL = 30; // Maximum level
    
    // XP multiplier for balanced progression
    private const XP_MULTIPLIER = 100; // Simplified: each level requires level * 100 XP
    
    /**
     * Calculate user's level based on experience points.
     * Simplified calculation: Each level requires level*100 XP
     * 
     * @param float $experiencePoints
     * @return int
     */
    public static function calculateLevel($experiencePoints): int
    {
        $level = 1;
        $requiredXp = 100; // XP required for level 2
        
        while ($experiencePoints >= $requiredXp && $level < self::MAX_LEVEL) {
            $level++;
            $requiredXp += $level * 100; // Increasing XP requirement per level
        }
        
        return min($level, self::MAX_LEVEL); // Cap at max level
    }
    
    /**
     * Calculate XP required for a specific level.
     * 
     * @param int $level
     * @return int
     */
    public static function xpRequiredForLevel(int $level): int
    {
        if ($level <= 1) {
            return 0;
        }
        
        $xp = 0;
        // Level 1 starts at 0 XP
        for ($i = 1; $i < $level; $i++) {
            $xp += $i * 100;
        }
        
        return $xp;
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
     * Check if user is at max level
     * 
     * @param User $user
     * @return bool
     */
    public static function isUserAtMaxLevel(User $user): bool
    {
        return self::getUserLevel($user) >= self::MAX_LEVEL;
    }
    
    /**
     * Get current level for a user based on their XP.
     *
     * @param User $user
     * @return int
     */
    public static function getUserLevel(User $user): int
    {
        // If user has account_level field set and it's valid, use that
        if (isset($user->account_level) && $user->account_level > 0 && $user->account_level <= self::MAX_LEVEL) {
            return $user->account_level;
        }
        
        // Otherwise calculate from experience points
        return self::calculateLevel($user->experience_points ?? 0);
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
        
        // If at max level, return 100%
        if ($currentLevel >= self::MAX_LEVEL) {
            return 100;
        }
        
        $currentXp = $user->experience_points ?? 0;
        
        // Calculate XP thresholds for current and next level
        $currentLevelXp = self::xpRequiredForLevel($currentLevel);
        $nextLevelXp = self::xpRequiredForLevel($currentLevel + 1);
        
        // Calculate XP needed for next level
        $xpForNextLevel = $nextLevelXp - $currentLevelXp;
        $xpIntoCurrentLevel = $currentXp - $currentLevelXp;
        
        // Calculate percentage (avoid division by zero)
        if ($xpForNextLevel <= 0) {
            return 100;
        }
        
        return min(100, max(0, ($xpIntoCurrentLevel / $xpForNextLevel) * 100));
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
        
        // If at max level, return current level XP requirement
        if ($currentLevel >= self::MAX_LEVEL) {
            return self::xpRequiredForLevel($currentLevel);
        }
        
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
        $result = [
            'leveled_up' => false,
            'old_level' => self::getUserLevel($user),
            'new_level' => self::getUserLevel($user),
            'old_rank' => $user->rank ?? 'silver',
            'new_rank' => $user->rank ?? 'silver',
        ];

        // Start transaction to ensure atomic operation
        \DB::beginTransaction();
        try {
            // Get current XP
            $currentXp = $user->experience_points ?? 0;
            
            // Add XP
            $user->experience_points = $currentXp + $xp;
            
            // Calculate new level
            $newLevel = self::calculateLevel($user->experience_points);
            
            // Check if user leveled up
            if ($newLevel > $result['old_level']) {
                $result['leveled_up'] = true;
                $result['new_level'] = $newLevel;
                
                // Update user level in database
                $user->account_level = $newLevel;
                
                // Update rank based on new level thresholds
                $newRank = self::getRankForLevel($newLevel);
                if ($newRank !== $user->rank) {
                    $user->rank = $newRank;
                    $result['new_rank'] = $newRank;
                } else {
                    $result['new_rank'] = $user->rank;
                }
            } else {
                $result['new_rank'] = $user->rank;
            }
            
            // Save changes
            $user->save();
            \DB::commit();
            
            return $result;
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to add experience points: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // /**
    //  * Apply perks based on level reached.
    //  * 
    //  * @param User $user
    //  * @param int $previousLevel
    //  * @param int $currentLevel
    //  * @return array Perks applied
    //  */
    // public static function applyLevelPerks($user, int $previousLevel, int $currentLevel): array
    // {
    //     $perksApplied = [];
        
    //     // Apply perks for each level gained
    //     for ($level = $previousLevel + 1; $level <= $currentLevel; $level++) {
    //         switch ($level) {
    //             case 2:
    //                 // Level 2 perk: 50 bonus points
    //                 $user->points += 50;
    //                 $perksApplied[] = '50 bonus points';
    //                 break;
                    
    //             case 5:
    //                 // Level 5 perk: 150 bonus points
    //                 $user->points += 150;
    //                 $perksApplied[] = '150 bonus points';
    //                 break;
                    
    //             case 11: // Gold rank threshold
    //                 // Level 11 perk: 500 bonus points
    //                 $user->points += 500;
    //                 $perksApplied[] = '500 bonus points';
    //                 $perksApplied[] = 'Gold rank achieved!';
    //                 break;
                    
    //             case 21: // Diamond rank threshold
    //                 // Level 21 perk: 1000 bonus points
    //                 $user->points += 1000;
    //                 $perksApplied[] = '1000 bonus points';
    //                 $perksApplied[] = 'Diamond rank achieved!';
    //                 break;
                    
    //             case 30: // Max level
    //                 // Level 30 perk: 2000 bonus points
    //                 $user->points += 2000;
    //                 $perksApplied[] = '2000 bonus points';
    //                 $perksApplied[] = 'Maximum level achieved!';
    //                 break;
                    
    //             // Add more level-specific perks as needed
    //         }
    //     }
        
    //     return $perksApplied;
    // }
}
