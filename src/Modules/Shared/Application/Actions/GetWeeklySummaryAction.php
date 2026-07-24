<?php

namespace Modules\Shared\Application\Actions;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GetWeeklySummaryAction
{
    public function execute(int $userId): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $tasksCompleted = DB::table('tasks')
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
            ->count();

        $tasksCreated = DB::table('tasks')
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->count();

        $pomodorosCompleted = DB::table('pomodoros')
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
            ->count();

        $focusMinutes = DB::table('pomodoros')
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
            ->sum('duration');

        $habitsCompletedToday = DB::table('habits')
            ->where('user_id', $userId)
            ->whereNotNull('last_completed_at')
            ->whereDate('last_completed_at', Carbon::today())
            ->count();

        $habitsTotal = DB::table('habits')
            ->where('user_id', $userId)
            ->count();

        $notesCreated = DB::table('notes')
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->count();

        $maxStreak = DB::table('habits')
            ->where('user_id', $userId)
            ->max('current_streak') ?? 0;

        return [
            'tasks_completed' => $tasksCompleted,
            'tasks_created' => $tasksCreated,
            'pomodoros_completed' => $pomodorosCompleted,
            'focus_minutes' => (int) $focusMinutes,
            'habits_today' => $habitsCompletedToday,
            'habits_total' => $habitsTotal,
            'notes_created' => $notesCreated,
            'max_streak' => (int) $maxStreak,
        ];
    }
}
