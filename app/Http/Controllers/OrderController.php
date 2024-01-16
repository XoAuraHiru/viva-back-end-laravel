<?php

namespace App\Http\Controllers;

use App\Jobs\CheckOrderStatus;
use App\Mail\PaymentNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use PhpParser\Node\Stmt\TryCatch;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'User is not logged in'], 401);
        }

        $clientIP = $request->ip();

        $content = [
            'title' => 'Mail from Laravel',
            'message' => 'Reservation Created! User ' . $clientIP
        ];

        try {
            Mail::to('astronomyhirunchamara@gmail.com')->send(new PaymentNotification($content));
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while sending the email: ' . $e->getMessage()
            ], 500);
        } finally {
            $seats = $request->seats;
            $shedule = $request->shedule_id;

            DB::beginTransaction();

            try {
                $order_id = DB::table('order')->insertGetId([
                    'created_at' => Carbon::now('Asia/Colombo'),
                    'paid_status' => 0,
                    'user_id' => Auth::id(),
                ]);

                foreach ($seats as $seat) {
                    $seat_id = DB::table('seat')
                        ->where('seat_no', $seat)
                        ->value('id');

                    $seat_price = DB::table('seat')
                        ->where('seat_no', $seat)
                        ->join('seat_type', 'seat_type.id', '=', 'seat.seat_type')
                        ->value('seat_type.price');


                    DB::table('reservations')->insert([
                        'order_id' => $order_id,
                        'seat_id' => $seat_id,
                        'shedule_id' => $shedule,
                        'user_id' => Auth::id(),
                        'timestamp' => Carbon::now(),
                        'price' => $seat_price,
                    ]);
                }

                $data = DB::table('reservations')
                    ->where('order_id', $order_id)
                    ->join('seat', 'seat.id', '=', 'reservations.seat_id')
                    ->get();

                $shedule = DB::table('shedule')
                    ->join('movie', 'movie.id', '=', 'shedule.movie_id')
                    ->join('screen', 'screen.screen_id', '=', 'shedule.screen_id')
                    ->join('screen_time', 'screen_time.id', '=', 'shedule.shedule_time')
                    ->where('shedule_id', '=', $shedule)
                    ->select('shedule.*', 'screen_time.time', 'movie.*')
                    ->get();

                $total_amount = DB::table('reservations')
                    ->where('order_id', $order_id)
                    ->join('seat', 'seat.id', '=', 'reservations.seat_id')
                    ->join('seat_type', 'seat_type.id', '=', 'seat.seat_type')
                    ->sum('seat_type.price');

                DB::commit();

                dispatch(new CheckOrderStatus($order_id))->delay(now()->addMinutes(10));

                return response()->json([
                    'status' => 200,
                    'data' => $data,
                    'shedule' => $shedule,
                    'total_amount' => $total_amount,
                    'order_id' => $order_id,
                ], 200);
            } catch (\Exception $e) {
                DB::rollback();

                return response()->json([
                    'status' => 500,
                    'message' => 'An error occurred while processing the order: ' . $e->getMessage()
                ], 500);
            }
        }
    }
}
