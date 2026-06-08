<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excuse_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('attendances')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->text('reason');
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['requested', 'approved', 'rejected'])->default('requested');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('student_id');
            $table->index('attendance_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excuse_requests');
    }
};