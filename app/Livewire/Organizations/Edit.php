<?php

declare(strict_types=1);

namespace App\Livewire\Organizations;

use App\Models\Organization;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    public Organization $organization;

    #[Validate('required|string')]
    public string $name = '';

    #[Validate('nullable|string')]
    public string $city = '';

    #[Validate('nullable|string')]
    public string $address = '';

    #[Validate('nullable|string')]
    public string $zip = '';

    #[Validate('required|max:24')]
    public string $tax_number = '';

    public function mount(Organization $organization): void
    {
        $this->organization = $organization;
        $this->name = $organization->name;
        $this->city = (string) $organization->city;
        $this->address = (string) $organization->address;
        $this->zip = (string) $organization->zip;
        $this->tax_number = (string) $organization->tax_number;
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::beginTransaction();

        try {
            $this->organization->update($validated);
            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollback();

            session()->flash('error', $throwable->getMessage());

            return;
        }

        session()->flash('success', __('Organization updated successfully.'));

        $this->redirectRoute('organizations.index', navigate: true);
    }

    public function render(): Factory|View
    {
        return view('livewire.organizations.edit');
    }
}
