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
     * Creates a shorter 10-character alphanumeric UUID without dashes
     */
    protected function generateUuidIfNotSet()
    {
        if (empty($this->uuid)) {
            // Generate a random 10-character alphanumeric string
            $this->uuid = substr(str_shuffle(
                str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 3)
            ), 0, 10);
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
