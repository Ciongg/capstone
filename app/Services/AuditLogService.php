<?php

namespace App\Services;

use App\Models\AuditLogs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    public static function log(
        string $eventType,
        ?string $message = null,
        ?string $resourceType = null,
        ?int $resourceId = null,
        ?array $before = null,
        ?array $after = null,
        ?array $changedFields = null,
        ?array $meta = null
    ): void {
        $user = Auth::user();
        
        AuditLogs::create([
            'event_type' => $eventType,
            'message' => $message,
            'performed_by' => $user?->id,
            'email' => $user?->email,
            'performed_role' => $user?->type, // Changed from ->role to ->type
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'before' => $before,
            'after' => $after,
            'changed_fields' => $changedFields,
            'meta' => array_merge([
                'ip' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ], $meta ?? []),
            'created_at' => now(),
        ]);
    }

    public static function logCreate(string $resourceType, int $resourceId, array $data, ?string $message = null): void
    {
        self::log(
            eventType: 'create',
            message: $message ?? "Created new {$resourceType}",
            resourceType: $resourceType,
            resourceId: $resourceId,
            after: $data
        );
    }

    public static function logUpdate(string $resourceType, int $resourceId, array $before, array $after, ?string $message = null): void
    {
        $changed = array_keys(array_diff_assoc($after, $before));
        
        self::log(
            eventType: 'update',
            message: $message ?? "Updated {$resourceType}",
            resourceType: $resourceType,
            resourceId: $resourceId,
            before: $before,
            after: $after,
            changedFields: $changed
        );
    }

    public static function logDelete(string $resourceType, int $resourceId, array $data, ?string $message = null): void
    {
        self::log(
            eventType: 'delete',
            message: $message ?? "Deleted {$resourceType}",
            resourceType: $resourceType,
            resourceId: $resourceId,
            before: $data
        );
    }

    public static function logArchive(string $resourceType, int $resourceId, ?string $message = null): void
    {
        self::log(
            eventType: 'archive',
            message: $message ?? "Archived {$resourceType}",
            resourceType: $resourceType,
            resourceId: $resourceId
        );
    }

    public static function logRestore(string $resourceType, int $resourceId, ?string $message = null): void
    {
        self::log(
            eventType: 'restore',
            message: $message ?? "Restored {$resourceType}",
            resourceType: $resourceType,
            resourceId: $resourceId
        );
    }

    public static function logExport(string $resourceType, ?array $meta = null, ?string $message = null): void
    {
        self::log(
            eventType: 'export',
            message: $message ?? "Exported {$resourceType} data",
            resourceType: $resourceType,
            meta: $meta
        );
    }

    public static function logLock(string $resourceType, int $resourceId, ?string $message = null): void
    {
        self::log(
            eventType: 'lock',
            message: $message ?? "Locked {$resourceType}",
            resourceType: $resourceType,
            resourceId: $resourceId
        );
    }

    public static function logUnlock(string $resourceType, int $resourceId, ?string $message = null): void
    {
        self::log(
            eventType: 'unlock',
            message: $message ?? "Unlocked {$resourceType}",
            resourceType: $resourceType,
            resourceId: $resourceId
        );
    }
}
