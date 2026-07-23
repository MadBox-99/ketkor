<?php

declare(strict_types=1);

namespace App\Livewire\Tools;

use App\Filament\Resources\Tools\Schemas\ToolFormSchema;
use App\Models\Tool;
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
        return ToolFormSchema::make($schema)->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        DB::beginTransaction();

        try {
            Tool::query()->create($data);
            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollback();

            session()->flash('error', $throwable->getMessage());

            return;
        }

        session()->flash('success', __('Tool created successfully.'));

        $this->redirectRoute('tools.index', navigate: true);
    }

    public function render(): Factory|View
    {
        return view('livewire.tools.create');
    }
}
