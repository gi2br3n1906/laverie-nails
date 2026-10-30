<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;

class CreateAdminUser extends Command
{
    protected $signature = 'laverie:create-admin
                            {email : Admin email address}
                            {--name=Laverie Admin : Display name}
                            {--password= : Non-interactive password (minimum 12 characters)}';

    protected $description = 'Create the first admin account without exposing a seeder password in the repository';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Please provide a valid email address.');

            return self::FAILURE;
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->error("An account with {$email} already exists. Use the profile page to change its password.");

            return self::FAILURE;
        }

        $password = (string) $this->option('password');

        if ($password === '') {
            $password = (string) $this->secret('Password (minimum 12 characters)');
        }

        if (strlen($password) < 12) {
            $this->error('The password must be at least 12 characters.');

            return self::FAILURE;
        }

        $user = new User([
            'name' => (string) $this->option('name'),
            'email' => $email,
            'password' => $password,
            'roles' => [UserRole::Admin->value],
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $this->info("Admin account created for {$email}.");
        $this->line('Sign in at /login, then keep the password in your password manager.');

        return self::SUCCESS;
    }
}
