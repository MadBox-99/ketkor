<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\UserRole;
use App\Filament\Forms\Components\SignaturePad;
use App\Mail\AccessGrantMail;
use App\Mail\WorksheetMail;
use App\Models\AccessToken;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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
            $newestPartial = $product->partials->last();
            $this->ownerForm->fill([
                'name' => $newestPartial->name,
                'email' => $newestPartial->email,
                'phone' => $newestPartial->phone,
            ]);
        }
        $this->eventForm->fill();
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
                    ->description(__('Update product\'s informations.'))
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
                            ]),

                        Textarea::make('comment')
                            ->label(__('Work description'))
                            ->rows(4)
                            ->columnSpanFull(),

                        Checkbox::make('is_online')
                            ->live()
                            ->label(__('Garrantee paper filled online'))
                            ->default(true),
                        TextInput::make('worksheet_id')
                            ->label(__('Worksheet ID'))
                            ->required(fn (Get $get): bool => $get('is_online') === false)
                            ->visible(fn (Get $get): bool => $get('is_online') === false),
                        SignaturePad::make('signature')
                            ->visible(fn (Get $get): bool => $get('is_online') === true)
                            ->label(__('Signature'))
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
            'is_online' => $data['is_online'] ?? true,
            'worksheet_id' => $data['worksheet_id'] ?? null,
            'signature' => $data['signature'] ?? null,
            'when' => now(),
        ]);
        $updateData = [];
        // Update product dates based on event type
        if ($data['what'] === 'commissioning') {
            if (! $this->product->product_logs()->where('what', 'commissioning')->exists()) {
                $updateData = ['warrantee_date' => now()->addYear()];
            }
            $updateData['installation_date'] = now();

            $this->product->update($updateData);
        }

        if ($data['what'] === 'maintenance') {
            $commissioning = $this->product->product_logs()
                ->where('what', 'commissioning')
                ->first();

            $maintenanceCount = $this->product->product_logs()
                ->where('what', 'maintenance')
                ->count();

            $shouldExtendWarranty = false;

            // First maintenance: check if within 6 months of commissioning
            if ($maintenanceCount === 0 && $commissioning) {
                $sixMonthsAfterCommissioning = Date::parse($commissioning->when)->addMonths(6);
                if (now()->lessThanOrEqualTo($sixMonthsAfterCommissioning)) {
                    $shouldExtendWarranty = true;
                }
            }

            // Second maintenance: check if within 11-13 months of first maintenance
            if ($maintenanceCount === 1) {
                $firstMaintenance = $this->product->product_logs()
                    ->where('what', 'maintenance')
                    ->oldest('when')
                    ->first();

                if ($firstMaintenance) {
                    $elevenMonthsAfter = Date::parse($firstMaintenance->when)->addMonths(11);
                    $thirteenMonthsAfter = Date::parse($firstMaintenance->when)->addMonths(13);

                    if (now()->between($elevenMonthsAfter, $thirteenMonthsAfter)) {
                        $shouldExtendWarranty = true;
                    }
                }
            }

            if ($shouldExtendWarranty) {
                $this->product->update([
                    'warrantee_date' => now()->addYear(),
                ]);
            }
        }

        $this->eventForm->fill();

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
                    ->title(__('Garrantee maintenance can only be performed 11-13 months after commissioning'))
                    ->send();
            }

            if (now()->greaterThan($thirteenMonthsAfter)) {
                Notification::make()
                    ->danger()
                    ->title(__('Maintenance window (11-13 months) has expired'))
                    ->send();
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
                    ->title(__('Garrantee maintenance can only be performed 11-13 months after commissioning'))
                    ->send();
            }

            if (now()->greaterThan($thirteenMonthsAfter)) {
                Notification::make()
                    ->danger()
                    ->title(__('Maintenance window (11-13 months) has expired'))
                    ->send();
            }
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

    public function generateWorksheetAction(): Action
    {
        return Action::make('generateWorksheet')
            ->label(__('Generate Worksheet'))
            ->icon('heroicon-o-document-text')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading(__('Generate and Send Worksheet'))
            ->modalDescription(__('his will generate a PDF worksheet and send it to the owners email address.'))
            ->action(function (array $arguments): void {
                // Get productLog from arguments
                $productLogId = $arguments['productLogId'] ?? null;
                if (! $productLogId) {
                    Notification::make()
                        ->danger()
                        ->title(__('Error'))
                        ->body(__('Product log not found.'))
                        ->send();

                    return;
                }

                $productLog = ProductLog::find($productLogId);
                if (! $productLog) {
                    Notification::make()
                        ->danger()
                        ->title(__('Error'))
                        ->body(__('Product log not found.'))
                        ->send();

                    return;
                }

                // Load necessary relationships
                $owner = $this->product->partials->last();

                // Check if owner email exists
                if (! $owner || ! $owner->email) {
                    Notification::make()
                        ->danger()
                        ->title(__('Owner email is required'))
                        ->body(__('Please add owner information before generating worksheet.'))
                        ->send();

                    return;
                }

                // Get the current user (servicer)
                $servicer = Auth::user();

                // Generate PDF
                $pdf = Pdf::loadView('pdf.worksheet', [
                    'product' => $this->product,
                    'productLog' => $productLog,
                    'owner' => $owner,
                    'servicer' => $servicer,
                ]);

                $pdfContent = $pdf->output();

                // Send email with PDF attachment
                Mail::to($owner->email)->send(new WorksheetMail(
                    $this->product,
                    $productLog,
                    $pdfContent,
                ));

                Notification::make()
                    ->success()
                    ->title(__('Worksheet generated and sent'))
                    ->body(__('The worksheet has been sent to') . ' ' . $owner->email)
                    ->send();
            });
    }

    public function viewSignatureAction(): Action
    {
        return Action::make('viewSignature')
            ->label(__('View Signature'))
            ->icon('heroicon-o-pencil-square')
            ->color('info')
            ->modalHeading(__('Signature'))
            ->modalContent(function (array $arguments): View {
                $productLogId = $arguments['productLogId'] ?? null;
                $productLog = $productLogId ? ProductLog::find($productLogId) : null;

                return view('livewire.signature-preview', [
                    'signature' => $productLog?->signature,
                ]);
            })
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('Close'));
    }

    public function render(): Factory|View
    {
        return view('livewire.product-edit');
    }
}
