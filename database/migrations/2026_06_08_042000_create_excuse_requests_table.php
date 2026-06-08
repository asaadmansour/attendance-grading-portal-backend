<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('excuse_requests')) {
            Schema::create('excuse_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('attendance_id')->constrained('attendances');
                $table->foreignId('student_id')->constrained('users');
                $table->text('reason')->nullable();
                $table->string('status')->default('pending');
                $table->foreignId('reviewed_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('excuse_requests');
    }
};
