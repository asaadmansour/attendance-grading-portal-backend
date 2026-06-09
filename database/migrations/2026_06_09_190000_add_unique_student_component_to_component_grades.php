<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A student has exactly one grade per course component.
     */
    public function up(): void
    {
        Schema::table('component_grades', function (Blueprint $table) {
            $table->unique(['student_id', 'course_component_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('component_grades', function (Blueprint $table) {
            $table->dropUnique(['student_id', 'course_component_id']);
        });
    }
};
