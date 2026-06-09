<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// a person's billable line for one cohort: delivered hours, the rate applied, and
// the total handed to accounting (rate x hours, plus salary for internal staff)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cohort_id')->constrained('cohorts')->cascadeOnDelete();
            $table->decimal('total_delivered_hours', 8, 2)->default(0);
            $table->decimal('hourly_rate', 8, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('instructor_type');           // external | internal
            $table->string('status')->default('pending'); // pending | forwarded
            $table->timestamps();

            // one running line per person per cohort, so a re-run updates in place
            $table->unique(['user_id', 'cohort_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_records');
    }
};
