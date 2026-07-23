<?php

declare(strict_types=1);

namespace App\Livewire\Products;

use App\Enums\UserRole;
use App\Livewire\Products\Concerns\BuildsProductSchemas;
use App\Livewire\Products\Support\MaintenanceWindow;
use App\Mail\WorksheetMail;
use App\Models\Partial;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\Tool;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Edit extends Component implements HasActions, HasSchemas
{
    use BuildsProductSchemas;
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?Product $product = null;

    public ?array $productData = [];

    public ?array $eventData = [];

    public ?array $ownerData = [];

    /** @var Collection<int, Partial> */
    public Collection $partials;

    /** @var Collection<int, User> */
    public Collection $users;

    /** @var Collection<int, Tool> */
    public Collection $tools;

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
        $this->partials = Partial::query()
            ->where('product_id', $product->id)
            ->latest()
            ->limit(6)
            ->get();
        $this->users = User::query()->orderBy('name')->get();
        $this->tools = Tool::query()->orderBy('name')->get();

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
        if ($product->partials->isNotEmpty()) {
            $newestPartial = $product->partials->last();
            $this->ownerForm->fill([
                'name' => $newestPartial->name,
                'email' => $newestPartial->email,
                'phone' => $newestPartial->phone,
            ]);
        }
        $this->eventForm->fill();
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

        /** @var User $user */
        $user = Auth::user();

        // Sync users if admin/operator
        if ($user->hasAnyRole([UserRole::Admin, UserRole::Operator, UserRole::SuperAdmin]) && isset($data['user_ids'])) {
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
                    $window = new MaintenanceWindow(Date::parse($firstMaintenance->when));

                    if ($window->contains(now())) {
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
            $window = new MaintenanceWindow(Date::parse($lastMaintenance->when));

            if ($window->isBeforeWindow(now())) {
                Notification::make()
                    ->danger()
                    ->title(__('Garrantee maintenance can only be performed 11-13 months after commissioning'))
                    ->send();
            }

            if ($window->isAfterWindow(now())) {
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
            $window = new MaintenanceWindow(Date::parse($commissioning->when));

            if ($window->isBeforeWindow(now())) {
                Notification::make()
                    ->danger()
                    ->title(__('Garrantee maintenance can only be performed 11-13 months after commissioning'))
                    ->send();
            }

            if ($window->isAfterWindow(now())) {
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

    public function generateWorksheetAction(): Action
    {
        return Action::make('generateWorksheet')
            ->label(__('Generate Worksheet'))
            ->icon(Heroicon::OutlinedDocumentText)
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

                $productLog = ProductLog::query()->find($productLogId);
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
                /** @var User $servicer */
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
                Mail::to($servicer->email)->send(new WorksheetMail(
                    $this->product,
                    $productLog,
                    $pdfContent,
                ));
                if ($owner || $owner->email) {
                    Mail::to($owner->email)->send(new WorksheetMail(
                        $this->product,
                        $productLog,
                        $pdfContent,
                    ));
                }
                Mail::to('szervizpartner@ketkorkft.hu')->send(new WorksheetMail(
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
            ->icon(Heroicon::OutlinedPencilSquare)
            ->color('info')
            ->modalHeading(__('Signature'))
            ->modalContent(function (array $arguments): View {
                $productLogId = $arguments['productLogId'] ?? null;
                $productLog = $productLogId ? ProductLog::query()->find($productLogId) : null;

                return view('livewire.signature-preview', [
                    'signature' => $productLog?->signature,
                ]);
            })
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('Close'));
    }

    public function render(): Factory|View
    {
        return view('livewire.products.edit');
    }
}
