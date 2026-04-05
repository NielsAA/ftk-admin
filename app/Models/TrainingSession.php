<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingSession extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color',
        'number_of_trials',
    ];

    public function teamAccessToTrainings(): HasMany
    {
        return $this->hasMany(TeamAccessToTraining::class, 'training_session_id');
    }

    public function trainingWeeklySchedules(): HasMany
    {
        return $this->hasMany(TrainingWeeklySchedule::class, 'training_session_id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_access_to_trainings')
            ->withTimestamps();
    }
}
