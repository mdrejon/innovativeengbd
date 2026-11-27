<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * View helpers used by the frontend Blade templates (aliased as `Cms`).
 *
 * Content stored in the CMS is plain text unless a field is explicitly an
 * "html" field, so every helper here escapes its input.
 */
class Cms
{
    /**
     * Escape plain text and wrap the Taka sign in `.tk` (the brand fonts
     * lack the glyph, see html/AGENTS.md).
     */
    public static function text(mixed $value): HtmlString
    {
        $escaped = self::currencyTokens(e((string) ($value ?? '')));

        return new HtmlString(str_replace('৳', '<span class="tk">৳</span>', $escaped));
    }

    /**
     * Currency symbol from Global Settings → Currency: "plan" for hosting,
     * reseller and server plans, "domain" for the domain price list.
     */
    public static function currency(string $type = 'plan'): string
    {
        return $type === 'domain'
            ? (string) self::setting('domain_currency_symbol', '৳')
            : (string) self::setting('currency_symbol', '$');
    }

    /**
     * Currency code from Global Settings → Currency (e.g. USD, BDT).
     */
    public static function currencyCode(string $type = 'plan'): string
    {
        return $type === 'domain'
            ? (string) self::setting('domain_currency_code', 'BDT')
            : (string) self::setting('currency_code', 'USD');
    }

    /**
     * Currency symbol wrapped for display (the Taka sign needs `.tk`).
     */
    public static function currencyHtml(string $type = 'plan'): HtmlString
    {
        return self::text(self::currency($type));
    }

    /**
     * Replace the {currency} / {domain_currency} tokens that content uses in
     * prices ("{currency}4.15/yr"), so one setting changes every price.
     * Pass $escape = false for plain text that Blade escapes itself.
     */
    public static function currencyTokens(mixed $value, bool $escape = true): string
    {
        $value = (string) ($value ?? '');

        if (! str_contains($value, '{')) {
            return $value;
        }

        $symbol = fn (string $type) => $escape ? e(self::currency($type)) : self::currency($type);

        return strtr($value, ['{currency}' => $symbol('plan'), '{domain_currency}' => $symbol('domain')]);
    }

    /**
     * Short text that may contain inline markup (links, bold, code).
     * Every other tag is stripped.
     */
    public static function rich(mixed $value): HtmlString
    {
        $html = strip_tags((string) ($value ?? ''), '<a><b><strong><em><i><code><br><span>');
        $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        $html = preg_replace('/(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\2/i', '$1="#"', $html);

        return new HtmlString(str_replace('৳', '<span class="tk">৳</span>', self::currencyTokens($html)));
    }

    /**
     * Escape text and turn line breaks into `<br>` tags.
     */
    public static function lines(mixed $value): HtmlString
    {
        $lines = preg_split('/\r\n|\r|\n/', trim((string) ($value ?? '')));

        return new HtmlString(implode('<br>', array_map(fn (string $line) => (string) self::text($line), $lines)));
    }

    /**
     * Render a heading where one phrase is wrapped in `.text-gradient`.
     */
    public static function highlight(mixed $title, mixed $highlight = null): HtmlString
    {
        $title = (string) ($title ?? '');
        $highlight = trim((string) ($highlight ?? ''));

        if ($highlight === '') {
            return self::text($title);
        }

        $span = '<span class="text-gradient">'.self::text($highlight).'</span>';
        $position = mb_strpos($title, $highlight);

        if ($position === false) {
            return new HtmlString(trim(self::text($title).' '.$span));
        }

        $before = mb_substr($title, 0, $position);
        $after = mb_substr($title, $position + mb_strlen($highlight));

        return new HtmlString(self::text($before).$span.self::text($after));
    }

    /**
     * Resolve a CMS link: absolute URLs, anchors, mailto and tel pass through,
     * site paths become absolute application URLs.
     */
    public static function url(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return '#';
        }

        if (Str::startsWith($value, ['http://', 'https://', '#', 'mailto:', 'tel:', '//'])) {
            return $value;
        }

        return url($value === '/' ? '/' : '/'.ltrim($value, '/'));
    }

    /**
     * Resolve a stored image or file path to a public URL.
     */
    public static function asset(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '' || Str::startsWith($value, ['http://', 'https://', '//', 'data:'])) {
            return $value;
        }

        return asset(ltrim($value, '/'));
    }

    /**
     * Whether a CMS link points at the page being viewed (anchors ignored).
     */
    public static function isCurrent(mixed $value): bool
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '' || Str::startsWith($value, ['#', 'http://', 'https://', 'mailto:', 'tel:'])) {
            return false;
        }

        if (Str::contains($value, '#')) {
            return false;
        }

        return trim($value, '/') === trim(request()->path(), '/');
    }

    /**
     * Read a scalar global setting.
     */
    public static function setting(string $key, mixed $default = null): mixed
    {
        $value = Setting::get($key);

        return ($value === null || $value === '') ? $default : $value;
    }

    /**
     * Read a structured (JSON) global setting such as a menu or the footer,
     * falling back to the schema's `defaults` until it is first saved.
     *
     * @return array<mixed>
     */
    public static function layout(string $key): array
    {
        return app(ContentSchema::class)->withDefaults($key, Setting::json($key));
    }

    /**
     * URL templates for the domain search buttons: a section's own override
     * wins, then Site Layout → Domain search, then the schema defaults.
     *
     * @return array{register: string, transfer: string}
     */
    public static function domainSearchUrls(mixed $registerOverride = null, mixed $transferOverride = null): array
    {
        $widget = self::layout('widget_domain_search');
        $defaults = config('cms.layout.widget_domain_search.defaults', []);

        $pick = fn (mixed $override, string $key): string => collect([$override, $widget[$key] ?? null, $defaults[$key] ?? null])
            ->map(fn (mixed $value) => trim((string) ($value ?? '')))
            ->first(fn (string $value) => $value !== '', '');

        return [
            'register' => $pick($registerOverride, 'register_url'),
            'transfer' => $pick($transferOverride, 'transfer_url'),
        ];
    }

    /**
     * Fill a domain order URL template: {domain} (example.com), {sld}
     * (example) and {tld} (.com). A template without {domain} or {sld} gets
     * "domain=<domain>" appended when a domain is given.
     * Keep in sync with domainUrl() in public/assets/js/main.js.
     */
    public static function domainUrl(mixed $template, string $domain = '', ?string $tld = null): string
    {
        $template = trim((string) ($template ?? ''));
        $dot = strpos($domain, '.');
        $sld = $dot === false ? $domain : substr($domain, 0, $dot);
        $tld ??= $dot === false ? '' : substr($domain, $dot);

        $url = strtr($template, [
            '{domain}' => rawurlencode($domain),
            '{sld}' => rawurlencode($sld),
            '{tld}' => rawurlencode($tld),
        ]);

        if ($domain !== '' && ! Str::contains($template, ['{domain}', '{sld}'])) {
            $url .= (Str::contains($url, '?') ? '&' : '?').'domain='.rawurlencode($domain);
        }

        return self::url($url);
    }

    /**
     * Format a price without decimals ("1,600").
     */
    public static function wholePrice(mixed $value): string
    {
        return $value === null ? '—' : number_format((float) $value, 0);
    }

    /**
     * Format a price with two decimals ("1,600.00").
     */
    public static function price(mixed $value): string
    {
        return $value === null ? '—' : number_format((float) $value, 2);
    }
}
