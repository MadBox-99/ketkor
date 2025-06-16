<?php

namespace App\Livewire;

use App\Models\Tool;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class ToolsTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'tools-table';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::exportable('export')
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Tool::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Név', 'name')
                ->sortable()
                ->searchable(),

            Column::add()
                ->title('Kategória')
                ->field('category', 'category'),

            Column::make('Tag', 'tag')
                ->sortable()
                ->searchable(),
            Column::add()
                ->title('Gyártó')
                ->field('factory_name', 'factory_name'),
            Column::action('Művelet'),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('name')->operators(['contains']),
            Filter::select('category', 'category')->dataSource(Tool::whereNot('category', '=', '')->get())->optionValue('category')->optionLabel('category'),
            Filter::inputText('tag')->operators(['contains']),

        ];
    }

    #[On('edit')]
    public function edit($rowId): void
    {
        $tool = Tool::find($rowId);
        $this->redirect(route('tools.edit', $tool->id), true);
    }

    public function actions(Tool $row): array
    {
        return [
            Button::add('edit')
                ->slot('Szerkesztés')
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
