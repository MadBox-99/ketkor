<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Imports\AccorroniProducts;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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
