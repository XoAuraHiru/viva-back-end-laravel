<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;
    protected $table = 'seat';

    protected $primaryKey = 'id';

    protected $fillable = [
        'screen_id',
        'seat_no',
        'seat_type,'
    ];

    public function type()
    {
        return $this->belongsTo(SeatType::class);
    }
}
