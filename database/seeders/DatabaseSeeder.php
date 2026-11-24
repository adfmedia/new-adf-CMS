<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;
    
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        //User::factory()->create([
        //    'name' => 'Test User',
        //    'email' => 'test@example.com',
        //]);

        $SuperAdminGroup = \Adm\Models\AdmUserGroup::firstOrCreate(
            ['title' => 'Super admin'],
            [
                'permissions'   => [],
                'super_admin'   => true,
                'active'        => true
            ]
        );

        // No admin with a fixed password any more: the installer (or
        // php artisan cms:admin:create) asks for one. For a local dev
        // database, set SEED_ADMIN_EMAIL and SEED_ADMIN_PASSWORD in .env
        // to have db:seed create one.
        $email = env('SEED_ADMIN_EMAIL');
        $password = env('SEED_ADMIN_PASSWORD');
        if ($email && $password && !\Adm\Models\AdmUser::where('email', $email)->exists()) {
            \Adm\Models\AdmUser::create([
                'name'              => env('SEED_ADMIN_NAME', 'adfmedia'),
                'email'             => $email,
                'password'          => Hash::make($password),
                'adm_user_group_id' => $SuperAdminGroup->id
            ]);
        }
    }
}
