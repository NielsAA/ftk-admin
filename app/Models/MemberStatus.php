<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberStatus extends Model
{
    protected $fillable = ['name', 'is_warning'];

    
    protected $casts = [
        'is_warning' => 'boolean',
    ];

    
    public function members()
    {
        return $this->hasMany(Member::class);   
    }
}
