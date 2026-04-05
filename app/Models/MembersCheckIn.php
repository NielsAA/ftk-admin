<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembersCheckIn extends Model
{
    protected $fillable = [
        'member_id',
        'training_weekly_schedule_id',
        'ekstra_traing_id',
        'check_in_date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function trainingWeeklySchedule(): BelongsTo
    {
        return $this->belongsTo(TrainingWeeklySchedule::class, 'training_weekly_schedule_id');
    }

    public function ekstraTraing(): BelongsTo
    {
        return $this->belongsTo(EkstraTraing::class, 'ekstra_traing_id');
    }
}
