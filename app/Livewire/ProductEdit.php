<?php

namespace App\Livewire;

use App\Enums\UserRole;
use App\Mail\AccessGrantMail;
use App\Models\AccessToken;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;

class ProductEdit extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
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

    public function mount(Product $product, bool $userVisibility): void
    {

        $this->product = $product;
        $this->userVisibility = $userVisibility;

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
        /** @var User $user */
        $user = Auth::user();
        $isOrganizerOrServicer = $user->hasAnyRole(['Organizer', 'Servicer']);
        $isAdminOrOperator = $user->hasAnyRole(['Admin', 'Operator', 'Super-Admin']);

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
                            ->visible($this->userVisibility)
                            ->maxLength(200),

                        TextInput::make('street')
                            ->label(__('Street'))
                            ->visible($this->userVisibility)
                            ->maxLength(200),

                        TextInput::make('zip')
                            ->label(__('Zip'))
                            ->visible($this->userVisibility)
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
                            ->label(__('Product'))
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
        $commissioning = $this->product->product_logs()->where('what', 'commissioning')->first();

        return $schema
            ->components([
                Section::make(__('Product Evensts'))
                    ->description(__('Create product event.'))
                    ->components([
                        Select::make('what')
                            ->label(__('Type of work'))
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

                                // Check maintenance timing if there are any previous events
                                if ($value === 'maintenance') {
                                    $lastMaintenance = $this->product->product_logs()
                                        ->where('what', 'maintenance')
                                        ->latest('when')
                                        ->first();

                                    // If there's a previous maintenance, check 11 months window
                                    if ($lastMaintenance) {
                                        $elevenMonthsAfter = Date::parse($lastMaintenance->when)->addMonths(11);

                                        if (now()->lessThan($elevenMonthsAfter)) {
                                            return true;
                                        }
                                    }

                                    // Check if there's commissioning to use as reference
                                    $commissioning = $this->product->product_logs()->where('what', 'commissioning')->first();
                                    if ($commissioning && ! $lastMaintenance) {
                                        $elevenMonthsAfter = Date::parse($commissioning->when)->addMonths(11);

                                        if (now()->lessThan($elevenMonthsAfter)) {
                                            return true;
                                        }
                                    }

                                    return false;
                                }

                                return false;
                            })
                            ->helperText(function () use ($commissioning): ?string {
                                // Check if maintenance is available based on timing
                                $lastMaintenance = $this->product->product_logs()
                                    ->where('what', 'maintenance')
                                    ->latest('when')
                                    ->first();

                                // If there's a previous maintenance, show when next one is available
                                if ($lastMaintenance) {
                                    $elevenMonthsAfter = Date::parse($lastMaintenance->when)->addMonths(11);

                                    if (now()->lessThan($elevenMonthsAfter)) {
                                        return __('Maintenance can only be performed 11 months after last maintenance');
                                    }
                                }

                                // If there's commissioning but no maintenance yet
                                if ($commissioning && ! $lastMaintenance) {
                                    $elevenMonthsAfter = Date::parse($commissioning->when)->addMonths(11);

                                    if (now()->lessThan($elevenMonthsAfter)) {
                                        return __('Maintenance can only be performed 11 months after commissioning');
                                    }
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
        if (Auth::user()->hasAnyRole([UserRole::Admin, UserRole::Operator]) && isset($data['user_ids'])) {
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
        ProductLog::query()->create([
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
        }
        if ($eventType === 'maintenance') {
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
        // Get last maintenance
        $lastMaintenance = $this->product->product_logs()
            ->where('what', 'maintenance')
            ->latest('when')
            ->first();

        // If there's a previous maintenance, validate timing from it
        if ($lastMaintenance) {
            $referenceDate = Date::parse($lastMaintenance->when);
            $elevenMonthsAfter = $referenceDate->copy()->addMonths(11);
            $thirteenMonthsAfter = $referenceDate->copy()->addMonths(13);

            if (now()->lessThan($elevenMonthsAfter)) {
                Notification::make()
                    ->danger()
                    ->title(__('Maintenance can only be performed 11-13 months after last maintenance'))
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

        // If there's commissioning but no maintenance yet, validate from commissioning
        $commissioning = $this->product->product_logs()
            ->where('what', 'commissioning')
            ->first();

        if ($commissioning) {
            $referenceDate = Date::parse($commissioning->when);
            $elevenMonthsAfter = $referenceDate->copy()->addMonths(11);
            $thirteenMonthsAfter = $referenceDate->copy()->addMonths(13);

            if (now()->lessThan($elevenMonthsAfter)) {
                Notification::make()
                    ->danger()
                    ->title(__('Maintenance can only be performed 11-13 months after commissioning'))
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

        // No commissioning or maintenance - allow maintenance anytime
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

    public function permissionAction(): Action
    {
        return Action::make('permission')
            ->label(__('Request Permission'))
            ->color('warning')
            ->action(function (): void {
                config('mail.from.address');
                $admin = User::whereEmail(env('ADMIN_EMAIL'))->first();
                $token = Str::random(40); // Generate a unique token
                $user_id = Auth::user()->id;
                // Store the token in the database
                $accessToken = AccessToken::query()->firstOrCreate([
                    'user_id' => $user_id,
                    'product_id' => $this->product->id,
                ]);

                $accessToken->update(['token' => $token, 'used' => false]);

                $user = Auth::user();

                Mail::to($admin)->cc($user->email)->send(new AccessGrantMail($token, $user->name));
                Notification::make()
                    ->title(__('Succesfuly send an email to administrator who will grant an access to private datas, please wait until is access in grant.'))
                    ->success()
                    ->send();

            });
    }

    public function render(): Factory|View
    {
        return view('livewire.product-edit');
    }
}
