<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create cases table with user_id from the start
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Add unique constraint for case name per user
            $table->unique(['user_id', 'name']);
        });

        // Update existing tables to ensure they have case_id
        if (!Schema::hasColumn('criteria', 'case_id')) {
            Schema::table('criteria', function (Blueprint $table) {
                $table->unsignedBigInteger('case_id')->after('id');
                $table->foreign('case_id')->references('id')->on('cases')->onDelete('cascade');
            });
        }

        if (!Schema::hasColumn('alternatives', 'case_id')) {
            Schema::table('alternatives', function (Blueprint $table) {
                $table->unsignedBigInteger('case_id')->after('id');
                $table->foreign('case_id')->references('id')->on('cases')->onDelete('cascade');
            });
        }

        if (!Schema::hasColumn('decisions', 'case_id')) {
            Schema::table('decisions', function (Blueprint $table) {
                $table->unsignedBigInteger('case_id')->after('id');
                $table->foreign('case_id')->references('id')->on('cases')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        // Drop foreign keys first
        Schema::table('criteria', function (Blueprint $table) {
            $table->dropForeign(['case_id']);
            $table->dropColumn('case_id');
        });

        Schema::table('alternatives', function (Blueprint $table) {
            $table->dropForeign(['case_id']);
            $table->dropColumn('case_id');
        });

        Schema::table('decisions', function (Blueprint $table) {
            $table->dropForeign(['case_id']);
            $table->dropColumn('case_id');
        });

        Schema::dropIfExists('cases');
    }
};