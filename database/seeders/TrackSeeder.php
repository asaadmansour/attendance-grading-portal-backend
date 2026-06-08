<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Seeder;

class TrackSeeder extends Seeder
{
    public function run(): void
    {
        $manager = User::where('role', 'branch_manager')->first();

        $branch = Branch::firstOrCreate(
            ['name' => 'Cairo Branch'],
            ['manager_id' => $manager?->id],
        );

        foreach (['Web Development', 'Mobile Development'] as $name) {
            Track::firstOrCreate(['branch_id' => $branch->id, 'name' => $name]);
        }
    }
}
