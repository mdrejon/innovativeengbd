<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\Cms;
use App\Services\MailSettings;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        AliasLoader::getInstance()->alias('Cms', Cms::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('text', fn (string $expression) => "<?php echo \\App\\Services\\Cms::text({$expression}); ?>");

        $this->applyMailSettings();
    }

    /**
     * Use the SMTP account saved in Admin → Email SMTP Setting for every outgoing email.
     */
    protected function applyMailSettings(): void
    {
        if ($this->app->runningUnitTests()) {
            return;
        }

        try {
            $settings = Setting::getAllCached();
        } catch (\Throwable) {
            return;
        }

        $this->app->make(MailSettings::class)->apply($settings);
    }
}
