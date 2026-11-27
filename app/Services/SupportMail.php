<?php

namespace App\Services;

use App\Jobs\SendSupportMail;
use App\Models\Setting;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Plain-text notification emails for every website submission (contact
 * form, live chat, tickets). Sent through the SMTP account configured in
 * Admin → Email SMTP Setting (applied in AppServiceProvider).
 *
 * A slow or unreachable mail server must never delay the visitor (one SMTP
 * send can take 10+ seconds). When the cron scheduler is running, messages
 * go to the queue it works through every minute; otherwise they are sent
 * after the response, which is closed early so the browser does not wait.
 * Failures are logged.
 */
class SupportMail
{
    /**
     * The scheduler counts as running if its heartbeat is this recent.
     */
    public const SCHEDULER_FRESH_SECONDS = 180;

    /**
     * @var list<array{to: string, subject: string, body: string}>
     */
    protected array $pending = [];

    protected bool $flushRegistered = false;

    public function brand(): string
    {
        return (string) Setting::get('brand_name', config('app.name'));
    }

    /**
     * Inbox that receives support notifications.
     */
    public function supportAddress(): ?string
    {
        return Setting::get('support_email') ?: null;
    }

    /**
     * Queue a plain-text email for delivery once the response is sent.
     * Returns false only when there is nobody to send to.
     */
    public function send(?string $to, string $subject, string $body): bool
    {
        if (! $to) {
            return false;
        }

        if ($this->canQueue()) {
            SendSupportMail::dispatch($to, $subject, $body);

            return true;
        }

        $this->pending[] = ['to' => $to, 'subject' => $subject, 'body' => $body];

        if (! $this->flushRegistered) {
            $this->flushRegistered = true;
            Event::listen(RequestHandled::class, fn (RequestHandled $event) => $this->closeConnectionEarly($event->response));
            app()->terminating(fn () => $this->flush());
        }

        return true;
    }

    /**
     * Queued mail is only sent when a worker runs, i.e. the cron scheduler
     * (which works the queue every minute) has a recent heartbeat.
     */
    public function canQueue(): bool
    {
        if (config('queue.default') === 'sync') {
            return false;
        }

        $lastRun = Cache::get(SystemTools::SCHEDULER_KEY);

        return $lastRun !== null && Carbon::parse($lastRun)->diffInSeconds(now()) <= self::SCHEDULER_FRESH_SECONDS;
    }

    /**
     * Tell the browser the response is complete, so it does not wait while
     * the queued mail is sent after it (servers without fastcgi_finish_request,
     * e.g. `php artisan serve` or mod_php).
     */
    protected function closeConnectionEarly(Response $response): void
    {
        if ($this->pending === [] || $response instanceof StreamedResponse || $response instanceof BinaryFileResponse || $response->headers->has('Content-Encoding')) {
            return;
        }

        $response->headers->set('Content-Length', (string) strlen((string) $response->getContent()));
        $response->headers->set('Connection', 'close');
    }

    /**
     * Deliver everything queued so far.
     */
    public function flush(): void
    {
        $messages = $this->pending;
        $this->pending = [];
        $this->flushRegistered = false;

        foreach ($messages as $message) {
            $this->deliver($message['to'], $message['subject'], $message['body']);
        }
    }

    /**
     * Send immediately (used for the SMTP test button).
     */
    public function deliver(string $to, string $subject, string $body): bool
    {
        try {
            Mail::raw($body, function ($mail) use ($to, $subject) {
                $mail->to($to)->subject($subject);

                if ($replyTo = $this->supportAddress()) {
                    $mail->replyTo($replyTo);
                }
            });

            return true;
        } catch (\Throwable $exception) {
            Log::warning("Support email to {$to} failed: ".$exception->getMessage());

            return false;
        }
    }

    public function signature(): string
    {
        return "\n\n— {$this->brand()} Support";
    }
}
