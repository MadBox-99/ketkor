<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Partial;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PartialPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Partial');
    }

    public function view(AuthUser $authUser, Partial $partial): bool
    {
        return $authUser->can('View:Partial');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Partial');
    }

    public function update(AuthUser $authUser, Partial $partial): bool
    {
        return $authUser->can('Update:Partial');
    }

    public function delete(AuthUser $authUser, Partial $partial): bool
    {
        return $authUser->can('Delete:Partial');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Partial');
    }

    public function restore(AuthUser $authUser, Partial $partial): bool
    {
        return $authUser->can('Restore:Partial');
    }

    public function forceDelete(AuthUser $authUser, Partial $partial): bool
    {
        return $authUser->can('ForceDelete:Partial');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Partial');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Partial');
    }

    public function replicate(AuthUser $authUser, Partial $partial): bool
    {
        return $authUser->can('Replicate:Partial');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Partial');
    }
}
