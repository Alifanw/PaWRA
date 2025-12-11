<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('username','superadmin')->first();
if($user){
    $user->password = Illuminate\Support\Facades\Hash::make('Admin123!');
    $user->save();
    echo "UPDATED\n";
} else {
    echo "USER_NOT_FOUND\n";
}
