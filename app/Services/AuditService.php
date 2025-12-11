<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an audit event
     */
    public static function log(
        string $action,
        string $resource,
        ?int $resourceId = null,
        ?array $before = null,
        ?array $after = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'resource' => $resource,
            'resource_id' => $resourceId,
            'before' => $before,
            'after' => $after,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log model creation
     */
    public static function logCreate(string $resource, int $resourceId, array $data): AuditLog
    {
        return self::log('create', $resource, $resourceId, null, $data);
    }

    /**
     * Log model update
     */
    public static function logUpdate(string $resource, int $resourceId, array $before, array $after): AuditLog
    {
        return self::log('update', $resource, $resourceId, $before, $after);
    }

    /**
     * Log model deletion
     */
    public static function logDelete(string $resource, int $resourceId, array $data): AuditLog
    {
        return self::log('delete', $resource, $resourceId, $data, null);
    }
}
