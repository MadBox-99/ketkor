<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Footer;
use PowerComponents\LivewirePowerGrid\Header;
use PowerComponents\LivewirePowerGrid\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridColumns;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class ProductTable extends PowerGridComponent
{
    use WithExport;

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            Exportable::make('export')
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            Header::make()->showSearchInput(),
            Footer::make()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Product::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function addColumns(): PowerGridColumns
    {
        return PowerGrid::columns()
            ->addColumn('id')
            ->addColumn('owner_name')

            /** Example of custom column using a closure **/
            ->addColumn('owner_name_lower', fn(Product $model) => strtolower(e($model->owner_name)))

            ->addColumn('installer_name')
            ->addColumn('city')
            ->addColumn('street')
            ->addColumn('zip')
            ->addColumn('purchase_place')
            ->addColumn('serial_number')
            ->addColumn('purchase_date_formatted', fn(Product $model) => Carbon::parse($model->purchase_date)->format('d/m/Y'))
            ->addColumn('installation_date_formatted', fn(Product $model) => Carbon::parse($model->installation_date)->format('d/m/Y'))
            ->addColumn('warrantee_date_formatted', fn(Product $model) => Carbon::parse($model->warrantee_date)->format('d/m/Y'))
            ->addColumn('tool_id')
            ->addColumn('user_id')
            ->addColumn('created_at_formatted', fn(Product $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'))
            ->addColumn('created_at_formatted', fn(Product $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Id', 'id'),
            Column::make('Owner name', 'owner_name')
                ->sortable()
                ->searchable(),

            Column::make('Installer name', 'installer_name')
                ->sortable()
                ->searchable(),

            Column::make('City', 'city')
                ->sortable()
                ->searchable(),

            Column::make('Street', 'street')
                ->sortable()
                ->searchable(),

            Column::make('Zip', 'zip')
                ->sortable()
                ->searchable(),

            Column::make('Purchase place', 'purchase_place')
                ->sortable()
                ->searchable(),

            Column::make('Serial number', 'serial_number')
                ->sortable()
                ->searchable(),

            Column::make('Purchase date', 'purchase_date_formatted', 'purchase_date')
                ->sortable(),

            Column::make('Installation date', 'installation_date_formatted', 'installation_date')
                ->sortable(),

            Column::make('Warrantee date', 'warrantee_date_formatted', 'warrantee_date')
                ->sortable(),

            Column::make('Tool id', 'tool_id'),
            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->sortable(),
            Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('owner_name')->operators(['contains']),
            Filter::inputText('installer_name')->operators(['contains']),
            Filter::inputText('city')->operators(['contains']),
            Filter::inputText('street')->operators(['contains']),
            Filter::inputText('zip')->operators(['contains']),
            Filter::inputText('purchase_place')->operators(['contains']),
            Filter::inputText('serial_number')->operators(['contains']),
            Filter::datepicker('purchase_date'),
            Filter::datepicker('installation_date'),
            Filter::datepicker('warrantee_date'),
            Filter::datetimepicker('created_at'),
        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert(' . $rowId . ')');
    }

    public function actions(\App\Models\Product $row): array
    {
        return [
            Button::add('edit')
                ->slot('Edit: ' . $row->id)
                ->id()
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->dispatch('edit', ['rowId' => $row->id])
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
