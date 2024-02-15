<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function view(){
        $genres = Genre::all();
        if($genres->count() > 0){
            return response()->json([
                'status' => 200,
                'data' => $genres
            ], 200);
        }else{
            return response()->json([
                'status' => 404,
                'message' => 'No Genre Records Found'
            ], 404);
        }
    }
}
