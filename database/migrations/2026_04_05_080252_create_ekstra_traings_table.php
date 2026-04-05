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
        Schema::create('ekstra_traings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_session_id')->constrained();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->string('description')->nullable();
            

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekstra_traings');
    }
};
