<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('criteria_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alternative_id');
            $table->unsignedBigInteger('criteria_id');
            $table->decimal('value', 10, 2);
            $table->timestamps();
        
            $table->foreign('alternative_id')->references('id')->on('alternatives')->onDelete('cascade');
            $table->foreign('criteria_id')->references('id')->on('criteria')->onDelete('cascade');
            
            $table->unique(['alternative_id', 'criteria_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('criteria_values');
    }
};