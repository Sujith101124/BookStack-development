<?php

namespace BookStack\Activity\Services;

use BookStack\Activity\Models\ReadingProgress;
use BookStack\Entities\Models\Page;
use BookStack\Users\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ReadingProgressService
{
    public function getForUserAndPage(User $user, Page $page): ?ReadingProgress
    {
        return ReadingProgress::query()
            ->where('user_id', '=', $user->id)
            ->where('page_id', '=', $page->id)
            ->first();
    }

    public function updateOrCreate(User $user, Page $page, int $progress, int $scrollPosition): ReadingProgress
    {
        $record = $this->getForUserAndPage($user, $page) ?: new ReadingProgress([
            'user_id' => $user->id,
            'page_id' => $page->id,
        ]);

        $now = Carbon::now();
        $completed = $progress >= 100;
        $completedAt = $completed ? ($record->completed_at ?? $now) : $record->completed_at;

        $record->fill([
            'progress_percentage' => max(0, min(100, $progress)),
            'scroll_position' => max(0, $scrollPosition),
            'completed' => $completed,
            'completed_at' => $completedAt,
            'visit_count' => ($record->visit_count ?? 0) + 1,
            'first_opened_at' => $record->first_opened_at ?? $now,
            'last_opened_at' => $now,
        ]);

        $record->save();

        return $record;
    }

    public function continueReadingForUser(User $user, int $limit = 10): Collection
    {
        return ReadingProgress::query()
            ->with(['page.book', 'page.chapter'])
            ->where('user_id', '=', $user->id)
            ->where('completed', '=', false)
            ->orderByDesc('last_opened_at')
            ->take($limit)
            ->get();
    }
}
