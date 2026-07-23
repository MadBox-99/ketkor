<?php

declare(strict_types=1);

namespace App\Livewire\Organizations;

use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.app')]
class CreateEmployee extends Component
{
    #[Validate('required|unique:users,name')]
    public string $name = '';

    #[Validate('required|unique:users,email')]
    public string $email = '';

    #[Validate]
    public string $password = '';

    /**
     * @return array<string, array<int, Password|string>>
     */
    protected function rules(): array
    {
        return [
            'password' => ['required', Password::defaults()],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::beginTransaction();

        try {
            /** @var User $authUser */
            $authUser = Auth::user();

            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'organization_id' => $authUser->organization_id,
            ]);

            $user->assignRole('Servicer');

            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollback();

            session()->flash('error', $throwable->getMessage());

            return;
        }

        session()->flash('success', __('Successfully created an new employee.'));

        $this->redirectRoute('organizations.myorganization', navigate: true);
    }

    public function render(): Factory|View
    {
        return view('livewire.organizations.create-employee');
    }
}
