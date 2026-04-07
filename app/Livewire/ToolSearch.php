<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Tool;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ToolSearch extends Component
{
    use WithPagination;

    public $tool_name = '';

    public function render(): Factory|View
    {
        $tools = Tool::query()
            ->when($this->tool_name, fn ($query) => $query->where('name', 'LIKE', '%' . $this->tool_name . '%'))
            ->paginate(20);

        return view('livewire.tool-search', ['tools' => $tools]);
    }
}
