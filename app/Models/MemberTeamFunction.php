<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberTeamFunction extends Model
{
    protected $fillable = [
        'name',
        'description',
        'default_member_function',
    ];

    protected $casts = [
        'default_member_function' => 'boolean',
    ];

    public function membersOfTeam()
    {
        return $this->hasMany(MemberOfTeam::class, 'type_id');
    }   
}
