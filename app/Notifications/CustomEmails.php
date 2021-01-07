<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomEmails extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $testUser = 'Eddy';
            
            $details = [
            'subject' => 'You are in :) quick video inside',
            'greeting' => 'Thank you for signing up  '.$user_data['first_name'],
            'body' => 'First, we built Noworri to help online vendors stand trustful & reliable while engaging in a distance selling with potential buyers and secondly, to protect buyers from losing money while engaging in a transaction with an online vendor.',
            'videoDescription' => 'We created this video to show you what we are all about',
            'salutation' => 'Best Regards, Josiane',
            'actionText' => '',
            'actionURL' => url('https://www.youtube.com/watch?v=ZwdS2owGEC4'),
            ];
                 
        //  $details = [
        //     'subject' => 'Your business profile is under review',
        //     'greeting' => 'Hello  '.$testUser,
        //     'body' => 'We have received your business profile which is currently under review with our team, you should hear back from us within the next 24 hours.',
        // ];
        $this->details = $details;
        return (new MailMessage)
                    ->from('noworriappstech@gmail.com', 'Noworri')
                    ->subject($this->details['subject'])
                    ->greeting($this->details['greeting'])
                    ->line($this->details['body'])
                    ->line($this->details['videoDescription'])
                    ->action($this->details['actionText'],$this->details['actionURL']);
                    // ->salutation($this->details['salutation']);
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
