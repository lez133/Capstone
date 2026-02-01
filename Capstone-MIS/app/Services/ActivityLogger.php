<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\MSWDMember;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log($userId = null, string $action, $subjectType = null, $subjectId = null, $meta = null)
    {
        try {
            $metaArr = $meta ? (array)$meta : [];
            // Resolve user id / name from provided id or current auth
            if (empty($userId)) {
                try {
                    $authUser = Auth::guard('mswd')->user() ?? Auth::user();
                } catch (\Throwable $e) {
                    $authUser = Auth::user();
                }
                if ($authUser) {
                    $userId = $authUser->id ?? $userId;
                    if (empty($metaArr['user_name'])) {
                        $metaArr['user_name'] = trim(($authUser->fname ?? '') . ' ' . ($authUser->lname ?? '')) ?: ($authUser->name ?? null);
                    }
                }
            } else {
                // If userId provided but no user_name, try to resolve from MSWDMember
                if (empty($metaArr['user_name'])) {
                    $user = MSWDMember::find($userId);
                    if ($user) {
                        $metaArr['user_name'] = trim(($user->fname ?? '') . ' ' . ($user->lname ?? '')) ?: ($user->name ?? null);
                    }
                }
            }
            $entry = ActivityLog::create([
                'user_id' => $userId,
                'action' => $action,
                'subject_type' => $subjectType ? (string) $subjectType : null,
                'subject_id' => $subjectId,
                'meta' => $metaArr ?: null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::header('User-Agent'),
            ]);

            return $entry;
        } catch (\Throwable $e) {
            // write error to laravel log so you can inspect why DB write failed
            Log::error('ActivityLogger::log failed', [
                'error' => $e->getMessage(),
                'action' => $action,
                'subject' => $subjectType,
                'subject_id' => $subjectId,
                'user_id' => $userId,
            ]);
            return false;
        }
    }
}
