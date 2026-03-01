<?php

namespace App\Listeners;

use App\Events\UserRegistration;
use App\Mail\WelcomeEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendWelcomeEmail
{
    /**
     * Handle the event.
     */
    public function handle(UserRegistration $event): void
    {
        try {
            Mail::to($event->user->email)->queue(
                new WelcomeEmail(
                    $event->user->name,
                    $event->user->surname,
                )
            );
        } catch (Throwable $ex) {
            Log::error('Send welcome email failed', [
                'ex' => $ex->getMessage()
            ]);
        }
    }
}
