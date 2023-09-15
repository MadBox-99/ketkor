<?php

namespace App\Imports;

use DateTime;
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
            || (!isset($row['Beüzemelés dátuma']) && !isset($row['Vásárlás dátuma']))
        ) {
            return null;
        }
        $row['Beüzemelés dátuma'] = Carbon::createFromDate(1900, 1, 1)->addDays($row['Beüzemelés dátuma'] - 2);
        $row['Vásárlás dátuma'] = Carbon::createFromDate(1900, 1, 1)->addDays($row['Vásárlás dátuma'] - 2);
        $install_date = Carbon::createFromInterface(new DateTime($row['Beüzemelés dátuma']));
        $purchase_date = Carbon::createFromInterface(new DateTime($row['Vásárlás dátuma']));
        $warrantee = null;
        if ($purchase_date->diffInMonths($install_date) <= 3) {
            $warrantee = $purchase_date->copy()->addYear();
        } else {
            $warrantee = $install_date->copy()->addYear();
        }
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

        $tool = Tool::firstOrCreate(['name' => $row['Kazán típus'], 'factory_name' => 'Sime']);
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
                'warrantee_date' => $warrantee
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