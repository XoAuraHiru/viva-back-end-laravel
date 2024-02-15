<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $table = 'movie';

    protected $fillable = [
        'name',
        'year',
        'genre_id',
        'description',
        'cast',
        'banner_img',
        'rating',
        'status_id',
        'created_at',
        'updated_at',
        'code'
    ];

    public function genre()
    {
        return $this->belongsTo(Genre::class);
    }

    public function status()
    {
        return $this->belongsTo(MovieStatus::class);
    }
}
