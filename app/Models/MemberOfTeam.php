<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberOfTeam extends Model
{
    protected $fillable = [
        'member_id',
        'team_id',
        'member_team_function_id',
        'joined_at',
        'left_at',
        'stripe_subscription_id',

    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function memberTeamFunction()
    {
        return $this->belongsTo(MemberTeamFunction::class, 'member_team_function_id');
    }


}
