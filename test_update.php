<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Http\Requests\Admin\StoreUserRequest;

$user = User::find(1);
echo "User found: " . $user->name . "\n";
echo "User role: " . $user->role . "\n";
echo "User is_active: " . ($user->is_active ? '1' : '0') . "\n\n";

// Simulate form data
$formData = [
    'employee_code' => $user->employee_code,
    'name' => $user->name,
    'nickname' => $user->nickname,
    'whatsapp' => $user->whatsapp,
    'email' => $user->email,
    'role' => 'Trainer Junior',
    'is_active' => '1',
    'password' => '',
];

// Create a mock request
$request = Request::create('/admin/users/1', 'PUT', $formData);
$request->setMethod('PUT');

// Create the form request
$storeRequest = new StoreUserRequest();
$storeRequest->setContainer($app)->setRedirector($app->make('redirect'));

// Set the user ID for the route
$storeRequest->setRoute($user);

// Validate
$validator = $app->make(Factory::class)->make($formData, (new StoreUserRequest())->rules());
$validator->sometimes('email', 'unique:users,email,' . $user->id, function() { return true; });

if ($validator->fails()) {
    echo "Validation FAILED:\n";
    foreach ($validator->errors()->all() as $error) {
        echo "  - $error\n";
    }
} else {
    echo "Validation PASSED\n";
}

// Also test directly via the controller
echo "\n--- Testing via controller ---\n";
try {
    $controller = new \App\Http\Controllers\Admin\AdminUserController();
    $request = Request::create('/admin/users/1', 'PUT', $formData);
    $request->setMethod('PUT');
    
    // Manually set the route parameter
    $request->attributes->set('route', \Illuminate\Routing\Route::class);
    
    $response = $controller->update($request, $user);
    echo "Response: " . $response->getContent() . "\n";
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
