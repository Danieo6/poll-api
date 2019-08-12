<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PollAnswer extends Model
{
    protected $fillable = [
        'answer', 'poll',
    ];

    protected $hidden = [
        'poll'
    ];

    protected $casts = [
        'votes_count' => 'integer'
    ];

    public function votes()
    {
        return $this->hasMany('App\Vote', 'answer');
    }
}