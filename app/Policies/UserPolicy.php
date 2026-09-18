<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the given user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    /**
     * Determine if the given user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Users can always view their own profile
        if ($user->id === $model->id) {
            return true;
        }

        return $user->can('users.view');
    }

    /**
     * Determine if the given user can create users.
     */
    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    /**
     * Determine if the given user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Users can always update their own profile
        if ($user->id === $model->id) {
            return true;
        }

        return $user->can('users.update');
    }

    /**
     * Determine if the given user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('users.delete');
    }
}
