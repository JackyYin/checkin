<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Staff;

class AuthenticationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $staff;
    protected $url;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Staff $staff, $url)
    {
        $this->staff = $staff;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'),env('mail.from.name'))
            ->to($this->staff->email, $this->staff->name)
            ->subject("您好,請點擊連結以啟用帳號")
            ->view('emails.registration')
            ->with([
                'staff' => $this->staff,
                'url'   => $this->url,
            ]);
    }
}
