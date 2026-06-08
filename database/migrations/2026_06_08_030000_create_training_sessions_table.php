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
        if (!Schema::hasTable('training_sessions')) {
            Schema::create('training_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('engagement_id')->nullable()->index();
                $table->date('session_date')->nullable();
                $table->decimal('scheduled_hours', 8, 2)->default(0);
                $table->decimal('delivered_hours', 8, 2)->default(0);
                $table->boolean('is_delivered')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_sessions');
    }
};
