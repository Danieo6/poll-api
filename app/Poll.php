<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $fillable = [
        'question', 'author', 'close_time', 'multiple_answers'
    ];

    protected $hidden = [
        'updated_at', 'author'
    ];

    protected $casts = [
        'multiple_answers' => 'boolean'
    ];

    public function answers()
    {
        return $this->hasMany('App\PollAnswer', 'poll');
    }
}