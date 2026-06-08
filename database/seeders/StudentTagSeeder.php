<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tag;
use App\Models\Course;
use App\Models\StudentTag;
class StudentTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $student = User::where('role', 'student')->first();
        $assigner = User::where('role', 'instructor')->first();
        $tag = Tag::first();
        $course = Course::first();

        StudentTag::create([
            'student_id'  => $student->id,
            'tag_id'      => $tag->id,
            'course_id'   => $course->id,
            'assigned_by' => $assigner->id,
        ]);
    }
}
