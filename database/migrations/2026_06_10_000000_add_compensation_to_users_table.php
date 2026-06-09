<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// compensation lives on the person: external instructors are paid per delivered hour,
// internal track admins get a fixed salary on top of their delivered hours
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'compensation_type')) {
                $table->string('compensation_type')->nullable()->after('role');
            }
            if (! Schema::hasColumn('users', 'hourly_rate')) {
                $table->decimal('hourly_rate', 8, 2)->nullable()->after('compensation_type');
            }
            if (! Schema::hasColumn('users', 'monthly_salary')) {
                $table->decimal('monthly_salary', 10, 2)->nullable()->after('hourly_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['compensation_type', 'hourly_rate', 'monthly_salary'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
