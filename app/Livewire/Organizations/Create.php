<?php

declare(strict_types=1);

namespace App\Livewire\Organizations;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.app')]
class Create extends Component
{
    #[Validate('required|string')]
    public string $name = '';

    #[Validate('string')]
    public string $city = '';

    #[Validate('string')]
    public string $address = '';

    #[Validate('string')]
    public string $zip = '';

    #[Validate('required|max:24')]
    public string $tax_number = '';

    public function save(): void
    {
        $validated = $this->validate();

        DB::beginTransaction();

        try {
            $organization = Organization::query()->create($validated);

            /** @var User $user */
            $user = Auth::user();
            $user->organization_id = $organization->id;
            $user->save();

            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollback();

            session()->flash('error', $throwable->getMessage());

            return;
        }

        session()->flash('success', __('Organization created successfully.'));

        $this->redirectRoute('organizations.myorganization', navigate: true);
    }

    public function render(): Factory|View
    {
        return view('livewire.organizations.create');
    }
}
