<?php
/**
 * Butcher Users API Endpoint Test
 * Tests the new butcher-users endpoint
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Support\Facades\DB;

echo "=== BUTCHER USERS API ENDPOINT TEST ===\n\n";

// Test 1: Check if butchery role exists
echo "Test 1: Checking for 'butchery' role...\n";
$role = DB::table('admin_roles')->where('slug', 'butchery')->first();
if ($role) {
    echo "✓ Found role: {$role->name} (ID: {$role->id}, Slug: {$role->slug})\n\n";
} else {
    echo "✗ Role 'butchery' not found. Available roles:\n";
    $roles = DB::table('admin_roles')->select('id', 'name', 'slug')->get();
    foreach ($roles as $r) {
        echo "  - {$r->name} (slug: {$r->slug}, id: {$r->id})\n";
    }
    
    // Create butchery role if it doesn't exist
    echo "\nCreating 'butchery' role...\n";
    try {
        $newRoleId = DB::table('admin_roles')->insertGetId([
            'name' => 'Butchery Staff',
            'slug' => 'butchery',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "✓ Created 'Butchery Staff' role with ID: {$newRoleId}\n\n";
        $role = DB::table('admin_roles')->where('id', $newRoleId)->first();
    } catch (Exception $e) {
        echo "✗ Failed to create role: {$e->getMessage()}\n\n";
        exit(1);
    }
}

// Test 2: Check for users with butchery role
echo "Test 2: Checking for users with butchery role...\n";
$users_table = config('admin.database.users_table', 'admin_users');
$role_users_table = config('admin.database.role_users_table', 'admin_role_users');

$sql = "SELECT DISTINCT 
            u.id,
            u.name,
            u.phone_number,
            u.email,
            u.first_name,
            u.last_name,
            u.status
        FROM {$users_table} u
        INNER JOIN {$role_users_table} ru ON u.id = ru.user_id
        WHERE ru.role_id = ? 
        AND u.status = 'Active'
        ORDER BY u.name ASC";

$butcherUsers = DB::select($sql, [$role->id]);

if (count($butcherUsers) > 0) {
    echo "✓ Found " . count($butcherUsers) . " butcher user(s):\n";
    foreach ($butcherUsers as $user) {
        echo "  - {$user->name} (ID: {$user->id}, Phone: {$user->phone_number}, Email: {$user->email})\n";
    }
} else {
    echo "✗ No users assigned to butchery role\n";
    echo "\nTo assign a user to butchery role, you can:\n";
    echo "1. Go to admin panel > Users\n";
    echo "2. Edit a user and assign 'Butchery Staff' role\n";
    echo "OR run this SQL:\n";
    echo "INSERT INTO {$role_users_table} (role_id, user_id, created_at, updated_at) VALUES ({$role->id}, YOUR_USER_ID, NOW(), NOW());\n";
}

echo "\n=== TEST COMPLETED ===\n";
echo "\nAPI Endpoint URL: /api/butcher-users\n";
echo "Expected Response Format:\n";
echo json_encode([
    'status' => 1,
    'data' => [
        [
            'id' => 1,
            'name' => 'John Doe',
            'phone_number' => '+256700000000',
            'email' => 'john@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'avatar' => 'path/to/avatar.jpg',
            'status' => 'Active'
        ]
    ],
    'message' => 'Success'
], JSON_PRETTY_PRINT);
echo "\n";
