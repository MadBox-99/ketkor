<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProductSearchUser extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('serial_number')
                    ->label(__('Serial number'))
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->weight('medium'),
                TextColumn::make('owner')
                    ->label(__('Owner'))
                    ->getStateUsing(fn(Product $record): string => $record->partials->first()?->name ?? '-')
                    ->placeholder('-'),
                TextColumn::make('tool.category')
                    ->label(__('Type'))
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('tool.name')
                    ->label(__('Tool'))
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('location')
                    ->label(__('Location'))
                    ->placeholder('-'),
                TextColumn::make('warrantee_date')
                    ->label(__('Warranty'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->filters([
                Filter::make('serial_number')
                    ->schema([
                        TextInput::make('value')
                            ->label(__('Serial number'))
                            ->placeholder(__('Search by serial...')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, $value): Builder => $query->where('serial_number', 'LIKE', "%{$value}%"),
                    ))
                    ->indicateUsing(function (array $data): ?string {
                        if (! ($data['value'] ?? null)) {
                            return null;
                        }

                        return __('Serial number') . ': ' . $data['value'];
                    }),
                Filter::make('tool_name')
                    ->schema([
                        TextInput::make('value')
                            ->label(__('Tool name'))
                            ->placeholder(__('Search by tool...')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, $value): Builder => $query->whereRelation('tool', 'name', 'LIKE', "%{$value}%"),
                    ))
                    ->indicateUsing(function (array $data): ?string {
                        if (! ($data['value'] ?? null)) {
                            return null;
                        }

                        return __('Tool name') . ': ' . $data['value'];
                    }),
                Filter::make('warranty_date')
                    ->schema([
                        DatePicker::make('from')
                            ->label(__('Warranty from')),
                        DatePicker::make('to')
                            ->label(__('Warranty to')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['from'] ?? null,
                        fn (Builder $query, $date): Builder => $query->whereDate('warrantee_date', '>=', $date),
                    )->when(
                        $data['to'] ?? null,
                        fn (Builder $query, $date): Builder => $query->whereDate('warrantee_date', '<=', $date),
                    ))
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators['from'] = __('Warranty from') . ': ' . $data['from'];
                        }

                        if ($data['to'] ?? null) {
                            $indicators['to'] = __('Warranty to') . ': ' . $data['to'];
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->label(__('View details'))
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (Product $record): string => route('products.edit', ['product' => $record->id])),
                Action::make('delete')
                    ->label(__('Remove'))
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('Remove product'))
                    ->modalDescription(__('Are you sure you want to remove this product from your list?'))
                    ->successNotificationTitle(__('Product removed from your list'))
                    ->action(function (Product $record): void {
                        $user = Auth::user();
                        $record->users()->detach($user->id);
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading(__('No products found'))
            ->emptyStateDescription(__('You don\'t have any products yet or no products match your search criteria.'))
            ->emptyStateIcon(Heroicon::OutlinedInboxStack);
    }

    protected function getTableQuery(): Builder
    {
        $user = Auth::user();

        return Product::query()
            ->whereHas('users', function (Builder $query) use ($user): void {
                $query->where('user_id', $user->id);
            });
    }

    public function render(): Factory|View
    {
        return view('livewire.product-search-user');
    }
}
