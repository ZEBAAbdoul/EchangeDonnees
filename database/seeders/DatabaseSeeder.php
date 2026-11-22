<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\SessionFormation;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call(RoleSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(ParametresSiteSeeder::class);

        $this->call(EtatSeeder::class);
        $this->call(OrganismeSeeder::class);
                $this->call(UserSeeder::class);

        $this->call(AgentSeeder::class);

        
    }
}
