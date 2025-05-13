<?php

namespace Database\Seeders;

use App\Imports\AccorroniProducts;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class AccorroniProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Excel::import(new AccorroniProducts, storage_path('app/import/ACCORRONI GHIBLI.xlsx'));
    }
}
