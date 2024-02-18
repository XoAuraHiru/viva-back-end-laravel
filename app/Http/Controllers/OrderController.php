<?php

namespace App\Http\Controllers;

use App\Jobs\CheckOrderStatus;
use App\Mail\PaymentNotification;
use App\Models\Order;
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
                $order_no = $this->createUniqueCode();

                $order_id = DB::table('order')->insertGetId([
                    'created_at' => Carbon::now('Asia/Colombo'),
                    'paid_status' => 0,
                    'user_id' => Auth::id(),
                    'order_no' => $order_no,
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

                $order = DB::table('reservations')
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

                DB::table('order')
                    ->where('order_id', $order_id)
                    ->update(['amount' => $total_amount]);

                DB::commit();

                $data = [
                    'order_id' => $order_id,
                    'order' => $order,
                    'total_amount' => $total_amount,
                    'shedule' => $shedule,
                ];

                dispatch(new CheckOrderStatus($order_id))->delay(now()->addMinutes(10));

                return response()->json([
                    'status' => 200,
                    'data' => $data,
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

    public function index($order_id = null)
    {
        if ($order_id !== null) {
            $order = DB::table('order')
                ->where('order_id', $order_id)
                ->get();

            if ($order->count() > 0) {
                return response()->json([
                    'status' => 200,
                    'data' => $order
                ], 200);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'No Order Records Found'
                ], 404);
            }
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No Order Records Found'
            ], 404);
        }
    }

    public function getUserOrders(){

        $user_id = Auth::user()->id;
    
        if($user_id !== null){

            $orders = Order::where('user_id', $user_id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            if($orders->count() > 0){
                return response()->json([
                    'status' => 200,
                    'data' => $orders
                ], 200);
            }else{
                return response()->json([
                    'status' => 404,
                    'message' => 'No Order Records Found'
                ], 404);
            }
        }else{
            return response()->json([
                'status' => 401,
                'message' => 'User Not Logged In'
            ], 401);
        }
    }

    public function getOrders(){

        $orders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if($orders->count() > 0){
            return response()->json([
                'status' => 200,
                'data' => $orders
            ], 200);
        }else{
            return response()->json([
                'status' => 404,
                'message' => 'No Order Records Found'
            ], 404);
        }
    }

    public function deleteOrder($order_id){

        $order = Order::find($order_id);

        if($order !== null){
            $order->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Order Deleted Successfully'
            ], 200);
        }else{
            return response()->json([
                'status' => 404,
                'message' => 'Order Not Found'
            ], 404);
        }
    }

    public function createUniqueCode()
    {
        $code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 15);
        if (Order::where('order_no', $code)->exists()) {
            return $this->createUniqueCode();
        } else {
            return $code;
        }
    }
}
