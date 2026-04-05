<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Billable;

class Member extends Model
{
    use Billable, HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'member_statuses_id',
        'firstname',
        'lastname',
        'email',
        'phone',
        'address',
        'postal_code',
        'city',
        'profile_photo_path',
        'birthdate',
        'gender',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'trial_ends_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the member status associated with the member.
     */
    public function memberStatus(): BelongsTo
    {
        return $this->belongsTo(MemberStatus::class, 'member_statuses_id');
    }

    /**
     * Get all teams this member is enrolled in.
     */
    public function memberOfTeams(): HasMany
    {
        return $this->hasMany(MemberOfTeam::class);
    }
}
