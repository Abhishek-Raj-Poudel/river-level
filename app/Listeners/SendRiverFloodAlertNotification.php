<?php

namespace App\Listeners;

use App\Events\RiverLevelExceeded;
use App\Models\User;
use App\Notifications\RiverFloodAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendRiverFloodAlertNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RiverLevelExceeded $event): void
    {
        $users = User::all();

        Notification::send($users, new RiverFloodAlert(
            $event->river->name,
            $event->river->current_water_level,
            $event->river->normal_water_level
        ));
    }
}
