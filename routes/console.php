<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Password is prompted interactively, never committed or passed as a command flag.
Artisan::command('marketing:admin {email} {--name=Marketing Manager}', function () {
    $email = $this->argument('email');
    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Invalid email address.');
        return 1;
    }

    $password = $this->secret('New password (at least 12 characters)');
    if (! is_string($password) || mb_strlen($password) < 12) {
        $this->error('Password must contain at least 12 characters.');
        return 1;
    }

    $user = User::firstOrNew(['email' => $email]);
    $user->name = $this->option('name');
    $user->password = $password; // hashed by the User model
    $user->is_marketing_admin = true;
    $user->save();

    $this->info('Marketing administrator created or updated: '.$email);
    return 0;
})->purpose('Create an authorized AI Hotel Marketing Assistant administrator');
