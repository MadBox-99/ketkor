<?php

namespace App\Imports;

use Illuminate\Database\Eloquent\Model;
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

class AccorroniProducts implements ToModel, WithHeadingRow
{

    /**
     * @return Model|null
     */
    public function model(array $row)
    {
        if (
            !isset($row['gyáriszám'])
            || !isset($row['beüzemelés ideje'])
            || is_null($row['beüzemelő szerviz'])
            || (!isset($row['beüzemelés ideje']) && !isset($row['vásárlás ideje']))
        ) {
            return null;
        }

        if ($row['beüzemelés ideje'] == '?') {
            $row['beüzemelés ideje'] = null;
        }

        if ($row['vásárlás ideje'] == '?') {
            $row['vásárlás ideje'] = null;
        }

        $row['beüzemelés ideje'] = Carbon::createFromDate(1900, 1, 1)->addDays($row['beüzemelés ideje'] - 2);
        $row['vásárlás ideje'] = Carbon::createFromDate(1900, 1, 1)->addDays($row['vásárlás ideje'] - 2);

        $install_date = Carbon::createFromInterface(new DateTime($row['beüzemelés ideje']));
        $purchase_date = Carbon::createFromInterface(new DateTime($row['vásárlás ideje']));
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

        $tool = Tool::firstOrCreate(['name' => $row['tipus'], 'factory_name' => 'Accorroni']);
        $product = Product::Create(
            [
                'tool_id' => $tool->id,
                'owner_name' => $row['név'] ?? null,
                'installer_name' => $row['beüzemelő szerviz'] ?? null,
                'user_id' => $user->id,
                'city' => $row['Város'] ?? null,
                'street' => $row['Utca'] ?? null,
                'zip' => $row['Ir.szám'] ?? null,
                'purchase_place' => $row['vásárlás helye'] ?? null,
                'purchase_date' => $row['vásárlás ideje'] ?? null,
                'installation_date' => $row['beüzemelés ideje'] ?? null,
                'serial_number' => $row['gyáriszám'] ?? null,
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
