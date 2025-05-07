<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use App\Models\Organization;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridColumns;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class OrganizationDetailsUsersTable extends PowerGridComponent
{
    use WithExport;

    public $organization;

    public string $tableName = 'organization-details-users-table';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::exportable('export')
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            PowerGrid::header()->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $organization = Organization::find($this->organization)->id;

        return Product::whereHas('users.organization', function ($query) use ($organization): void {
            $query->where('id', $organization);
        });

    }

    public function relationSearch(): array
    {
        return ['product' => ['id', '']];
    }

    public function addColumns(): PowerGridColumns
    {
        return PowerGrid::columns()
            ->addColumn('id')
            ->addColumn('serial_number')
            ->addColumn('city')
            ->addColumn('tool_name')
            ->addColumn('warrantee_date_formatted', fn (Product $model) => Carbon::parse($model->warrantee_date)->format('Y-m-d'));
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Serial Number', 'serial_number')
                ->sortable()
                ->searchable(),
            Column::make('City', 'city')
                ->sortable()
                ->searchable(),
            Column::make('Tool name', 'tool_name', 'tools.name')
                ->sortable()
                ->searchable(),
            Column::make('Warrantee date', 'warrantee_date_formatted', 'warrantee_date')
                ->sortable(),
            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('serial_number')->operators(['contains']),
            Filter::inputText('city')->operators(['contains']),
            Filter::datepicker('purchase_date'),
            Filter::datepicker('installation_date'),
            Filter::inputText('tool_name')->operators(['contains']),
            Filter::datepicker('warrantee_date'),
        ];
    }

    #[On('edit')]
    public function edit(string $rowId): void
    {
        $this->js('alert('.$rowId.')');
    }

    public function actions(Product $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit: '.$row->id)
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('edit', ['rowId' => $row->id]),
        ];
    }

    /*
    public function actionRules($row): array
    {
       return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}
