<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use App\Models\ActivityLog;

class LogSuccessfulLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        $user = $event->user;
        if (! $user) {
            return;
        }

        $userId = $user->id ?? null;
        $name = trim(($user->fname ?? '') . ' ' . ($user->lname ?? '')) ?: ($user->name ?? null);

        // avoid duplicate quick-fire logs
        $recentExists = ActivityLog::where('user_id', $userId)
            ->where('action', 'logout')
            ->where('created_at', '>=', Carbon::now()->subSeconds(5))
            ->exists();

        if ($recentExists) {
            return;
        }

        ActivityLogger::log(
            $userId,
            'logout',
            is_object($user) ? get_class($user) : null,
            $userId,
            ['user_name' => $name, 'note' => 'Logout']
        );
    }
}
