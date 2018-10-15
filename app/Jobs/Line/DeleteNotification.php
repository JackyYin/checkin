<?php

namespace App\Jobs\Line;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Helpers\LineHelper;
use App\Models\Check;

class DeleteNotification
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $check;
    protected $action;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Check $check)
    {
        $this->check = $check;
        $this->action = "Delete";
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $LineHelper = new LineHelper();
        $LineHelper->personalNotification($this->check, $this->action);
    }
}
