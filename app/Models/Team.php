<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'number',
        'description',
        'photo_path',
        'price',
        'price_type',
        'stripe_price_id',
        'stripe_product_id',
    ];

    public function numberOfMembers(): int
    {
        return $this->membersOfTeam()->whereNull('left_at')->count();
    }

    public function membersOfTeam(): HasMany
    {
        return $this->hasMany(MemberOfTeam::class, 'team_id');
    }

    public function trainingSessions(): BelongsToMany
    {
        return $this->belongsToMany(TrainingSession::class, 'team_access_to_trainings')
            ->withTimestamps();
    }
}
