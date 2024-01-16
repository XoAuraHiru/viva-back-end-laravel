<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use Illuminate\Http\Request;

class SeatController extends Controller
{
    public function index()
    {
        $seats = Seat::with('type')->get();

        if ($seats->count() > 0) {
            return response()->json([
                'status' => 200,
                'data' => $seats
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No Seat Records Found'
            ], 404);
        }
    }
}
