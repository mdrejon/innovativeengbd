<?php

namespace App\Services;

/**
 * Applies the SMTP account saved in Admin → Email SMTP Setting to Laravel's
 * mail config. Incomplete settings are ignored so a half-filled form never
 * breaks outgoing email; .env is used instead.
 */
class MailSettings
{
    /**
     * Seconds to wait for the SMTP server (keeps a down server from hanging
     * requests until PHP's execution limit).
     */
    public const TIMEOUT_SECONDS = 10;

    /**
     * @param  array<string, mixed>  $settings
     */
    public function apply(array $settings): bool
    {
        if (! $this->isComplete($settings)) {
            return false;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => trim((string) $settings['mail_host']),
            'mail.mailers.smtp.port' => (int) ($settings['mail_port'] ?? 587),
            'mail.mailers.smtp.username' => $settings['mail_username'],
            'mail.mailers.smtp.password' => ($settings['mail_password'] ?? '') !== '' ? $settings['mail_password'] : null,
            // "ssl" is implicit TLS (smtps); "tls" and "none" let the server negotiate STARTTLS.
            'mail.mailers.smtp.scheme' => ($settings['mail_encryption'] ?? '') === 'ssl' ? 'smtps' : null,
            'mail.mailers.smtp.timeout' => self::TIMEOUT_SECONDS,
        ]);

        if (! empty($settings['mail_from_address'])) {
            config([
                'mail.from.address' => $settings['mail_from_address'],
                'mail.from.name' => ($settings['mail_from_name'] ?? '') ?: config('mail.from.name'),
            ]);
        }

        return true;
    }

    /**
     * Whether the saved settings are enough to send through SMTP.
     *
     * @param  array<string, mixed>  $settings
     */
    public function isComplete(array $settings): bool
    {
        return ($settings['mail_mailer'] ?? 'smtp') === 'smtp'
            && trim((string) ($settings['mail_host'] ?? '')) !== ''
            && trim((string) ($settings['mail_username'] ?? '')) !== '';
    }

    /**
     * Human-readable state for the SMTP settings screen.
     *
     * @param  array<string, mixed>  $settings
     * @return array{active: bool, message: string}
     */
    public function describe(array $settings): array
    {
        if ($this->isComplete($settings)) {
            return [
                'active' => true,
                'message' => 'Emails are sent through '.$settings['mail_host'].':'.($settings['mail_port'] ?? 587).' as '.$settings['mail_username'].'.',
            ];
        }

        $fallback = config('mail.default');

        return [
            'active' => false,
            'message' => 'Saved SMTP settings are not used yet: fill in the host and username. '
                .($fallback === 'log'
                    ? 'Until then emails are only written to the log file, not delivered.'
                    : "Until then the server's .env mailer ({$fallback}) is used."),
        ];
    }
}
