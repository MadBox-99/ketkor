<?php

declare(strict_types=1);

namespace App\Livewire\Products\Concerns;

use App\Enums\UserRole;
use App\Filament\Forms\Components\SignaturePad;
use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

trait BuildsProductSchemas
{
    public function productForm(Schema $schema): Schema
    {
        /** @var User $user */
        $user = Auth::user();
        $isOrganizerOrServicer = $user->hasAnyRole(['Organizer', 'Servicer']);
        $isAdminOrOperator = $user->hasAnyRole([UserRole::Admin, UserRole::Operator, UserRole::SuperAdmin]);

        return $schema
            ->components([
                Section::make(__('Product Information'))
                    ->description(__('Update product\'s informations.'))
                    ->components([
                        TextInput::make('serial_number')
                            ->label(__('Serial number'))
                            ->required()
                            ->readOnly($isOrganizerOrServicer)
                            ->maxLength(200),

                        TextInput::make('city')
                            ->label(__('City'))
                            ->maxLength(200),

                        TextInput::make('street')
                            ->label(__('Street'))
                            ->maxLength(200),

                        TextInput::make('zip')
                            ->label(__('Zip'))
                            ->maxLength(4),

                        TextInput::make('owner_name')
                            ->label(__('Owner name'))
                            ->readOnly($isOrganizerOrServicer)
                            ->maxLength(200),

                        Section::make(__('Important dates'))
                            ->description(__('Purchase and installation dates are critical for consumer protection'))
                            ->components([
                                DatePicker::make('purchase_date')
                                    ->label(__('Purchase date'))
                                    ->required()
                                    ->readOnly($isOrganizerOrServicer)
                                    ->native(false),

                                DatePicker::make('installation_date')
                                    ->label(__('Installation date'))
                                    ->required()
                                    ->readOnly($isOrganizerOrServicer)
                                    ->native(false),

                                DatePicker::make('warrantee_date')
                                    ->label(__('Warrantee date'))
                                    ->required()
                                    ->readOnly($isOrganizerOrServicer)
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
        // $commissioning = $this->product->product_logs()->where('what', 'commissioning')->first();

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
}
