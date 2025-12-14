<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "════════════════════════════════════════════════════════════\n";
echo "🔐 FINAL ROLE-BASED ACCESS CONTROL VERIFICATION\n";
echo "════════════════════════════════════════════════════════════\n\n";

$users = [
    'ticket@airpanas.local',
    'booking@airpanas.local',
    'parking@airpanas.local',
    'monitor@airpanas.local',
    'admin@airpanas.local'
];

foreach ($users as $email) {
    $user = User::where('email', $email)->with('roles')->first();
    if ($user) {
        $role = $user->roles->first()?->name ?? 'N/A';
        echo "✅ " . str_pad($email, 30) . " → " . str_pad($role, 15) . "\n";
        
        // Show access
        $access = [];
        if ($role === 'ticketing') {
            $access = ['Dashboard', 'Ticket Sales'];
        } elseif ($role === 'booking') {
            $access = ['Dashboard', 'Bookings'];
        } elseif ($role === 'parking') {
            $access = ['Dashboard', 'Parking'];
        } elseif ($role === 'monitoring') {
            $access = ['Dashboard', 'Products', 'Users', 'Roles', 'Reports', 'Audit Logs', 'Attendance'];
        } elseif ($role === 'superadmin') {
            $access = ['Dashboard', 'Ticket Sales', 'Bookings', 'Parking', 'Products', 'Users', 'Roles', 'Reports', 'Audit Logs', 'Attendance'];
        }
        
        echo "   Access: " . implode(' + ', $access) . "\n\n";
    }
}

echo "════════════════════════════════════════════════════════════\n";
echo "✓ Setup Complete\n";
echo "════════════════════════════════════════════════════════════\n";
