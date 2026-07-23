<?php

declare(strict_types=1);

namespace App\Livewire\Organizations;

use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('city')
                    ->label(__('City'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('tax_number')
                    ->label(__('Tax number'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('address')
                    ->label(__('Address'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('zip')
                    ->label(__('Zip'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label(__('Edit'))
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->url(fn (Organization $record): string => route('organizations.edit', ['organization' => $record->id])),
                Action::make('delete')
                    ->label(__('Delete'))
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Organization $record): void {
                        /** @var User $user */
                        $user = Auth::user();

                        if (! $user->hasAnyRole([UserRole::Admin, UserRole::SuperAdmin, UserRole::Operator])) {
                            Notification::make()
                                ->title(__('You do not have permission to perform this action.'))
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->delete();

                        Notification::make()
                            ->title(__('Organization deleted successfully.'))
                            ->success()
                            ->send();
                    }),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        return Organization::query();
    }

    public function render(): Factory|View
    {
        return view('livewire.organizations.index');
    }
}
