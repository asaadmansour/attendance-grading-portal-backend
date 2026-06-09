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
        Schema::create('grade_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_grade_id')->constrained();
            $table->decimal('original_value',5,2);
            $table->decimal('new_value',5,2);
            $table->text('note');
            $table->foreignId('overridden_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_overrides');
    }
};
