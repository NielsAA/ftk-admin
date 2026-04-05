<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClosedDay extends Model
{
    protected $fillable = [
        'training_weekly_schedule_id',
        'date',
        'reason',
    ];

    public function trainingWeeklySchedule()
    {
        return $this->belongsTo(TrainingWeeklySchedule::class);
    }   
}
