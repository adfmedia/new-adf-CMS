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

        $SuperAdminGroup = \Adm\Models\AdmUserGroup::create([
            'title'         => 'Super admin',
            'permissions'   => [],
            'super_admin'   => true,
            'active'        => true
        ]);

        \Adm\Models\AdmUser::create([
            'name'              => 'Bespoke Developer',
            'email'             => 'developer@bespokedigital.agency',
            'password'          => Hash::make('!Staff0nly!'),
            'adm_user_group_id' => $SuperAdminGroup->id
        ]);
    }
}
