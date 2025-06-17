<?php

namespace App\Policies;

use App\Models\ProductLog;
use App\Models\User;

class ProductLogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->checkPermissionTo('view-any ProductLog');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProductLog $productlog): bool
    {
        return $user->checkPermissionTo('view ProductLog');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create ProductLog');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProductLog $productlog): bool
    {
        return $user->checkPermissionTo('update ProductLog');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProductLog $productlog): bool
    {
        return $user->checkPermissionTo('delete ProductLog');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any ProductLog');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProductLog $productlog): bool
    {
        return $user->checkPermissionTo('restore ProductLog');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any ProductLog');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, ProductLog $productlog): bool
    {
        return $user->checkPermissionTo('replicate ProductLog');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder ProductLog');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProductLog $productlog): bool
    {
        return $user->checkPermissionTo('force-delete ProductLog');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any ProductLog');
    }
}
