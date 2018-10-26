<?php

namespace App\Listeners\Line;

use App\Events\LeaveCreated;
use App\Helpers\LineHelper;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PersonalNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  LeaveCreated  $event
     * @return void
     */
    public function handle($event)
    {
        $LineHelper = new LineHelper();
        $LineHelper->personalNotification($event->check, $event->action);
    }
}
