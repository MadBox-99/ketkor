<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Organization;
use Livewire\WithPagination;

class OrganizationSearchAdmin extends Component
{
    use WithPagination;
    public string $organization_name = '';
    public string $city = '';
    /**
     *
     * .cs ignore
     */
    public function render()
    {
        $organizations = Organization::when($this->organization_name, function ($query) {
            return $query->where('name', 'like', '%' . $this->organization_name . '%');
        })->when($this->city, function ($query) {
            return $query->where('city', 'like', '%' . $this->city . '%');
        })->paginate(15);
        return view('livewire.organization-search-admin', compact('organizations'));
    }
}
