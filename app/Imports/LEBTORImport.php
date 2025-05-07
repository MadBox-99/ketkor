<?php

namespace App\Imports;

use Illuminate\Database\Eloquent\Model;
use DateTime;
use App\Models\Tool;
use App\Models\User;
use App\Models\Partial;
use App\Models\Product;
use App\Models\Organization;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LEBTORImport implements ToModel, WithHeadingRow
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
     * @return Model|null
     */
    public function model(array $row)
    {
        if (
            !isset($row['Gyári szám'])
            || !isset($row['Beüzemelés dátuma'])
            || is_null($row['beüzemelő szerviz'])
            || (!isset($row['Beüzemelés dátuma']) && !isset($row['Vásárlás dátuma']))
        ) {
            return null;
        }

        if ($row['Beüzemelés dátuma'] == '?') {
            $row['Beüzemelés dátuma'] = null;
        }

        if ($row['Vásárlás dátuma'] == '?') {
            $row['Vásárlás dátuma'] = null;
        }

        if ($row['Beüzemelés dátuma'] !== '?') {
            if (is_numeric(['Beüzemelés dátuma'])) {
                $row['Beüzemelés dátuma'] = Carbon::createFromDate(1900, 1, 1)->addDays($row['Beüzemelés dátuma'] - 2);
            } else {
                $row['Beüzemelés dátuma'] = null;
            }
        }

        if ($row['Vásárlás dátuma'] !== '?') {
            if (is_numeric(['Vásárlás dátuma'])) {
                $row['Vásárlás dátuma'] = Carbon::createFromDate(1900, 1, 1)->addDays($row['Vásárlás dátuma'] - 2);
            } else {
                $row['Vásárlás dátuma'] = null;
            }
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

        $user = User::where('name', $row['beüzemelő szerviz'])->first();

        if (!$user) {
            $organization = Organization::firstOrCreate(['name' => $row['beüzemelő szerviz']]);

            $user = User::firstOrCreate([
                'organization_id' => $organization->id,
                'name' => $row['beüzemelő szerviz'],
            ]);
            $user->assignRole('Organizer');
        }

        $tool = Tool::firstOrCreate(['name' => $row['Típus:'], 'factory_name' => 'Ferroli']);
        $product = Product::Create(
            [
                'tool_id' => $tool->id,
                'owner_name' => $row['név'] ?? null,
                'installer_name' => $row['beüzemelő szerviz'] ?? null,
                'user_id' => $user->id,
                'city' => $row['Város'] ?? null,
                'street' => $row['Cím:'] ?? null,
                'zip' => $row['Ir szám'] ?? null,
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
