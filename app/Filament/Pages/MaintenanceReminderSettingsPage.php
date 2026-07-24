<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\MaintenanceReminderStage;
use App\Models\MaintenanceReminderSetting;
use App\Models\Product;
use App\Models\User;
use App\Support\MaintenanceReminderTemplateRenderer;
use App\Support\MaintenanceSchedule;
use App\Support\PendingMaintenanceReminder;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
final class MaintenanceReminderSettingsPage extends Page
{
    protected string $view = 'filament.pages.maintenance-reminder-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'Karbantartás';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Emlékeztető beállítások';

    protected static ?string $title = 'Karbantartás emlékeztető beállítások';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public ?string $previewSubject = null;

    public ?string $previewBody = null;

    public function mount(): void
    {
        $this->form->fill(MaintenanceReminderSetting::current()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Ütemezés')
                        ->schema([
                            Toggle::make('enabled')
                                ->label('Emlékeztetők küldése bekapcsolva'),
                            TagsInput::make('advance_days')
                                ->label('Előidők (nap)')
                                ->helperText('Hány nappal az esedékesség előtt menjen ki emlékeztető. Ha üres, csak a lejárt emlékeztetők kerülnek kiküldésre.')
                                ->placeholder('30')
                                ->columnSpanFull(),
                            TextInput::make('overdue_repeat_days')
                                ->label('Lejárt emlékeztető ismétlése (nap)')
                                ->numeric()
                                ->minValue(1)
                                ->required(),
                            TextInput::make('overdue_max_count')
                                ->label('Lejárt emlékeztetők maximális száma')
                                ->numeric()
                                ->minValue(0)
                                ->required(),
                        ])
                        ->columns(2),
                    Section::make('Kapcsolatfelvétel')
                        ->schema([
                            TextInput::make('contact_phone')
                                ->label('Telefon')
                                ->tel()
                                ->maxLength(100),
                            TextInput::make('contact_email')
                                ->label('E-mail')
                                ->email()
                                ->maxLength(255),
                            TextInput::make('booking_url')
                                ->label('Időpontfoglaló link')
                                ->url()
                                ->maxLength(500)
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                    Section::make('E-mail sablon')
                        ->schema([
                            TextInput::make('email_subject')
                                ->label('Tárgy')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('email_body')
                                ->label('Törzs')
                                ->required()
                                ->rows(14)
                                ->helperText(
                                    'Használható változók: {{ owner_name }}, {{ serial_number }}, '
                                    . '{{ tool_name }}, {{ maintenance_type }}, {{ last_maintenance_date }}, '
                                    . '{{ due_date }}, {{ contact_phone }}, {{ contact_email }}, {{ booking_url }}',
                                ),
                        ]),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Mentés')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                            Action::make('preview')
                                ->label('Előnézet')
                                ->color('gray')
                                ->action('preview'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $data['advance_days'] = collect($data['advance_days'] ?? [])
            ->map(fn (int|string $days): int => (int) $days)
            ->filter(fn (int $days): bool => $days > 0)
            ->values()
            ->all();

        MaintenanceReminderSetting::current()->update($data);

        Notification::make()
            ->success()
            ->title('A beállítások elmentve')
            ->send();
    }

    /**
     * Az űrlap aktuális (még nem mentett) állapotával rendereli a sablont
     * az első olyan készülék adataival, amelyre számítható esedékesség.
     */
    public function preview(): void
    {
        $data = $this->form->getState();

        $product = Product::query()
            ->whereNotNull('installation_date')
            ->oldest('id')
            ->first();

        $schedule = $product === null ? null : MaintenanceSchedule::for($product);

        if (! $schedule instanceof MaintenanceSchedule) {
            $this->previewSubject = null;
            $this->previewBody = null;

            Notification::make()
                ->warning()
                ->title('Nincs mintaként használható készülék')
                ->body('Az előnézethez legalább egy beüzemelési dátummal rendelkező készülék szükséges.')
                ->send();

            return;
        }

        $settings = new MaintenanceReminderSetting($data);

        $rendered = resolve(MaintenanceReminderTemplateRenderer::class)->render(
            new PendingMaintenanceReminder(
                product: $product,
                user: new User(['name' => 'Minta Ügyfél', 'email' => 'minta@example.test']),
                stage: MaintenanceReminderStage::Advance,
                stageKey: (int) ($settings->advance_days[0] ?? 30),
                schedule: $schedule,
            ),
            $settings,
        );

        $this->previewSubject = $rendered['subject'];
        $this->previewBody = $rendered['body'];
    }
}
