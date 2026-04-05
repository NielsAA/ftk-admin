<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamAccessToTraining extends Model
{
    protected $fillable = [
        'team_id',
        'training_session_id',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function trainingSession()
    {
        return $this->belongsTo(TrainingSession::class, 'training_session_id');
    }
}
