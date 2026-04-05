<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrialSession extends Model
{
    protected $fillable = [
        'training_session_id',
        'member_id',
        'trial_date',
    ];

    public function trainingSession()
    {
        return $this->belongsTo(TrainingSession::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
