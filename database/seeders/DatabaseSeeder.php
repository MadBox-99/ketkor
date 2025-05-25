<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $permissions = [
            // Products
            'products.list' => [UserRole::Admin->value, UserRole::Operator->value],
            'products.create' => [UserRole::Admin->value, UserRole::Operator->value],
            'products.update' => [UserRole::Admin->value, UserRole::Operator->value, UserRole::Servicer->value, UserRole::Organizer->value],
            'products.delete' => [UserRole::Admin->value, UserRole::Operator->value],

            // Organizations
            'organizations.list' => [UserRole::Admin->value, UserRole::Operator->value],
            'organizations.create' => [UserRole::Admin->value, UserRole::Operator->value],
            'organizations.update' => [UserRole::Admin->value, UserRole::Operator->value, UserRole::Organizer->value],
            'organizations.delete' => [UserRole::Admin->value, UserRole::Operator->value],

            // Users
            'users.*' => [UserRole::Admin->value, UserRole::Operator->value],

            // Tools
            'tools.*' => [UserRole::Admin->value, UserRole::Operator->value],
        ];

        // Create roles
        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }

        // Create permissions and sync with roles
        foreach ($permissions as $name => $permissionRoles) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            $permission->syncRoles($permissionRoles);
        }

        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'zoli.szabok@gamil.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // password
            'remember_token' => Str::random(10),
        ]);
        $user1 = User::factory()->create([
            'name' => 'Test User',
            'email' => 'admin@admin.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // password
            'remember_token' => Str::random(10),
            'organization_id' => 1,
        ]);
        $user2 = User::factory()->create([
            'name' => 'Test User 2',
            'email' => 'test@test2.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // password
            'remember_token' => Str::random(10),
            'organization_id' => 1,
        ]);
        $user3 = User::factory()->create([
            'name' => 'Test User 3',
            'email' => 'test@test3.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // password
            'remember_token' => Str::random(10),
            'organization_id' => 1,
        ]);

        $admin->assignRole(UserRole::Admin);
        $user1->assignRole(UserRole::Operator);
        $user2->assignRole(UserRole::Organizer);
        $user3->assignRole(UserRole::Servicer);
        Tool::factory()->create([
            'id' => '1',
            'name' => 'Brava Slim 25 BT',
            'category' => 'kazán',
            'tag' => 'Próba tag',
            'factory_name' => 'Sime',

        ]);

        $this->call([
            /* SimeProductSeeder::class,
            AccorroniProductsSeeder::class,
            FerroliProductsSeeder::class,
            LEBTORProductsSeeder::class, */
        ]);

        $productTest1 = Product::whereId(1)->first();
        $productTest2 = Product::whereId(2)->first();
        $user1->products()->attach($productTest1);
        $user1->products()->attach($productTest2);
        $user2->products()->attach($productTest2);

    }
}
