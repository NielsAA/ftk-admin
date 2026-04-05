<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkstraTraing extends Model
{
    protected $fillable = [
        'training_session_id',
        'date',
        'start_time',
        'end_time',
        'description',
    ];

    public function trainingSession()
    {
        return $this->belongsTo(TrainingSession::class);
    }
}
