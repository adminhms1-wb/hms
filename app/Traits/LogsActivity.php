<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Log an activity
     */
    protected function logActivity($action, $model = null, $modelId = null, $description = null, $module = null)
    {
        $userId = Auth::id() ?? $this->getUserIdFromSession();
        
        if (!$userId) {
            return; // Skip logging if no user
        }
        
        ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'module' => $module ?? $this->getModuleName(),
        ]);
    }

    /**
     * Get user ID from session if auth is not available
     */
    protected function getUserIdFromSession()
    {
        return request()->session()->get('user_id');
    }

    /**
     * Get module name from controller
     */
    protected function getModuleName()
    {
        $className = class_basename($this);
        return str_replace('Controller', '', $className);
    }
}
