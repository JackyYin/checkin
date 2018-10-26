<?php

namespace App\Listeners\Discord;

use App\Events\LeaveCreated;
use App\Helpers\DiscordHelper;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class RoomNotification implements ShouldQueue
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
    public function handle(LeaveCreated $event)
    {
        $DiscordHelper = new DiscordHelper();
        $DiscordHelper->roomNotification($event->check, $event->action);
    }
}
