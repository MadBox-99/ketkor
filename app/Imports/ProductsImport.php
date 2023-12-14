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
     *  'owner_name',
     *  'installer_name',
     *  'city',
     *  'street',
     *  'zip',
     *  'purchase_place',
     *  'serial_number',
     *  'purchase_date',
     *  'installation_date',
     *  'warrantee_date',
     *  'tool_id',
     *  'user_id',
     *  'comments',
     *  'created_at',
     */
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        if (
            !isset($row['Gyári szám'])
            || is_null($row['Beüzemelő szerviz'])
            || (!isset($row['Beüzemelés dátuma']) && !isset($row['Vásárlás dátuma']))
        ) {
            return null;
        }
        try {
            if ($row['Beüzemelés dátuma'] != '?')
                $row['Beüzemelés dátuma'] = Carbon::createFromDate(1900, 1, 1)->addDays($row['Beüzemelés dátuma'] - 2);
        } catch (\Throwable $th) {
            echo $th;
        }
        try {
            if ($row['Vásárlás dátuma'] !== '?')
                $row['Vásárlás dátuma'] = Carbon::createFromDate(1900, 1, 1)->addDays($row['Vásárlás dátuma'] - 2);
        } catch (\Throwable $th) {
            echo $th;
        }

        if ($row['Beüzemelés dátuma'] == '?')
            $row['Beüzemelés dátuma'] = null;
        if ($row['Vásárlás dátuma'] == '?')
            $row['Vásárlás dátuma'] = null;


        $install_date = Carbon::createFromInterface(new DateTime($row['Beüzemelés dátuma']));
        $purchase_date = Carbon::createFromInterface(new DateTime($row['Vásárlás dátuma']));
        $warrantee = null;
        if ($purchase_date->diffInMonths($install_date) <= 3) {
            $warrantee = $purchase_date->copy()->addYear();
        } else {
            $warrantee = $install_date->copy()->addYear();
        }
        $user = User::where('name', $row['Beüzemelő szerviz'])->first();

        if (!$user) {
            $organization = Organization::firstOrCreate(['name' => $row['Beüzemelő szerviz']]);

            $user = User::create([
                'organization_id' => $organization->id,
                'name' => $row['Beüzemelő szerviz'],
            ]);
            $user->assignRole('Organizer');
        }
        /*if (is_null($organization))
            dd($row['Beüzemelő szerviz']);
        */

        $tool = Tool::firstOrCreate(['name' => $row['Kazán típus'], 'factory_name' => 'Sime']);
        $product = Product::Create(
            [
                'tool_id' => $tool->id ?? 1,
                'owner_name' => $row['Tulajdonos/bérlő'] ?? null,
                'installer_name' => $row['Beüzemelő szerviz'] ?? null,
                'user_id' => $user->id,
                'city' => $row['Város'] ?? null,
                'street' => $row['Beépítés helye'] ?? null,
                'zip' => $row['Ir.szám'] ?? null,
                'purchase_place' => $row['Vásárlás helye'] ?? null,
                'purchase_date' => $row['Vásárlás dátuma'] ?? null,
                'installation_date' => $row['Beüzemelés dátuma'] ?? null,
                'serial_number' => $row['Gyári szám'] ?? null,
                'warrantee_date' => $warrantee ?? null
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
