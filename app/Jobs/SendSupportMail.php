<?php

namespace App\Jobs;

use App\Services\SupportMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Delivers one notification email from the queue, so website forms never
 * wait for the SMTP server. Processed by the scheduler (routes/console.php).
 */
class SendSupportMail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [60, 300];

    public function __construct(public string $to, public string $subject, public string $body) {}

    /**
     * Failures are logged by SupportMail; retry later while attempts remain.
     */
    public function handle(SupportMail $mail): void
    {
        if (! $mail->deliver($this->to, $this->subject, $this->body) && $this->attempts() < $this->tries) {
            $this->release($this->backoff[$this->attempts() - 1] ?? 300);
        }
    }
}
