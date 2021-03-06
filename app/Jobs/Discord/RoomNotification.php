<?php

namespace App\Jobs\Discord;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Helpers\DiscordHelper;
use App\Models\Check;

class RoomNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $check;
    protected $action;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Check $check, $action)
    {
        $this->check = $check;
        $this->action = $action;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $DiscordHelper = new DiscordHelper();
        $DiscordHelper->roomNotification($this->check, $this->action);
    }
}
