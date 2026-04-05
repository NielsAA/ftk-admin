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
        Schema::create('members_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->foreignId('training_weekly_schedule_id')->nullable()->constrained('training_weekly_schedules')->nullOnDelete();
            $table->foreignId('ekstra_traing_id')->nullable()->constrained('ekstra_traings')->nullOnDelete();
            $table->date('check_in_date');
            $table->timestamps();

            $table->unique(
                ['member_id', 'training_weekly_schedule_id', 'check_in_date'],
                'members_check_ins_member_weekly_schedule_date_unique'
            );
            $table->unique(
                ['member_id', 'ekstra_traing_id', 'check_in_date'],
                'members_check_ins_member_ekstra_traing_date_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members_check_ins');
    }
};
