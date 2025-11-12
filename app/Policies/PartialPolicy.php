<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Partial;
use App\Models\User;

class PartialPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->checkPermissionTo('view-any Partial');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Partial $partial): bool
    {
        return $user->checkPermissionTo('view Partial');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Partial');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Partial $partial): bool
    {
        return $user->checkPermissionTo('update Partial');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Partial $partial): bool
    {
        return $user->checkPermissionTo('delete Partial');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Partial');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Partial $partial): bool
    {
        return $user->checkPermissionTo('restore Partial');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Partial');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Partial $partial): bool
    {
        return $user->checkPermissionTo('replicate Partial');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Partial');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Partial $partial): bool
    {
        return $user->checkPermissionTo('force-delete Partial');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Partial');
    }
}
