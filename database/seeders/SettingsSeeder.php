<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General & branding
            'site_name' => 'Innovative Engineering | Lift & Escalator Solutions',
            'brand_name' => 'Innovative Engineering',
            'site_tagline' => 'Total solution for lifts and escalators — installation, repair, maintenance and spare parts in Bangladesh.',
            'site_logo' => 'assets/images/logo/logo.png',
            'site_logo_dark' => 'assets/images/logo/logo.png',
            'site_favicon' => 'assets/images/logo/logo.png',
            'established_year' => '2010',

            // Contact info
            'phone' => '+8801714216666',
            'phone2' => '+8801829984722',
            'sales_email' => 'palash_pe@yahoo.com',
            'support_email' => 'palash_pe@yahoo.com',
            'whatsapp_number' => '+8801714216666',
            'emergency_text' => '24/7 Emergency Service: 01714-216666',
            'head_office' => 'Barik Building, 10 Sk. Mujib Road (2nd Floor), Agrabad C/A, Chattogram',
            'branch_office' => '63/1 VIP Road, Malibag, Dhaka-1217',
            'facebook_url' => 'https://www.facebook.com/innovativeengbd',
            'messenger_url' => 'https://m.me/innovativeengbd',

            // Currency
            'currency_symbol' => '৳',
            'currency_code' => 'BDT',
            'domain_currency_symbol' => '৳',
            'domain_currency_code' => 'BDT',

            // SMTP default configuration
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.mailtrap.io',
            'mail_port' => '2525',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@innovativeengbd.com',
            'mail_from_name' => 'Innovative Engineering BD',

            // Scripts & SEO
            'header_scripts' => '',
            'footer_scripts' => '',
            'google_analytics_id' => '',
            'robots_txt' => "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /login\n\nSitemap: ".url('/sitemap.xml'),
        ];

        foreach ($settings as $key => $value) {
            if (! Setting::query()->where('key', $key)->exists()) {
                Setting::set($key, $value);
            }
        }
    }
}
