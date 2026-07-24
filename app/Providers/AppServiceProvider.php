<?php

declare(strict_types=1);

namespace App\Providers;

use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Infolists\Components\Entry;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return ($user->hasRole('Super Admin') || $user->hasRole('Admin')) ? true : null;
        });
        Model::automaticallyEagerLoadRelationships();

        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);

        Table::configureUsing(fn (Table $table): Table => $table->reorderableColumns());
        Field::configureUsing(static fn (Field $field): Field => $field->translateLabel());
        Column::configureUsing(static fn (Column $column): Column => $column->translateLabel()->searchable()->toggleable()->sortable());
        Entry::configureUsing(static fn (Entry $entry): Entry => $entry->translateLabel());
        Action::configureUsing(static fn (Action $action): Action => $action->translateLabel());
        BaseFilter::configureUsing(static fn (BaseFilter $filter): BaseFilter => $filter->translateLabel());
    }
}
