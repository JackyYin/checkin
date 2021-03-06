<?php

namespace App\Listeners\Stride;

use App\Events\LeaveCreated;
use App\Helpers\StrideHelper;
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
        $StrideHelper = new StrideHelper();
        $StrideHelper->personalNotification($event->check, $event->action);
    }
}
