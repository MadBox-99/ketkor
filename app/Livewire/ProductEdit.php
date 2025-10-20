<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductLog;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ProductEdit extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?Product $product = null;

    public ?array $productData = [];

    public ?array $eventData = [];

    public ?array $ownerData = [];

    public bool $userVisibility = false;

    protected function getForms(): array
    {
        return [
            'productForm',
            'eventForm',
            'ownerForm',
        ];
    }

    public function mount(Product $product): void
    {
        $this->product = $product;

        // Check user visibility
        $user = Auth::user();
        $visible = $product->are_visible->first();
        $this->userVisibility = $visible && $visible->isVisible;

        // Fill product form
        $this->productForm->fill([
            'serial_number' => $product->serial_number,
            'city' => $product->city,
            'street' => $product->street,
            'zip' => $product->zip,
            'owner_name' => $product->owner_name,
            'purchase_date' => $product->purchase_date,
            'installation_date' => $product->installation_date,
            'warrantee_date' => $product->warrantee_date,
            'tool_id' => $product->tool_id,
            'user_ids' => $product->users->pluck('id')->toArray(),
        ]);

        // Fill owner form if has visibility
        if ($this->userVisibility && $product->partials->isNotEmpty()) {
            $firstPartial = $product->partials->first();
            $this->ownerForm->fill([
                'name' => $firstPartial->name,
                'email' => $firstPartial->email,
                'phone' => $firstPartial->phone,
            ]);
        }
    }

    public function productForm(Schema $schema): Schema
    {
        $user = Auth::user();
        $isOrganizerOrServicer = $user->hasAnyRole(['Organizer', 'Servicer']);
        $isAdminOrOperator = $user->hasAnyRole(['Admin', 'Operator']);

        return $schema
            ->components([
                Section::make(__('Product Information'))
                    ->description(__("Update product's informations."))
                    ->components([
                        TextInput::make('serial_number')
                            ->label(__('Serial number'))
                            ->required()
                            ->disabled($isOrganizerOrServicer)
                            ->maxLength(200),

                        TextInput::make('city')
                            ->label(__('City'))
                            ->disabled($isOrganizerOrServicer ? ! $this->userVisibility : ! $this->userVisibility)
                            ->maxLength(200),

                        TextInput::make('street')
                            ->label(__('Street'))
                            ->disabled($isOrganizerOrServicer ? ! $this->userVisibility : ! $this->userVisibility)
                            ->maxLength(200),

                        TextInput::make('zip')
                            ->label(__('Zip'))
                            ->disabled($isOrganizerOrServicer ? ! $this->userVisibility : ! $this->userVisibility)
                            ->maxLength(4),

                        TextInput::make('owner_name')
                            ->label(__('Owner name'))
                            ->disabled()
                            ->hidden(! $this->userVisibility)
                            ->maxLength(200),

                        Section::make(__('Important dates'))
                            ->description(__('Purchase and installation dates are critical for consumer protection'))
                            ->components([
                                DatePicker::make('purchase_date')
                                    ->label(__('Purchase date'))
                                    ->required()
                                    ->disabled($isOrganizerOrServicer)
                                    ->native(false),

                                DatePicker::make('installation_date')
                                    ->label(__('Installation date'))
                                    ->required()
                                    ->disabled($isOrganizerOrServicer)
                                    ->native(false),

                                DatePicker::make('warrantee_date')
                                    ->label(__('Warrantee date'))
                                    ->required()
                                    ->disabled($isOrganizerOrServicer)
                                    ->native(false),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),

                        Select::make('user_ids')
                            ->label(__('Users'))
                            ->multiple()
                            ->relationship('users', 'name')
                            ->preload()
                            ->hidden(! $isAdminOrOperator),

                        Select::make('tool_id')
                            ->label(__('Tool'))
                            ->relationship('tool', 'name')
                            ->required()
                            ->disabled(! $this->userVisibility)
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),
            ])
            ->statePath('productData')
            ->model($this->product);
    }

    public function eventForm(Schema $schema): Schema
    {
        $hasCommissioning = $this->product->product_logs()->where('what', 'commissioning')->exists();
        $maintenanceCount = $this->product->product_logs()->where('what', 'maintenance')->count();

        return $schema
            ->components([
                Section::make(__('Product Evensts'))
                    ->description(__('Create product event.'))
                    ->components([
                        Select::make('what')
                            ->label(__('Operation type'))
                            ->required()
                            ->options([
                                'installation' => __('Installation'),
                                'maintenance' => __('Maintenance'),
                                'commissioning' => __('Commissioning'),
                            ])
                            ->disableOptionWhen(function (string $value): bool {
                                // Disable commissioning if already exists
                                if ($value === 'commissioning') {
                                    return $this->product->product_logs()->where('what', 'commissioning')->exists();
                                }

                                // Disable maintenance if commissioning doesn't exist
                                if ($value === 'maintenance') {
                                    $hasCommissioning = $this->product->product_logs()->where('what', 'commissioning')->exists();

                                    if (! $hasCommissioning) {
                                        return true;
                                    }

                                    // Disable if already 2 maintenances
                                    $maintenanceCount = $this->product->product_logs()->where('what', 'maintenance')->count();

                                    return $maintenanceCount >= 2;
                                }

                                return false;
                            })
                            ->helperText(function () use ($hasCommissioning, $maintenanceCount): ?string {
                                if (! $hasCommissioning) {
                                    return __('Commissioning must be completed first');
                                }

                                if ($maintenanceCount >= 2) {
                                    return __('Maximum 2 maintenance operations reached');
                                }

                                return null;
                            }),

                        Textarea::make('comment')
                            ->label(__('comment'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('eventData');
    }

    public function ownerForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Owner Datas'))
                    ->description(__('Update owner information'))
                    ->components([
                        TextInput::make('name')
                            ->label(__('Owner name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label(__('Mobile'))
                            ->tel()
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(3),
            ])
            ->statePath('ownerData');
    }

    public function updateProduct(): void
    {
        $data = $this->productForm->getState();

        $this->product->update([
            'serial_number' => $data['serial_number'],
            'city' => $data['city'],
            'street' => $data['street'],
            'zip' => $data['zip'],
            'purchase_date' => $data['purchase_date'],
            'installation_date' => $data['installation_date'],
            'warrantee_date' => $data['warrantee_date'],
            'tool_id' => $data['tool_id'],
        ]);

        // Sync users if admin/operator
        if (Auth::user()->hasAnyRole(['Admin', 'Operator']) && isset($data['user_ids'])) {
            $this->product->users()->sync($data['user_ids']);
        }

        Notification::make()
            ->success()
            ->title(__('Product updated successfully.'))
            ->send();
    }

    public function createEvent(): void
    {
        $data = $this->eventForm->getState();

        // Validate time windows
        if (! $this->validateEventTiming($data['what'])) {
            return;
        }

        // Create the event log
        ProductLog::create([
            'product_id' => $this->product->id,
            'what' => $data['what'],
            'comment' => $data['comment'] ?? null,
            'when' => now(),
        ]);

        // Update product dates based on event type
        if ($data['what'] === 'commissioning') {
            $this->product->update([
                'installation_date' => now(),
                'warrantee_date' => now()->addYear(),
            ]);
        } elseif ($data['what'] === 'maintenance') {
            $this->product->update([
                'warrantee_date' => now()->addYear(),
            ]);
        }

        $this->eventForm->fill();
        $this->product->load('product_logs');

        Notification::make()
            ->success()
            ->title(__('Event created successfully'))
            ->send();
    }

    protected function validateEventTiming(string $eventType): bool
    {
        if ($eventType === 'commissioning') {
            return $this->validateCommissioningTiming();
        } elseif ($eventType === 'maintenance') {
            return $this->validateMaintenanceTiming();
        }

        return true;
    }

    protected function validateCommissioningTiming(): bool
    {
        $purchaseDate = $this->product->purchase_date;

        // Check if purchase date exists
        if (! $purchaseDate) {
            Notification::make()
                ->danger()
                ->title(__('Purchase date is required for commissioning'))
                ->send();

            return false;
        }

        // Check if commissioning is within 6 months of purchase
        $sixMonthsAfterPurchase = $purchaseDate->copy()->addMonths(6);

        if (now()->greaterThan($sixMonthsAfterPurchase)) {
            Notification::make()
                ->danger()
                ->title(__('Commissioning must be done within 6 months of purchase date'))
                ->send();

            return false;
        }

        return true;
    }

    protected function validateMaintenanceTiming(): bool
    {
        $commissioning = $this->product->product_logs()
            ->where('what', 'commissioning')
            ->first();

        // Get last maintenance or use commissioning date
        $lastMaintenance = $this->product->product_logs()
            ->where('what', 'maintenance')
            ->latest('when')
            ->first();

        $referenceDate = $lastMaintenance
            ? \Carbon\Carbon::parse($lastMaintenance->when)
            : \Carbon\Carbon::parse($commissioning->when);

        // Check if maintenance is within 11-13 months window
        $elevenMonthsAfter = $referenceDate->copy()->addMonths(11);
        $thirteenMonthsAfter = $referenceDate->copy()->addMonths(13);

        if (now()->lessThan($elevenMonthsAfter)) {
            Notification::make()
                ->danger()
                ->title(__('Maintenance can only be performed 11-13 months after commissioning or last maintenance'))
                ->send();

            return false;
        }

        if (now()->greaterThan($thirteenMonthsAfter)) {
            Notification::make()
                ->danger()
                ->title(__('Maintenance window (11-13 months) has expired'))
                ->send();

            return false;
        }

        return true;
    }

    public function updateOwner(): void
    {
        $data = $this->ownerForm->getState();

        $this->product->partials()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);

        $this->product->load('partials');

        Notification::make()
            ->success()
            ->title(__('Owner data updated successfully'))
            ->send();
    }

    public function render()
    {
        return view('livewire.product-edit');
    }
}
