<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EscrowDestNotification extends Notification
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
        //
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
         return ['mail','database'];
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
                    ->subject('A new Noworri transaction requires your agreement')
                    ->greeting($this->details['greeting'])
                    ->line($this->details['body'])
                    ->line($this->details['body1'])
                    ->action($this->details['actionText'], $this->details['actionURL']);
                    //->line($this->details['thanks']);
    }

  

    /**

     * Get the array representation of the notification.

     *

     * @param  mixed  $notifiable

     * @return array

     */

    public function toDatabase($notifiable)

    {

        return [

            'id' => $this->details['id']

        ];

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
