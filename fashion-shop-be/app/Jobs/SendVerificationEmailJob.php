<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use App\Mail\VerifyEmail;

class SendVerificationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(): void
    {
       $verificationUrl = URL::temporarySignedRoute(
        'verification.verify.fotn', 
        now()->addMinutes(30),      
        [
            'id' => $this->user->getKey(),
            'hash' => sha1($this->user->getEmailForVerification()),
        ]
        );


         Mail::to($this->user->email)->send(new VerifyEmail($verificationUrl));
    }
}
