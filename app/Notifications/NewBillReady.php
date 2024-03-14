<?php

namespace App\Notifications;

use App\Models\Bill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class NewBillReady extends Notification 
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Bill $bill)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Tesla Electricity Usage for ' . $this->bill->billingProfile->name .' for the period ' . $this->bill->from->format('d-M-Y') . ' to ' . $this->bill->to->format('d-M-Y'))
                    ->markdown('emails.NewBillReady', [
                        'bill' => $this->bill,
                        'url' => $this->generateURL()
                    ]);
    }

    private function generateURL()
    {
        return route('bills.show', ['bill' => $this->bill->hash_id, 'switchToTeam' => $this->bill->billingProfile->team->id]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
