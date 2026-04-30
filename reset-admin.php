<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Reset admin password to 123456
$admin = User::where('email', 'admin@kip.id')->first();

if ($admin) {
    echo "=== Admin User Found ===\n";
    echo "Name: " . $admin->name . "\n";
    echo "Email: " . $admin->email . "\n";
    echo "Role: " . $admin->role . "\n";
    echo "Is Active: " . ($admin->is_active ? 'Yes' : 'No') . "\n";
    echo "Current Password Hash: " . substr($admin->password, 0, 30) . "...\n\n";
    
    // Reset password to 123456
    $admin->password = Hash::make('123456');
    $admin->is_active = true;
    $admin->role = 'Admin';
    $admin->save();
    
    // Reload user to verify
    $admin->refresh();
    
    echo "=== Password Reset ===\n";
    $verify = Hash::check('123456', $admin->password);
    echo "Password verification for '123456': " . ($verify ? 'SUCCESS' : 'FAILED') . "\n\n";
    
    echo "=== Ready to Login ===\n";
    echo "Email: admin@kip.id\n";
    echo "Password: 123456\n";
    echo "Expected Role: Admin\n";
    echo "Expected Dashboard: /admin (Admin Dashboard)\n";
} else {
    echo "ERROR: Admin user not found in database!\n";
    echo "Please run: php artisan db:seed --class=DatabaseSeeder\n";
}

echo "\n=== All Users ===\n";
$users = User::all();
foreach ($users as $user) {
    echo "- " . $user->email . " | Role: " . $user->role . " | Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
}
