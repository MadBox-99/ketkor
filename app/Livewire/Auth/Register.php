<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth.simple')]
class Register extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->placeholder(__('Full name'))
                    ->required()
                    ->maxLength(255)
                    ->autofocus(),

                TextInput::make('email')
                    ->label(__('Email address'))
                    ->placeholder('email@example.com')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(User::class),

                TextInput::make('password')
                    ->label(__('Password'))
                    ->placeholder(__('Password'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->rule(Password::defaults())
                    ->confirmed(),

                TextInput::make('password_confirmation')
                    ->label(__('Confirm password'))
                    ->placeholder(__('Confirm password'))
                    ->password()
                    ->revealable()
                    ->required(),
            ])
            ->statePath('data');
    }

    public function register(): void
    {
        $data = $this->form->getState();

        $data['password'] = Hash::make($data['password']);
        unset($data['password_confirmation']);

        event(new Registered(($user = User::query()->create($data))));

        Auth::login($user);

        $this->redirect(route('index', absolute: false), navigate: true);
    }
}
