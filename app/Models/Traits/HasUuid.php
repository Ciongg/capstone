<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Boot the trait and add the UUID generation functionality.
     */
    protected static function bootHasUuid()
    {
        // Generate UUID before creating a model
        static::creating(function ($model) {
            $model->generateUuidIfNotSet();
        });
        
        // Ensure UUID exists before saving (safety check)
        static::saving(function ($model) {
            $model->generateUuidIfNotSet();
        });
    }

    /**
     * Generate a UUID if it's not already set
     * Uses standard UUID format for PostgreSQL compatibility
     */
    protected function generateUuidIfNotSet()
    {
        if (empty($this->uuid)) {
            $this->uuid = (string) Str::uuid();
        }
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
