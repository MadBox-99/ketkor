<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Tool;
use App\Models\User;
use App\Models\Partial;
use App\Models\Product;
use App\Models\Organization;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class ProductsImport implements ToModel, WithHeadingRow
{

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        if (
            !isset($row['Gyári szám'])
            || !isset($row['Beüzemelés dátuma'])
            || is_null($row['Beuzemelo'])
        ) {
            return null;
        }
        $row['Beüzemelés dátuma'] = Carbon::createFromDate(1900, 1, 1)->addDays($row['Beüzemelés dátuma'] - 2);
        $row['Vásárlás dátuma'] = Carbon::createFromDate(1900, 1, 1)->addDays($row['Vásárlás dátuma'] - 2);

        $user = User::where('name', $row['Beuzemelo'])->first();

        if (!$user) {
            $organization = Organization::firstOrCreate(['name' => $row['Beuzemelo']]);

            $user = User::create([
                'organization_id' => $organization->id,
                'name' => $row['Beuzemelo'],
            ]);
            $user->assignRole('Organizer');
        }
        /*if (is_null($organization))
            dd($row['Beuzemelo']);
        */

        $tool = Tool::firstOrCreate(['name' => $row['Kazán típus']]);
        $product = Product::Create(
            [
                'tool_id' => $tool->id,
                'owner_name' => $row['tulajdonos'],
                'installer_name' => $row['Beuzemelo'],
                'user_id' => $user->id,
                'city' => $row['varos'],
                'street' => $row['street'],
                'zip' => $row['zip'],
                'purchase_place' => $row['purchase_place'],
                'purchase_date' => $row['Vásárlás dátuma'],
                'installation_date' => $row['Beüzemelés dátuma'],
                'serial_number' => $row['Gyári szám'],
            ]
        );
        Partial::create(
            [
                'name' => $product->owner_name,
                'product_id' => $product->id
            ]
        );
        $product->users()->attach($user->id);
        return $product;
    }
}