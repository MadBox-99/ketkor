<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Support\Str;
use App\Imports\ProductsImport;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Maatwebsite\Excel\Excel as ExcelExtension;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = ['Admin', 'Operator', 'Servicer', 'Organizer'];
        $permissions = [
            'products.list' => 'Admin|Operator',
            'products.create' => 'Admin|Operator',
            'products.update' => 'Admin|Operator|Servicer|organizer',
            'products.delete' => 'Admin|Operator',
            'organizations.list' => 'Admin|Operator',
            'organizations.create' => 'Admin|Operator',
            'organizations.update' => 'Admin|Operator|organizer',
            'organizations.delete' => 'Admin|Operator',
            'users.list' => 'Admin|Operator',
            'users.create' => 'Admin|Operator',
            'users.update' => 'Admin|Operator',
            'users.delete' => 'Admin|Operator',
            'tools.list' => 'Admin|Operator',
            'tools.create' => 'Admin|Operator',
            'tools.update' => 'Admin|Operator',
            'tools.delete' => 'Admin|Operator',
        ];

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }

        foreach ($permissions as $name => $roles) {
            $permission = Permission::create(['name' => $name]);
            $toRoles = explode('|', $roles);
            foreach ($toRoles as $toRole) {
                $permission->assignRole($toRole);
            }
        }
        $user1 = User::factory()->create([
            'name' => 'Test User',
            'email' => 'admin@admin.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'remember_token' => Str::random(10),
            'organization_id' => 1
        ]);
        $user1->assignRole('Organizer');
        $user2 = User::factory()->create([
            'name' => 'Test User 2',
            'email' => 'test@example2.com',
            'email_verified_at' => now(),
            'password' => Hash::make('1234'),
            'remember_token' => Str::random(10),
            'organization_id' => 1
        ]);
        Tool::factory()->create([
            'id' => '1',
            'name' => 'Brava Slim 25 BT',
            'category' => 'kazán',
            'tag' => 'Próba tag',
            'factory_name' => 'Sime',

        ]); /*
$product = Product::factory()->create([
'owner_name' => 'Proba Név',
'city' => 'Biatorbágy',
'street' => 'Géza fejedelem u. 6.',
'zip' => '2051',
'serial_number' => '4518300786',
'installation_date' => '2023-08-20',
'warrantee_date' => '2024-08-20',
'purchase_place' => 'Két Kör Kft.',
'comments' => 'HMV-t nem használják',
'tool_id' => '1',
]);
$product->users()->attach($user1->id);
$product->users()->attach($user2->id);
*/
        Excel::import(new ProductsImport, storage_path('app/import/SIME.xlsx'));
    }
}