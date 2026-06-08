<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cohorts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained('tracks')->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // one active cohort per track (partial unique index — works on pg + sqlite)
        DB::statement(
            "CREATE UNIQUE INDEX cohorts_one_active_per_track ON cohorts (track_id) WHERE status = 'active'"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('cohorts');
    }
};
