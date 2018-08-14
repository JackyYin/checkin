<?php

namespace App\Jobs\Stride;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Helpers\StrideHelper;
use App\Models\Check;

class PersonalNotification implements ShouldQueue
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
        $StrideHelper = new StrideHelper();
        $StrideHelper->personalNotification($this->check, $this->action);
    }

}
