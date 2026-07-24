<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ProductLog;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ProductLogPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProductLog');
    }

    public function view(AuthUser $authUser, ProductLog $productLog): bool
    {
        return $authUser->can('View:ProductLog');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProductLog');
    }

    public function update(AuthUser $authUser, ProductLog $productLog): bool
    {
        return $authUser->can('Update:ProductLog');
    }

    public function delete(AuthUser $authUser, ProductLog $productLog): bool
    {
        return $authUser->can('Delete:ProductLog');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ProductLog');
    }

    public function restore(AuthUser $authUser, ProductLog $productLog): bool
    {
        return $authUser->can('Restore:ProductLog');
    }

    public function forceDelete(AuthUser $authUser, ProductLog $productLog): bool
    {
        return $authUser->can('ForceDelete:ProductLog');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProductLog');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProductLog');
    }

    public function replicate(AuthUser $authUser, ProductLog $productLog): bool
    {
        return $authUser->can('Replicate:ProductLog');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProductLog');
    }
}
