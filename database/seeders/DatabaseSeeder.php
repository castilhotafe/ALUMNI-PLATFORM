<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserAndProfilesSeeder::class,
            ProjectsEcosystemSeeder::class,
            ResearchEcosystemSeeder::class,
            GroupEcosystemSeeder::class,
            CommunityContentSeeder::class,
            CollaboratorsSeeders::class,
        ]);
    }
}
