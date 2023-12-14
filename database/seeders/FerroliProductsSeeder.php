<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\FerroliProductsImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FerroliProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Excel::import(new FerroliProductsImport, storage_path('app/import/Ferroli Garanciális táblázat.xls'));
    }
}
