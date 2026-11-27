<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Section;
use App\Models\Setting;
use Illuminate\Support\Collection;

class SeoService
{
    /**
     * Preload image
     *
     * @param  Collection<int, Section>  $sections
     */
    public function preloadImage(Page $page, Collection $sections): ?string
    {
        $first = $sections->first();

        if ($first?->renderType() === 'hero') {
            $data = $first->renderData();
            $slides = $data['slides'] ?? [];
            return !empty($slides[0]['image']) ? $slides[0]['image'] : 'assets/images/lift/hero-glass-lift.jpg';
        }

        return $page->og_image ?: 'assets/images/lift/hero-glass-lift.jpg';
    }

    /**
     * Schema.org JSON-LD blocks for a page
     *
     * @param  Collection<int, Section>  $sections
     * @return array<int, array<string, mixed>>
     */
    public function schemas(Page $page, Collection $sections): array
    {
        $schemas = [$this->organization()];

        return $schemas;
    }

    /**
     * @return array<string, mixed>
     */
    public function organization(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => Setting::get('site_name', 'Innovative Engineering'),
            'description' => Setting::get('site_tagline', 'Total solution for lifts and escalators — installation, repair, maintenance and spare parts.'),
            'image' => asset(Setting::get('site_logo', 'assets/images/logo/logo.png')),
            'telephone' => [
                Setting::get('phone', '+8801714216666'),
                Setting::get('phone2', '+8801829984722'),
            ],
            'email' => Setting::get('sales_email', 'palash_pe@yahoo.com'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => Setting::get('head_office', 'Barik Building, 10 Sk. Mujib Road (2nd Floor), Agrabad C/A'),
                'addressLocality' => 'Chattogram',
                'addressCountry' => 'BD',
            ],
            'openingHours' => 'Mo-Su 00:00-23:59',
            'sameAs' => collect(Setting::json('social_links'))
                ->pluck('url')
                ->filter(fn ($url) => is_string($url) && str_starts_with($url, 'http'))
                ->values()
                ->all(),
        ];
    }

    /**
     * XML sitemap with every active page and published blog post.
     */
    public function sitemapXml(): string
    {
        $urls = Page::query()->active()->orderBy('sort_order')->get()
            ->map(fn (Page $page) => [
                'loc' => url($page->path()),
                'lastmod' => $page->updated_at?->toAtomString(),
                'priority' => $page->slug === Page::HOME_SLUG ? '1.0' : '0.8',
            ])
            ->concat(BlogPost::query()->published()->get()->map(fn (BlogPost $post) => [
                'loc' => route('blog.show', $post->slug),
                'lastmod' => $post->updated_at?->toAtomString(),
                'priority' => '0.6',
            ]));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= '  <url><loc>'.htmlspecialchars($url['loc']).'</loc>'
                .($url['lastmod'] ? '<lastmod>'.$url['lastmod'].'</lastmod>' : '')
                .'<priority>'.$url['priority'].'</priority></url>'."\n";
        }

        return $xml.'</urlset>';
    }
}

