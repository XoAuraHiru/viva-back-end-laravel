<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $table = 'ticket';

    protected $primaryKey = 'ticket_id';

    protected $fillable = [
        'order_id',
        'users_id',
        'shedule_id',
        'seat_id',
        'price',
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function shedule()
    {
        return $this->belongsTo(Shedule::class);
    }

    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }
}
