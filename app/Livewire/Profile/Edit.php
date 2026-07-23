<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    #[Validate]
    public string $name = '';

    #[Validate]
    public string $email = '';

    public string $password = '';

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        /** @var User $user */
        $user = Auth::user();

        return [
            'name' => ['string', 'max:255'],
            'email' => ['email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::beginTransaction();

        try {
            /** @var User $user */
            $user = Auth::user();
            $user->fill($validated);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollback();

            session()->flash('error', $throwable->getMessage());

            return;
        }

        session()->flash('status', __('Profile updated successfully.'));
    }

    public function destroy(): void
    {
        $this->validate([
            'password' => ['required', 'current_password'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        Auth::logout();

        $user->delete();

        session()->invalidate();
        session()->regenerateToken();

        $this->redirect('/');
    }

    public function render(): Factory|View
    {
        return view('livewire.profile.edit', [
            'user' => Auth::user(),
        ]);
    }
}
