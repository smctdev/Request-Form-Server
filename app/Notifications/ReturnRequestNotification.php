<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use app\Events\NotificationEvent;
use Log;

class ReturnRequestNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */

     protected $requestForm;
     protected $status;

     protected $firstname;
     protected $approverFirstname;
     protected $approverLastname;
     protected $comment;
     protected $requested_code;
    public function __construct($requestForm, $status, $firstname,$approverFirstname,$approverLastname,$request_code,$comment)
    {
        $this->requestForm = $requestForm;
        $this->status = $status;
        $this->firstname = $firstname;
        $this->approverFirstname =$approverFirstname;
        $this->approverLastname =$approverLastname;
        $this->comment=$comment;
        $this->requested_code=$request_code;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail','database','broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        
        Log::info($this->requestForm->created_at);
        $approvalUrl = route('view.single.request.form.for.approval', ['request_form_id' => $this->requestForm->id]);
                    return (new MailMessage)
                    ->view('emails.return_request',[
                        'requestForm' => $this->requestForm,
                        'firstname' =>$this->firstname,
                        'approvalUrl' => $approvalUrl,
                        'status' =>$this->status,
                        'approverFirstname' =>$this->approverFirstname,
                        'approverLastname' =>$this->approverLastname,
                        'request_code' => $this->requested_code,
                        'comment' =>$this->comment,
                        'date' => Carbon::parse($this->requestForm->created_at)->format('F j, Y')

                        ])
                        ->subject('Request Form Update - '.$this->requestForm->form_type. ' '.now()->format('Y-m-d H:i:s'))
                        ->line('Your request has been returned because it is ' . $this->status)
                        ->line('Request Type: '.$this->requestForm->form_type)
                        ->line('Status:' . $this->status)
                        ->action('Notification Action', url('/'));
                    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $approvalUrl = route('view.single.request.form.for.approval', ['request_form_id' => $this->requestForm->id]);
        return [
            'message' => 'Your request has been returned because it is ' . $this->status . ' by '. $this->approverFirstname.' '. $this->approverLastname,
            'requestForm' => $this->requestForm->form_type,
            'status' => $this->status,
            'created_at' => now()->toDateTimeString(),
            'firstname' =>$this->firstname,
            'approvalUrl' => $approvalUrl,
            'approverFirstname' =>$this->approverFirstname,
            'approverLastname' =>$this->approverLastname,
            'comment' =>$this->comment
        ];
    }


   /*  public function toBroadcast($notifiable)
    {
        //broadcast(new NotificationEvent($this->toArray($notifiable)));
        return new BroadcastMessage([
            'message' => 'Your request has been returned because it is ' . $this->status,
            'form_type' => $this->requestForm->form_type,
            'status' => $this->status,
            'created_at' => now()->toDateTimeString(),
        ]);
    } */
}