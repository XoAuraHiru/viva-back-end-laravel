<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieStatus extends Model
{
    use HasFactory;

    protected $table = 'movie_status';

    protected $fillable = [
        'genre'
    ];

    public function movies()
    {
        return $this->hasMany(Movie::class);
    }
}
