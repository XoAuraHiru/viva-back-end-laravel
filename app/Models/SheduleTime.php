<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SheduleTime extends Model
{
    use HasFactory;

    protected $table = 'screen_time';

    protected $fillable = [
        'time'
    ];
}
