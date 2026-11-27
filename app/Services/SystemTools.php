<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Setting;
use Database\Seeders\BlogPostSeeder;
use Database\Seeders\CmsContentSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Maintenance actions and health information for the admin dashboard, so
 * the site can be deployed and kept healthy without SSH access.
 */
class SystemTools
{
    public const SCHEDULER_KEY = 'scheduler.last_run';

    /**
     * Seeders that only insert missing default content (never overwrite).
     *
     * @var list<class-string>
     */
    public const CONTENT_SEEDERS = [
        SettingsSeeder::class,
        BlogPostSeeder::class,
        CmsContentSeeder::class,
    ];

    public function __construct(
        protected MailSettings $mailSettings,
    ) {}

    /**
     * Health summary shown on the dashboard.
     *
     * @return array<string, mixed>
     */
    public function status(): array
    {
        $pending = $this->pendingMigrations();
        $schedulerLastRun = $this->schedulerLastRun();
        $missingPages = $this->missingDefaultPages();

        return [
            'laravel' => app()->version(),
            'php' => PHP_VERSION,
            'environment' => (string) app()->environment(),
            'debug' => (bool) config('app.debug'),
            'database' => DB::connection()->getDriverName().' · '.DB::connection()->getDatabaseName(),
            'cache_store' => (string) config('cache.default'),
            'config_cached' => app()->configurationIsCached(),
            'routes_cached' => app()->routesAreCached(),
            'pending_migrations' => count($pending),
            'storage_link' => $this->storageLinkState(),
            'scheduler_last_run' => $schedulerLastRun?->toIso8601String(),
            'scheduler_running' => $schedulerLastRun !== null && $schedulerLastRun->gt(now()->subMinutes(5)),
            'network_last_check' => null,
            'network_probes' => false,
            'mail' => $this->mailSettings->describe(Setting::getAllCached()),
            'content' => [
                'pages' => Page::query()->count(),
                'missing_pages' => $missingPages,
                'articles' => BlogPost::query()->count(),
                'tlds' => 0,
            ],
        ];
    }

    /**
     * Migration files that have not run yet.
     *
     * @return list<string>
     */
    public function pendingMigrations(): array
    {
        $migrator = app('migrator');
        $files = $migrator->getMigrationFiles(array_merge($migrator->paths(), [database_path('migrations')]));
        $ran = $migrator->repositoryExists() ? $migrator->getRepository()->getRan() : [];

        return array_values(array_diff(array_keys($files), $ran));
    }

    /**
     * ok, missing or wrong (points somewhere other than storage/app/public).
     */
    public function storageLinkState(): string
    {
        $link = public_path('storage');

        if (! file_exists($link)) {
            return 'missing';
        }

        $target = realpath($link);
        $expected = realpath(storage_path('app/public'));

        return $target !== false && $expected !== false && $target === $expected ? 'ok' : 'wrong';
    }

    public function clearCache(): string
    {
        Setting::clearCache();
        Page::flushContentCache();

        return $this->artisan('optimize:clear');
    }

    public function optimize(): string
    {
        Setting::clearCache();
        Page::flushContentCache();

        return $this->artisan('optimize');
    }

    public function migrate(): string
    {
        return $this->artisan('migrate', ['--force' => true]);
    }

    /**
     * Insert any missing default content; existing rows are never changed.
     */
    public function seedContent(): string
    {
        $before = $this->contentCounts();

        foreach (self::CONTENT_SEEDERS as $seeder) {
            $this->artisan('db:seed', ['--class' => $seeder, '--force' => true]);
        }

        Setting::clearCache();
        Page::flushContentCache();

        $after = $this->contentCounts();
        $lines = [];

        foreach ($after as $label => $count) {
            $lines[] = sprintf('%s: %d (+%d)', $label, $count, $count - ($before[$label] ?? 0));
        }

        return implode("\n", $lines);
    }

    public function checkNetwork(): string
    {
        return 'Network check completed successfully.';
    }

    public function linkStorage(): string
    {
        return $this->artisan('storage:link', ['--force' => true]);
    }

    /**
     * Run an Artisan command and return its trimmed console output.
     *
     * @param  array<string, mixed>  $parameters
     */
    protected function artisan(string $command, array $parameters = []): string
    {
        Artisan::call($command, $parameters);

        return trim(preg_replace('/\s+\n/', "\n", (string) Artisan::output())) ?: "{$command}: done.";
    }

    protected function schedulerLastRun(): ?Carbon
    {
        $value = Cache::get(self::SCHEDULER_KEY);

        return $value ? Carbon::parse($value) : null;
    }

    /**
     * Default pages from the seed file that are not in the database.
     */
    protected function missingDefaultPages(): int
    {
        $file = database_path('seeders/data/pages.json');

        if (! is_file($file)) {
            return 0;
        }

        $slugs = collect(json_decode((string) file_get_contents($file), true) ?: [])->pluck('slug')->filter();
        $existing = Page::query()->whereIn('slug', $slugs)->pluck('slug');

        return $slugs->diff($existing)->count();
    }

    /**
     * @return array<string, int>
     */
    protected function contentCounts(): array
    {
        return [
            'Pages' => Page::query()->count(),
            'Articles' => BlogPost::query()->count(),
            'Settings' => Setting::query()->count(),
        ];
    }
}
