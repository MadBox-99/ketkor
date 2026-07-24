<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MaintenanceReminder;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MaintenanceReminderPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MaintenanceReminder');
    }

    public function view(AuthUser $authUser, MaintenanceReminder $maintenanceReminder): bool
    {
        return $authUser->can('View:MaintenanceReminder');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MaintenanceReminder');
    }

    public function update(AuthUser $authUser, MaintenanceReminder $maintenanceReminder): bool
    {
        return $authUser->can('Update:MaintenanceReminder');
    }

    public function delete(AuthUser $authUser, MaintenanceReminder $maintenanceReminder): bool
    {
        return $authUser->can('Delete:MaintenanceReminder');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MaintenanceReminder');
    }

    public function restore(AuthUser $authUser, MaintenanceReminder $maintenanceReminder): bool
    {
        return $authUser->can('Restore:MaintenanceReminder');
    }

    public function forceDelete(AuthUser $authUser, MaintenanceReminder $maintenanceReminder): bool
    {
        return $authUser->can('ForceDelete:MaintenanceReminder');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MaintenanceReminder');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MaintenanceReminder');
    }

    public function replicate(AuthUser $authUser, MaintenanceReminder $maintenanceReminder): bool
    {
        return $authUser->can('Replicate:MaintenanceReminder');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MaintenanceReminder');
    }
}
