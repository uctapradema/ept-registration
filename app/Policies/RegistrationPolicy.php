<?php

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;

class RegistrationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isFinance() || $user->isMahasiswa();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Registration $registration): bool
    {
        if ($user->isAdmin() || $user->isFinance()) {
            return true;
        }

        return $user->isMahasiswa() && $user->id === $registration->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Registration $registration): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isFinance()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Registration $registration): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Registration $registration): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Registration $registration): bool
    {
        return $user->isAdmin();
    }
}
