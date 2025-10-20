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
                            ]),

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

        ProductLog::create([
            'product_id' => $this->product->id,
            'what' => $data['what'],
            'comment' => $data['comment'] ?? null,
            'when' => now(),
        ]);

        $this->eventForm->fill();
        $this->product->load('product_logs');

        Notification::make()
            ->success()
            ->title(__('Event created successfully'))
            ->send();
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
