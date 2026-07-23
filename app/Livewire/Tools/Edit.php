<?php

declare(strict_types=1);

namespace App\Livewire\Tools;

use App\Models\Tool;
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
    public Tool $tool;

    #[Validate('required|string')]
    public string $name = '';

    #[Validate('nullable|string')]
    public ?string $category = null;

    #[Validate('nullable|string')]
    public ?string $tag = null;

    #[Validate('nullable|string')]
    public ?string $factory_name = null;

    public function mount(Tool $tool): void
    {
        $this->tool = $tool;
        $this->name = $tool->name;
        $this->category = $tool->category?->value;
        $this->tag = $tool->tag;
        $this->factory_name = $tool->factory_name;
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::beginTransaction();

        try {
            $this->tool->update($validated);
            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollback();

            session()->flash('error', $throwable->getMessage());

            return;
        }

        session()->flash('success', __('Tool updated successfully.'));

        $this->redirectRoute('tools.index', navigate: true);
    }

    public function render(): Factory|View
    {
        return view('livewire.tools.edit');
    }
}
