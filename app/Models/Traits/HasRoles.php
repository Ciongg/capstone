<?php

namespace App\Models\Traits;

trait HasRoles
{
    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->type === $role;
    }

    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->type, $roles);
    }

    /**
     * Check if user is a respondent
     */
    public function isRespondent(): bool
    {
        return $this->hasRole('respondent');
    }

    /**
     * Check if user is a researcher
     */
    public function isResearcher(): bool
    {
        return $this->hasRole('researcher');
    }

    /**
     * Check if user is an institution admin
     */
    public function isInstitutionAdmin(): bool
    {
        return $this->hasRole('institution_admin');
    }

    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Get all available roles
     */
    public static function getAvailableRoles(): array
    {
        return ['respondent', 'researcher', 'institution_admin', 'super_admin'];
    }
} 