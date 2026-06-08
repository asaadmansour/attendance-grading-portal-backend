<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('attendance_reconciliations')) {
            Schema::create('attendance_reconciliations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('session_id')->constrained('training_sessions');
                $table->foreignId('student_id')->constrained('users');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_reconciliations');
    }
};
