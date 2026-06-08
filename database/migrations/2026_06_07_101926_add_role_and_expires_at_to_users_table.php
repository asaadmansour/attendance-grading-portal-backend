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
        Schema::table('users', function (Blueprint $table) {
            // add columns only if they don't already exist (safe for repeated runs)
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('student')->after('name');
            }

            if (! Schema::hasColumn('users', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('password');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
            if (Schema::hasColumn('users', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });
    }
};
