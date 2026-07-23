<?php

declare(strict_types=1);

namespace App\Livewire\Organizations;

use App\Filament\Resources\Organizations\Schemas\OrganizationFormSchema;
use App\Models\Organization;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.app')]
class Edit extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public Organization $organization;

    public ?array $data = [];

    public function mount(Organization $organization): void
    {
        $this->organization = $organization;

        $this->form->fill([
            'name' => $organization->name,
            'city' => $organization->city,
            'address' => $organization->address,
            'zip' => $organization->zip,
            'tax_number' => $organization->tax_number,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return OrganizationFormSchema::make($schema)->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        DB::beginTransaction();

        try {
            $this->organization->update($data);
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
