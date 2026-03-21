<?php

namespace App\Listeners;

use App\Events\UserRegistration;
use App\Mail\VerifyAccountEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class SendVerifyAccountEmail implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(UserRegistration $event): void
    {
        try {
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $event->user->id,
                    'hash' => sha1($event->user->email),
                ]
            );

            // Sync email
            Mail::to($event->user->email)->send(
                new VerifyAccountEmail(
                    $event->user->name,
                    $event->user->surname,
                    $verificationUrl,
                )
            );
        } catch (Throwable $ex) {
            Log::error('Send verify account email failed', [
                'ex' => $ex->getMessage(),
            ]);
        }
    }
}
