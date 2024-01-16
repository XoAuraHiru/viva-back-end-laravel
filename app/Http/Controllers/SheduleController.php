<?php

namespace App\Http\Controllers;

use App\Models\Shedule;
use Illuminate\Http\Request;

class SheduleController extends Controller
{
    public function index( $type = null, $id = null)
    {
        if ($type === 'show' && $id !== null){
            $shows = Shedule::with(['movie', 'time', 'screen'])
                ->where('shedule_id', $id)
                ->get();

            if ($shows->count() > 0) {
                return response()->json([
                    'status' => 200,
                    'data' => $shows
                ], 200);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'No Shedule Records Found'
                ], 404);
            }
        } elseif ($type === 'latest' && $id !== null){
            $shows = Shedule::with(['movie', 'time', 'screen'])
                ->orderBy('shedule_date', 'desc')
                ->limit($id)
                ->get();

            if ($shows->count() > 0) {
                return response()->json([
                    'status' => 200,
                    'data' => $shows
                ], 200);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'No Lastest Shedule Records Found'
                ], 404);
            }
        } elseif ($type === 'movie' && $id !== null){
            $shows = Shedule::with(['movie', 'time', 'screen'])
                ->where('movie_id', $id)
                ->orderBy('shedule_date', 'desc')
                ->get();

            if ($shows->count() > 0) {
                return response()->json([
                    'status' => 200,
                    'data' => $shows
                ], 200);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'No Shedule Records Found For This Movie'
                ], 404);
            }
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No Shedule Records Found'
            ], 404);
        }
    }
}
