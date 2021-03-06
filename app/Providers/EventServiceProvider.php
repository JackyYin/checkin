<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Database\Events\StatementPrepared;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            // add your listeners (aka providers) here
        ],

        'App\Events\LeaveCreated' => [
            'App\Listeners\Stride\RoomNotification',
            'App\Listeners\Stride\PersonalNotification',
            'App\Listeners\Discord\RoomNotification',
            'App\Listeners\Line\PersonalNotification'
        ],

        'App\Events\LeaveUpdated' => [
            'App\Listeners\Stride\RoomNotification',
            'App\Listeners\Stride\PersonalNotification',
            'App\Listeners\Discord\RoomNotification',
            'App\Listeners\Line\PersonalNotification'
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        Event::listen(StatementPrepared::class, function ($event) {
            $event->statement->setFetchMode(\PDO::FETCH_OBJ);
        });
    }
}
