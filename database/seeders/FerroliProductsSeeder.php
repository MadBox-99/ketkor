<?php

namespace Database\Seeders;

use App\Imports\FerroliProductsImport;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class FerroliProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Excel::import(new FerroliProductsImport, storage_path('app/import/ferroli_gar.xls'));
    }
}
