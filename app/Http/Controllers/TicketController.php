<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function viewTickets($order_id)
    {
        $tickets = Ticket::with(['user', 'order', 'shedule', 'seat'])
            ->where('order_id', $order_id)
            ->get();

        if ($tickets->count() > 0) {
            return response()->json([
                'status' => 200,
                'data' => $tickets
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No Tickets Found'
            ], 404);
        }
    }
}
