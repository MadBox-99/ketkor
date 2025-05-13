<?php

namespace Database\Seeders;

use App\Imports\ProductsImport;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class SimeProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Excel::import(new ProductsImport, storage_path('app/import/SIME garancialis tablazat.xls'));
    }
}
