<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// a student lands in a lab group through their enrollment; nullable because they're
// enrolled first and slotted into a group afterwards
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('enrollments', 'lab_group_id')) {
                $table->unsignedBigInteger('lab_group_id')->nullable()->after('cohort_id')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('lab_group_id');
        });
    }
};
