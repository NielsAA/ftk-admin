<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingWeeklySchedule extends Model
{
    protected $fillable = [
        'training_session_id',
        'day_of_week',
        'start_time',
        'end_time',
        'description',
    ];

    public function trainingSession(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class, 'training_session_id');
    }
}
