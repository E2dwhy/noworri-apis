<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Rejected extends Notification
{
     use Queueable;
        private $details;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($details)

    {

        $this->details = $details;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->from('noworriappstech@gmail.com', 'Noworri')
                    ->subject($this->details['subject'])
                    ->greeting($this->details['greeting'])
                    ->line($this->details['intro'])
                    ->line($this->details['body1'])
                    ->line($this->details['body2'])
                    ->line($this->details['body3'])
                    ->line($this->details['point1'])
                    ->line($this->details['point2'])
                    ->line($this->details['point3']);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
