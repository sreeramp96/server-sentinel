<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Website;
use Illuminate\Auth\Access\Response;

class WebsitePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Website $website): bool
    {
        return $user->id === $website->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Website $website): bool
    {
        return $user->id === $website->user_id;
    }

    public function delete(User $user, Website $website): bool
    {
        return $user->id === $website->user_id;
    }
}
