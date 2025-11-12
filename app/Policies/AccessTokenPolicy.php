<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AccessToken;
use App\Models\User;

class AccessTokenPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->checkPermissionTo('view-any AccessToken');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AccessToken $accesstoken): bool
    {
        return $user->checkPermissionTo('view AccessToken');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create AccessToken');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AccessToken $accesstoken): bool
    {
        return $user->checkPermissionTo('update AccessToken');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AccessToken $accesstoken): bool
    {
        return $user->checkPermissionTo('delete AccessToken');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any AccessToken');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AccessToken $accesstoken): bool
    {
        return $user->checkPermissionTo('restore AccessToken');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any AccessToken');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, AccessToken $accesstoken): bool
    {
        return $user->checkPermissionTo('replicate AccessToken');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder AccessToken');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AccessToken $accesstoken): bool
    {
        return $user->checkPermissionTo('force-delete AccessToken');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any AccessToken');
    }
}
