<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\PendingMail;
use Symfony\Component\Mime\Address;

class AccountVerificationMail extends Mailable
{
    /**
     * The OTP for account verification.
     *
     * @var string
     */
    public $otp;

    /**
     * Create a new message instance.
     *
     * @param  string  $otp
     * @return void
     */
    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Account Verification')
            ->view('emails.account_verification')
            ->with(['otp' => $this->otp]);
    }
}
