<?php

declare(strict_types=1);

namespace App\Livewire\Organizations;

use App\Filament\Resources\Organizations\Schemas\OrganizationFormSchema;
use App\Models\Organization;
use App\Models\User;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.app')]
class Create extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
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
            $organization = Organization::query()->create($data);

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
