<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatType extends Model
{
    use HasFactory;

    protected $table = 'seat_type';

    protected $primaryKey = 'id';

    protected $fillable = [
        'type',
        'price',
    ];

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }
}
