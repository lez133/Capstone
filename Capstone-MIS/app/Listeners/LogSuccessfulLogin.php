<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use App\Models\ActivityLog;

class LogSuccessfulLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        $userId = $user->id ?? null;
        $name = trim(($user->fname ?? '') . ' ' . ($user->lname ?? '')) ?: ($user->name ?? null);

        $recentExists = ActivityLog::where('user_id', $userId)
            ->where('action', 'login')
            ->where('created_at', '>=', Carbon::now()->subSeconds(5))
            ->exists();

        if ($recentExists) {
            return;
        }

        // record login action; subject_type set to user model class for context
        ActivityLogger::log(
            $userId,
            'login',
            is_object($user) ? get_class($user) : null,
            $userId,
            ['user_name' => $name, 'note' => 'Successful login']
        );
    }
}
