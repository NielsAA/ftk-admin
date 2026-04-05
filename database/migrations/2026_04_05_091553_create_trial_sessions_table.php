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
        Schema::create('trial_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_session_id')->constrained();
            $table->foreignId('member_id')->constrained();
            $table->date('trial_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trial_sessions');
    }
};
