<?php

namespace BookStack\Activity\Policies;

use BookStack\Activity\Models\ReadingProgress;
use BookStack\Users\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReadingProgressPolicy
{
    use HandlesAuthorization;

    public function view(User $user, ReadingProgress $readingProgress): bool
    {
        return $user->id === $readingProgress->user_id;
    }

    public function update(User $user, ReadingProgress $readingProgress): bool
    {
        return $user->id === $readingProgress->user_id;
    }
}
