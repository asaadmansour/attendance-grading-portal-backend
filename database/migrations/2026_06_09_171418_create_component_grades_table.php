<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('component_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_component_id')->constrained();
            $table->foreignId('student_id')->constrained('users');
            $table->decimal('raw_score', 5, 2);
            $table->decimal('normalized_score', 5, 2);
            $table->foreignId('entered_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('component_grades');
    }
};
