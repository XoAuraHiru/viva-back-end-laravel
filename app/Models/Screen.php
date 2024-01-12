<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Screen extends Model
{
    use HasFactory;

    protected $table = 'screen';

    protected $primaryKey = 'screen_id';

    protected $fillable = [
        'screen_number',
        'number_of_seats',
        'screen_name',
        'screen_logo',
    ];
}
