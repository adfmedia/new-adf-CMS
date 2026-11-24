<?php

namespace App\Console\Commands;

use Adm\Models\AdmUser;
use Adm\Models\AdmUserGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class AdminCreate extends Command
{
    protected $signature = 'cms:admin:create
        {email : The admin\'s email}
        {--name= : The admin\'s name}';

    protected $description = 'Create a super admin, or reset an existing admin\'s password (asks for it)';

    public function handle(): int
    {
        $email = $this->argument('email');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('That isn\'t a valid email address.');
            return self::FAILURE;
        }

        $password = $this->secret('Password (at least 12 characters)');
        if (strlen((string) $password) < 12 || $password !== $this->secret('Password again')) {
            $this->error('The passwords didn\'t match, or were shorter than 12 characters.');
            return self::FAILURE;
        }

        $Group = AdmUserGroup::firstOrCreate(['title' => 'Super admin'], ['permissions' => [], 'super_admin' => true, 'active' => true]);
        $User = AdmUser::where('email', $email)->first();

        AdmUser::updateOrCreate(['email' => $email], array_filter([
            'name'              => $this->option('name') ?: ($User?->name ?? 'Admin'),
            'password'          => Hash::make($password),
            'adm_user_group_id' => $User?->adm_user_group_id ?? $Group->id,
        ]));

        $this->info($User ? 'Password reset for ' . $email . '.' : 'Super admin ' . $email . ' created.');
        return self::SUCCESS;
    }
}
