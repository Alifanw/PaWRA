<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('username','admin')->first();
if($user){
    $user->password = Illuminate\Support\Facades\Hash::make('Admin123!');
    $user->save();
    echo "UPDATED admin id={$user->id}\n";
} else {
    echo "ADMIN_NOT_FOUND\n";
}
