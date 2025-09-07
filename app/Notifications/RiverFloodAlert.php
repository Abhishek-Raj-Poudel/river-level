<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RiverFloodAlert extends Notification
{
    use Queueable;

    public $riverName;
    public $level;
    public $threshold;
    /**
     * Create a new notification instance.
     */
    public function __construct(string $riverName, float $level, float $threshold)
    {
        $this->riverName = $riverName;
        $this->level = $level;
        $this->threshold = $threshold;

        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['broadcast', 'database'];
    }


    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => "River level reached {$this->level} in {$this->riverName}",
            'level' => $this->level,
        ]);
    }


    /**
     * Get the mail representation of the notification.
     */
    /* public function toMail(object $notifiable): MailMessage */
    /* { */
    /*     return (new MailMessage) */
    /*         ->line('The introduction to the notification.') */
    /*         ->action('Notification Action', url('/')) */
    /*         ->line('Thank you for using our application!'); */
    /* } */

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'river_name' => $this->riverName,
            'level' => $this->level,
            'threshold' => $this->threshold,
        ];
    }
}
