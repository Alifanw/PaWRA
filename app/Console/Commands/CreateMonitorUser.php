<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class CreateMonitorUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-monitor-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create monitor user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
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

        $monitor_role = Role::where('name', 'monitoring')->first();
        if ($monitor_role) {
            $user->roles()->syncWithoutDetaching([$monitor_role->id]);
            $this->info("✅ Monitor user created: " . $user->email);
            $this->info("   Role: " . $user->roles->pluck('name')->implode(', '));
        } else {
            $this->error("❌ Monitor role not found");
        }
    }
}
