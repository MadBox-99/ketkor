<?php

namespace App\Livewire;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class ProductFilamentTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->with(['tool', 'partials' => function ($query) {
                $query->latest()->limit(1);
            }]))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('owner_name')
                    ->label(__('Owner name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('installer_name')
                    ->label(__('Installer name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('city')
                    ->label(__('City'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('street')
                    ->label(__('Street'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('zip')
                    ->label(__('Zip'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('purchase_place')
                    ->label(__('Purchase place'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('serial_number')
                    ->label(__('Serial number'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('purchase_date')
                    ->label(__('Purchase date'))
                    ->date()
                    ->sortable(),

                TextColumn::make('installation_date')
                    ->label(__('Installation date'))
                    ->date()
                    ->sortable(),

                TextColumn::make('warrantee_date')
                    ->label(__('Warrantee date'))
                    ->date()
                    ->sortable(),

                TextColumn::make('tool.name')
                    ->label(__('Tool name'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('tool')
                    ->relationship('tool', 'name')
                    ->label(__('Tool name')),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label(__('Edit'))
                    ->icon('heroicon-o-pencil')
                    ->url(fn (Product $record): string => route('products.edit', $record)),
                Action::make('delete')
                    ->label(__('Delete'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Product $record) {
                        $record->delete();
                        Notification::make()
                            ->success()
                            ->title(__('Product successfully deleted.'))
                            ->send();
                        $this->redirect(route('products.index'), navigate: true);
                    }),
            ])
            ->toolbarActions([
                Action::make('delete')
                    ->label(__('Bulk delete'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each->delete();
                        Notification::make()
                            ->success()
                            ->title(__('Products successfully deleted.'))
                            ->send();
                        $this->redirect(route('products.index'), navigate: true);
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->striped()
            ->defaultSort('id', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public function render()
    {
        return view('livewire.product-filament-table');
    }
}
