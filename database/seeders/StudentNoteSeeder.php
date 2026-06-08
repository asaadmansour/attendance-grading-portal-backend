<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\StudentNote;
class StudentNoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $student = User::where('role', 'student')->first();
        $author  = User::where('role', 'instructor')->first();

        StudentNote::create([
            'student_id' => $student->id,
            'author_id'  => $author->id,
            'body'       => 'Submits work late but quality is strong.',
        ]);
    }
}
