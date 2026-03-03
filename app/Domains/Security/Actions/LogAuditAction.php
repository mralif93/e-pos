<?php

namespace App\Domains\Security\Actions;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class LogAuditAction
{
    /**
     * Records an audit log entry.
     *
     * @param string $action       The key of the action (e.g., 'VOID_SALE', 'PIN_VERIFY_FAILED')
     * @param string|null $description   A human-readable description of the action.
     * @param array $metadata      Extra details for auditing (e.g., old/new values, request data)
     * @param int|null $userId     Override the user_id (defaults to current auth user)
     * @param int|null $outletId   Override the outlet_id (defaults to current user's outlet)
     * @return AuditLog
     */
    public function execute(
        string $action,
        ?string $description = null,
        array $metadata = [],
        ?int $userId = null,
        ?int $outletId = null
    ): AuditLog {
        $user = auth()->user();
        
        return AuditLog::create([
            'user_id' => $userId ?? ($user ? $user->id : null),
            'outlet_id' => $outletId ?? ($user ? $user->outlet_id : null),
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
        ]);
    }
}
