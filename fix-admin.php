<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Check if admin exists
$admin = User::where('email', 'admin@kip.id')->first();

if ($admin) {
    echo "Admin user found:\n";
    echo "Name: " . $admin->name . "\n";
    echo "Email: " . $admin->email . "\n";
    echo "Role: " . $admin->role . "\n";
    echo "Is Active: " . ($admin->is_active ? 'Yes' : 'No') . "\n\n";
    
    // Ensure admin is active and has correct role
    $admin->is_active = true;
    $admin->role = 'Admin';
    $admin->save();
    
    echo "Admin user updated successfully!\n";
    echo "Password hash: " . substr($admin->password, 0, 20) . "...\n";
} else {
    echo "No admin user found. Creating new admin...\n";
    
    $admin = User::create([
        'employee_code' => 'ADM001',
        'name'          => 'Admin KIP',
        'nickname'      => 'Admin',
        'email'         => 'admin@kip.id',
        'password'      => Hash::make('123456'),
        'role'          => 'Admin',
        'is_active'     => true,
    ]);
    
    echo "Admin user created with ID: " . $admin->id . "\n";
}

// List all users
echo "\n--- All Users ---\n";
$users = User::all();
foreach ($users as $user) {
    echo "- " . $user->email . " | Role: " . $user->role . " | Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
}
