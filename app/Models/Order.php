<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'order';

    protected $primaryKey = 'order_id'; 

    protected $fillable = [
        'created_at',
        'order_no',
        'paid_status',
        'user_id',
        'paid_at',
        'amount',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
