<?php

namespace App\Livewire;

use App\Models\Tool;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ToolSearch extends Component
{
    public $tool_name = '';

    public Collection $tools;

    use WithPagination;

    public function mount()
    {
        $this->tools = Tool::when($this->tool_name, function ($query) {
            return $query->where('name', 'LIKE', '%'.$this->tool_name.'%');
        })->paginate(20);
    }

    public function render()
    {
        return view('livewire.tool-search');
    }
}
