<?php

namespace App\Jobs\Line;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Helpers\LineHelper;
use App\Models\Check;

class PredictionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $staff;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($staff)
    {
        $this->staff = $staff;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $LineHelper = new LineHelper();
        $LineHelper->predictionNotification($this->staff);
    }
}
