<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EscrowNotification extends Notification
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
        if(!isset($this->details['body1'])) {
            $this->details['body1'] = '';
        }

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
                    // ->from('noworriappstech@gmail.com', 'Noworri Technologies')
                    // ->subject('Your transaction was successfully created on noworri.com')
                    // //->line('Hi!')
                    // ->line('Congratulations on your nearly created transaction.We will notify you it is accepted.')
                    // ->action('Accept', url('/'))
                    // ->line('Sincerely,')
                    // ->line('Noworri.com,');
                    
                    ->from('noworriappstech@gmail.com', 'Noworri')
                    ->subject($this->details['subject'])
                    ->greeting($this->details['greeting'])
                    ->line($this->details['body'])
                    ->line($this->details['body1']);

    }
    
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
