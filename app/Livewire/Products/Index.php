<?php

declare(strict_types=1);

namespace App\Livewire\Products;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
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
                TextColumn::make('owner_name')
                    ->label(__('Owner name'))
                    ->searchable(),
                TextColumn::make('installer_name')
                    ->label(__('Installer name'))
                    ->searchable(),
                TextColumn::make('user_id')
                    ->label(__('User ID'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('city')
                    ->label(__('City'))
                    ->searchable(),
                TextColumn::make('street')
                    ->label(__('Street'))
                    ->searchable(),
                TextColumn::make('zip')
                    ->label(__('Zip'))
                    ->searchable(),
                TextColumn::make('purchase_place')
                    ->label(__('Purchase place'))
                    ->searchable(),
                TextColumn::make('serial_number')
                    ->label(__('Serial number'))
                    ->searchable(),
                TextColumn::make('comments')
                    ->label(__('Comments'))
                    ->searchable(),
                TextColumn::make('installation_date')
                    ->label(__('Installation date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('warrantee_date')
                    ->label(__('Warranty'))
                    ->date()
                    ->sortable(),
                TextColumn::make('purchase_date')
                    ->label(__('Purchase date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('tool.name')
                    ->label(__('Tool'))
                    ->searchable()
                    ->sortable(),
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
                    ->url(fn (Product $record): string => route('products.edit', ['product' => $record->id])),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    protected function getTableQuery(): Builder
    {
        return Product::query();
    }

    public function render(): Factory|View
    {
        return view('livewire.products.index');
    }
}
