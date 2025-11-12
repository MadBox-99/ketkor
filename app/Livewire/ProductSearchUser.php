<?php

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
                    ->getStateUsing(function (Product $record): string {
                        if ($record->are_visible->isEmpty() || ! $record->are_visible[0]->isVisible) {
                            return '';
                        }

                        return $record->partials->first()?->name ?? '-';
                    })
                    ->placeholder('-')
                    ->html()
                    ->formatStateUsing(function (Product $record, string $state): string {
                        if ($record->are_visible->isEmpty() || ! $record->are_visible[0]->isVisible) {
                            return '<a href="'.route('accestokens.createAccessToken', ['product' => $record->id]).'" class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                '.__('Request access').'
                            </a>';
                        }

                        return htmlspecialchars($state);
                    }),
                TextColumn::make('tool.category')
                    ->label(__('Type'))
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function (Product $record) {
                        if ($record->are_visible->isEmpty() || ! $record->are_visible[0]->isVisible) {
                            return '-';
                        }

                        return $record->tool->category ?? '-';
                    })
                    ->placeholder('-'),
                TextColumn::make('tool.name')
                    ->label(__('Tool'))
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function (Product $record): string {
                        if ($record->are_visible->isEmpty() || ! $record->are_visible[0]->isVisible) {
                            return '-';
                        }

                        return $record->tool->name ?? '-';
                    })
                    ->placeholder('-'),
                TextColumn::make('location')
                    ->label(__('Location'))
                    ->getStateUsing(function (Product $record): string {
                        if ($record->are_visible->isEmpty() || ! $record->are_visible[0]->isVisible) {
                            return '-';
                        }

                        $location = trim(($record->city ?? '').' '.($record->street ?? ''));

                        return $location ?: '-';
                    })
                    ->placeholder('-'),
                TextColumn::make('warrantee_date')
                    ->label(__('Warranty'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->getStateUsing(function (Product $record): ?string {
                        if ($record->are_visible->isEmpty() || ! $record->are_visible[0]->isVisible) {
                            return null;
                        }

                        return $record->warrantee_date ? $record->serializeDate($record->warrantee_date) : null;
                    })
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
                        fn (Builder $query, $value): Builder => $query->where('serial_number', 'LIKE', "%{$value}%")
                    ))
                    ->indicateUsing(function (array $data): ?string {
                        if (! ($data['value'] ?? null)) {
                            return null;
                        }

                        return __('Serial number').': '.$data['value'];
                    }),
                Filter::make('tool_name')
                    ->schema([
                        TextInput::make('value')
                            ->label(__('Tool name'))
                            ->placeholder(__('Search by tool...')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, $value): Builder => $query->whereRelation('tool', 'name', 'LIKE', "%{$value}%")
                    ))
                    ->indicateUsing(function (array $data): ?string {
                        if (! ($data['value'] ?? null)) {
                            return null;
                        }

                        return __('Tool name').': '.$data['value'];
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
                        fn (Builder $query, $date): Builder => $query->whereDate('warrantee_date', '>=', $date)
                    )->when(
                        $data['to'] ?? null,
                        fn (Builder $query, $date): Builder => $query->whereDate('warrantee_date', '<=', $date)
                    ))
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators['from'] = __('Warranty from').': '.$data['from'];
                        }

                        if ($data['to'] ?? null) {
                            $indicators['to'] = __('Warranty to').': '.$data['to'];
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                /* Action::make('permission')
                    ->label(__('Require access'))
                    ->icon(Heroicon::OutlinedEye)
                    ->hidden(fn (Product $record): bool => ! $record->are_visible->isEmpty() && $record->are_visible[0]->isVisible)
                    ->url(fn (Product $record): string => route('accestokens.createAccessToken', ['product' => $record->id])), */
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
            ->with(['partials', 'are_visible', 'tool'])
            ->whereHas('users', function (Builder $query) use ($user): void {
                $query->where('user_id', $user->id);
            });
    }

    public function render(): Factory|View
    {
        return view('livewire.product-search-user');
    }
}
