<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\URL;
use App\Mail\VerifyNewEmail;

class SendNewEmailVerificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $newEmail;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $newEmail)
    {
        $this->userId = $userId;
        $this->newEmail = $newEmail;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $verificationUrlFotn = URL::temporarySignedRoute(
        'email.update.verify',
        now()->addMinutes(30),
        [
            'id' => $this->userId,
            'new_email' => $this->newEmail,
        ]
    );

    Mail::to($this->newEmail)->send(new VerifyNewEmail($verificationUrlFotn));
    }
}
