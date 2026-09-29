<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * The only seed data is the demo team from DemoDataSeeder, whose users all
     * share a known password, so it is refused outside local development.
     * Real accounts are created through registration or an invitation.
     */
    public function run(): void
    {
        $this->call(DemoDataSeeder::class);
    }
}
