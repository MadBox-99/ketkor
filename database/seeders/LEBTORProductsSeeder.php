<?php

namespace Database\Seeders;

use App\Imports\LEBTORImport;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class LEBTORProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Excel::import(new LEBTORImport, storage_path('app/import/lebtor kazán beüzemelések.xlsx'));
    }
}
