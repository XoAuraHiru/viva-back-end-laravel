<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shedule extends Model
{
    use HasFactory;

    protected $table = 'shedule';

    protected $fillable = [
        'shedule_no',
        'shedule_date',
        'shedule_time',
        'movie_id',
        'screen_id',
    ];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function time()
    {
        return $this->belongsTo(SheduleTime::class, 'shedule_time');
    }

    public function screen()
    {
        return $this->belongsTo(Screen::class, 'screen_id');
    }
}
