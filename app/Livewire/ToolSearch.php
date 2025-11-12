<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Tool;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ToolSearch extends Component
{
    public $tool_name = '';

    public Collection $tools;

    use WithPagination;

    public function mount(): void
    {
        $this->tools = Tool::query()->when($this->tool_name, fn ($query) => $query->where('name', 'LIKE', '%' . $this->tool_name . '%'))->paginate(20);
    }

    public function render(): Factory|View
    {
        return view('livewire.tool-search');
    }
}
