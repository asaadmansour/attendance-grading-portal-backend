<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Branch Manager',
            'email' => 'bm@example.com',
            'role' => 'branch_manager',
        ]);

        User::factory()->create([
            'name' => 'Track Admin',
            'email' => 'trackadmin@example.com',
            'role' => 'track_admin',
        ]);

        User::factory()->create([
            'name' => 'Instructor',
            'email' => 'instructor@example.com',
            'role' => 'instructor',
        ]);

        User::factory()->create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'role' => 'student',
        ]);
        $this->call(TagSeeder::class);
    }
}
