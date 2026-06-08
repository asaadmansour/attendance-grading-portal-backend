<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('engagements')) {
            Schema::create('engagements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cohort_id')->nullable();
                $table->foreignId('instructor_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('engagements');
    }
};
