<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Log;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LogPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Log');
    }

    public function view(AuthUser $authUser, Log $log): bool
    {
        return $authUser->can('View:Log');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Log');
    }

    public function update(AuthUser $authUser, Log $log): bool
    {
        return $authUser->can('Update:Log');
    }

    public function delete(AuthUser $authUser, Log $log): bool
    {
        return $authUser->can('Delete:Log');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Log');
    }

    public function restore(AuthUser $authUser, Log $log): bool
    {
        return $authUser->can('Restore:Log');
    }

    public function forceDelete(AuthUser $authUser, Log $log): bool
    {
        return $authUser->can('ForceDelete:Log');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Log');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Log');
    }

    public function replicate(AuthUser $authUser, Log $log): bool
    {
        return $authUser->can('Replicate:Log');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Log');
    }
}
