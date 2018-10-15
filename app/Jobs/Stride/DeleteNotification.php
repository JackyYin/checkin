<?php

namespace App\Jobs\Stride;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Helpers\StrideHelper;
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
        $StrideHelper = new StrideHelper();
        $StrideHelper->roomNotification($this->check, $this->action);
    }
}
