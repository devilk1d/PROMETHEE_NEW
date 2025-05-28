<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('criteria', function (Blueprint $table) {
            // Change to preference_function to match your error
            $table->string('preference_function')->default('usual');
            $table->decimal('q', 10, 4)->nullable(); // Increased precision
            $table->decimal('p', 10, 4)->nullable(); // Increased precision
            // Only include s if you actually need it
            $table->decimal('s', 10, 4)->nullable();
        });
    }

    public function down()
    {
        Schema::table('criteria', function (Blueprint $table) {
            $table->dropColumn([
                'preference_function', 
                'q', 
                'p',
                's' // Only if you added it
            ]);
        });
    }
};