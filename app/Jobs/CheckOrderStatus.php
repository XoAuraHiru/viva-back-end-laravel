<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckOrderStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order_id;

    /**
     * Create a new job instance.
     */
    public function __construct(int $order_id)
    {
        $this->order_id = $order_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $order = DB::table('order')->where('order_id', $this->order_id)->first();

            if ($order && $order->paid_status == 0) {
                DB::table('reservations')->where('order_id', $this->order_id)->delete();
                DB::table('order')->where('order_id', $this->order_id)->update(['paid_status' => 3]);
            }
        } catch (\Exception $e) {
            // Log the error message
            Log::error('An error occurred while processing the order: ' . $e->getMessage());

            // Optionally, you can re-throw the exception if you want it to be handled by Laravel's exception handler
            // throw $e;
        }
    }
}
