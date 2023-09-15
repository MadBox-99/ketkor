<?php

namespace App\Livewire;


use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class ToolSearch extends Component
{
    public $tool_name = '';
    use WithPagination;
    public function render()
    {
        $tools = DB::table('tools')->when($this->tool_name, function ($query) {
            return $query->where('name', 'LIKE', '%' . $this->tool_name . '%');
        })->paginate(20);
        return view('livewire.tool-search', compact('tools'));
    }
}