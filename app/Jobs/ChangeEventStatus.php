<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use App\Models\Event;

class ChangeEventStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $current_date_time = Carbon::now()->format('Y-m-d H:i:s');
        $upcoming_events = Event::where('status', 'upcoming')->get();
        $ongoing_events = Event::where('status', 'ongoing')->get();

        foreach($upcoming_events as $events) {
            if($events->opening_datetime <= $current_date_time) {
                $events->update([
                    'status' => 'ongoing'
                ]);
            }
        }

        foreach($ongoing_events as $events) {
            if($events->closing_datetime <= $current_date_time) {
                $events->update([
                    'status' => 'done'
                ]);
            }
        }
    }
}
