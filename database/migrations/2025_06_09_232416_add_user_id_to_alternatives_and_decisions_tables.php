<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First add the column without foreign key constraint
        Schema::table('alternatives', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->nullable();
        });

        Schema::table('decisions', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->nullable();
        });

        // Get the first admin user
        $adminId = DB::table('users')->where('role', 'admin')->first()?->id;

        // Update existing records with admin user ID
        if ($adminId) {
            DB::table('alternatives')->whereNull('user_id')->update(['user_id' => $adminId]);
            DB::table('decisions')->whereNull('user_id')->update(['user_id' => $adminId]);
        }

        // Now add the foreign key constraints and make columns non-nullable
        Schema::table('alternatives', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->change();
        });

        Schema::table('decisions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alternatives', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('decisions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
