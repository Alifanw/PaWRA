<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;

$user = User::firstOrCreate(
    ['email' => 'monitor@airpanas.local'],
    [
        'username' => 'monitor',
        'name' => 'Monitor',
        'full_name' => 'Monitor User',
        'password' => bcrypt('123123'),
        'email_verified_at' => now(),
    ]
);

$monitor_role = Role::where('name', 'monitor')->first();
if ($monitor_role) {
    $user->roles()->syncWithoutDetaching([$monitor_role->id]);
    echo "✅ Monitor user created: " . $user->email . "\n";
    echo "   Role: " . $user->roles->pluck('name')->implode(', ') . "\n";
} else {
    echo "❌ Monitor role not found\n";
}
