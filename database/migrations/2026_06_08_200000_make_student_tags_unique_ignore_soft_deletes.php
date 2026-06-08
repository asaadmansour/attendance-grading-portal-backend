<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Replace the plain composite unique constraint with a partial unique
     * index so that soft-deleted rows no longer block re-tagging the same
     * (student, tag, course) combination.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE student_tags DROP CONSTRAINT student_tags_student_id_tag_id_course_id_unique');
        DB::statement('CREATE UNIQUE INDEX student_tags_active_unique ON student_tags (student_id, tag_id, course_id) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX student_tags_active_unique');
        DB::statement('ALTER TABLE student_tags ADD CONSTRAINT student_tags_student_id_tag_id_course_id_unique UNIQUE (student_id, tag_id, course_id)');
    }
};
