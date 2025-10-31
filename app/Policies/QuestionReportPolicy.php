<?php

namespace App\Policies;

use App\Models\QuestionReport;
use App\Models\User;

class QuestionReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isEditor();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, QuestionReport $questionReport): bool
    {
        return $user->isEditor();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, QuestionReport $questionReport): bool
    {
        return $user->isEditor();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, QuestionReport $questionReport): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, QuestionReport $questionReport): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, QuestionReport $questionReport): bool
    {
        return $user->isAdmin();
    }
}
