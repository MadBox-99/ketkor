<?php

declare(strict_types=1);

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Enums\ProductCategory;
use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
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
        // Create roles
        foreach (UserRole::cases() as $role) {
            Role::query()->firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }

        // Generate the Filament Shield permissions for every admin-panel entity,
        // then grant the Super Admin role the full set. Finer-grained access for
        // the other roles is managed through Shield's role manager in the panel.
        Artisan::call('shield:generate', [
            '--all' => true,
            '--panel' => 'admin',
            '--option' => 'permissions',
            '--no-interaction' => true,
        ]);

        Role::findByName(UserRole::SuperAdmin->value, 'web')
            ->givePermissionTo(Permission::query()->get());
        $organization = Organization::factory()->createOne([
            'id' => 1,
            'name' => 'Default Organization',
            'address' => '123 Main St',
            'zip' => '12345',
            'tax_number' => '123456789',
        ]);
        $admin = User::factory()->createOne([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // password
            'remember_token' => Str::random(10),
            'organization_id' => $organization->id,
        ]);
        $user1 = User::factory()->createOne([
            'name' => 'Test User',
            'email' => 'admin@operator.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // password
            'remember_token' => Str::random(10),
            'organization_id' => $organization->id,
        ]);

        $user2 = User::factory()->createOne([
            'name' => 'Test User 2',
            'email' => 'test@test2.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // password
            'remember_token' => Str::random(10),
            'organization_id' => $organization->id,
        ]);
        $user3 = User::factory()->createOne([
            'name' => 'Test User 3',
            'email' => 'test@test3.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // password
            'remember_token' => Str::random(10),
            'organization_id' => $organization->id,
        ]);

        // Assign roles - Admin and SuperAdmin get both roles for full access
        $admin->assignRole([UserRole::Admin, UserRole::SuperAdmin]);
        $user1->assignRole([UserRole::Admin, UserRole::Operator, UserRole::SuperAdmin]);
        $user2->assignRole(UserRole::Organizer);
        $user3->assignRole(UserRole::Servicer);
        Tool::factory()->createOne([
            'id' => '1',
            'name' => 'Brava Slim 25 BT',
            'category' => ProductCategory::SIME,
            'tag' => 'Próba tag',
            'factory_name' => 'Sime',
        ]);

        Product::factory(10)->create();
        $productTest1 = Product::whereId(1)->first();
        $productTest2 = Product::whereId(2)->first();
        $user1->products()->attach($productTest1);
        $user1->products()->attach($productTest2);
        $user2->products()->attach($productTest2);
    }
}
