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
        // Remove case_id dari criteria table
        if (Schema::hasColumn('criteria', 'case_id')) {
            Schema::table('criteria', function (Blueprint $table) {
                $table->dropForeign(['case_id']);
                $table->dropColumn('case_id');
            });
        }

        // Remove case_id dari alternatives table
        if (Schema::hasColumn('alternatives', 'case_id')) {
            Schema::table('alternatives', function (Blueprint $table) {
                $table->dropForeign(['case_id']);
                $table->dropColumn('case_id');
            });
        }

        // Remove case_id dari decisions table
        if (Schema::hasColumn('decisions', 'case_id')) {
            Schema::table('decisions', function (Blueprint $table) {
                $table->dropForeign(['case_id']);
                $table->dropColumn('case_id');
            });
        }

        // Optional: Drop cases table entirely if not needed
        // Schema::dropIfExists('cases');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back case_id columns if needed
        Schema::table('criteria', function (Blueprint $table) {
            $table->unsignedBigInteger('case_id')->nullable();
        });

        Schema::table('alternatives', function (Blueprint $table) {
            $table->unsignedBigInteger('case_id')->nullable();
        });

        Schema::table('decisions', function (Blueprint $table) {
            $table->unsignedBigInteger('case_id')->nullable();
        });
    }
};