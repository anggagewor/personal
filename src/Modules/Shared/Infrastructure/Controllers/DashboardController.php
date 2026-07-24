<?php

namespace Modules\Shared\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function weeklySummary(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Tasks completed this week
        $tasksCompleted = DB::table('tasks')
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
            ->count();

        // Tasks created this week
        $tasksCreated = DB::table('tasks')
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->count();

        // Pomodoro sessions completed this week
        $pomodorosCompleted = DB::table('pomodoros')
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
            ->count();

        // Total focus minutes this week
        $focusMinutes = DB::table('pomodoros')
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
            ->sum('duration');

        // Habits completed today
        $habitsCompletedToday = DB::table('habits')
            ->where('user_id', $userId)
            ->whereNotNull('last_completed_at')
            ->whereDate('last_completed_at', Carbon::today())
            ->count();

        // Total habits
        $habitsTotal = DB::table('habits')
            ->where('user_id', $userId)
            ->count();

        // Notes created this week
        $notesCreated = DB::table('notes')
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->count();

        // Current max streak across habits
        $maxStreak = DB::table('habits')
            ->where('user_id', $userId)
            ->max('current_streak') ?? 0;

        return response()->json([
            'data' => [
                'tasks_completed' => $tasksCompleted,
                'tasks_created' => $tasksCreated,
                'pomodoros_completed' => $pomodorosCompleted,
                'focus_minutes' => (int) $focusMinutes,
                'habits_today' => $habitsCompletedToday,
                'habits_total' => $habitsTotal,
                'notes_created' => $notesCreated,
                'max_streak' => (int) $maxStreak,
            ],
        ]);
    }
}
