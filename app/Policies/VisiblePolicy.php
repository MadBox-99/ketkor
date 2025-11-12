<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Visible;

class VisiblePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->checkPermissionTo('view-any Visible');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Visible $visible): bool
    {
        return $user->checkPermissionTo('view Visible');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Visible');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Visible $visible): bool
    {
        return $user->checkPermissionTo('update Visible');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Visible $visible): bool
    {
        return $user->checkPermissionTo('delete Visible');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Visible');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Visible $visible): bool
    {
        return $user->checkPermissionTo('restore Visible');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Visible');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Visible $visible): bool
    {
        return $user->checkPermissionTo('replicate Visible');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Visible');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Visible $visible): bool
    {
        return $user->checkPermissionTo('force-delete Visible');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Visible');
    }
}
