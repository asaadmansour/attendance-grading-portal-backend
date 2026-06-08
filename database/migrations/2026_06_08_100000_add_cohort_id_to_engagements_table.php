<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Shahd's attendance flow links a training session's engagement to a cohort; Sameh's
// engagements table (instructor window) had no cohort_id, so add it here, nullable.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('engagements', function (Blueprint $table) {
            if (! Schema::hasColumn('engagements', 'cohort_id')) {
                $table->unsignedBigInteger('cohort_id')->nullable()->after('instructor_id')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('engagements', function (Blueprint $table) {
            $table->dropColumn('cohort_id');
        });
    }
};
