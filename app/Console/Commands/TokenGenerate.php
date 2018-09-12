<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bot;

class TokenGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token:generate 
        {client : Oauth Client Name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Oath Token for Bot';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $bot = Bot::where('name', $this->argument('client'))->first();

        if (!$bot) {
            $this->error('不存在的Bot');
            return;
        }

        $this->info($bot->createToken('Bot')->accessToken);
    }
}
