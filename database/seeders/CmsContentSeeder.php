<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Section;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedLayout();
            $shared = $this->seedSharedSections();
            $this->seedPages($shared);
        });

        Page::flushContentCache();
    }

    protected function seedLayout(): void
    {
        foreach ($this->data('layout.json') as $key => $value) {
            if (! Setting::query()->where('key', $key)->exists()) {
                Setting::set($key, $value, 'layout');
            }
        }
    }

    /**
     * @return array<string, int> shared section key => section id
     */
    protected function seedSharedSections(): array
    {
        $ids = [];

        foreach ($this->data('shared_sections.json') as $key => $section) {
            $ids[$key] = Section::query()->firstOrCreate(
                ['page_id' => null, 'name' => $section['name']],
                ['type' => $section['type'], 'data' => $section['data'], 'is_active' => true],
            )->id;
        }

        return $ids;
    }

    /**
     * @param  array<string, int>  $shared
     */
    protected function seedPages(array $shared): void
    {
        foreach ($this->data('pages.json') as $definition) {
            if (Page::query()->where('slug', $definition['slug'])->exists()) {
                continue;
            }

            $page = Page::query()->create([
                'title' => $definition['title'],
                'slug' => $definition['slug'],
                'hero_background' => $definition['hero_background'] ?? null,
                'meta_title' => $definition['meta_title'] ?? null,
                'meta_description' => $definition['meta_description'] ?? null,
                'schema_type' => 'WebPage',
                'sort_order' => $definition['sort_order'] ?? 0,
                'is_active' => true,
            ]);

            foreach ($definition['sections'] as $position => $section) {
                if (isset($section['shared']) && isset($shared[$section['shared']])) {
                    $library = Section::query()->find($shared[$section['shared']]);
                    if ($library) {
                        $page->sections()->create([
                            'shared_id' => $library->id,
                            'type' => $library->type,
                            'name' => $library->name,
                            'sort_order' => $position + 1,
                            'is_active' => true,
                        ]);
                        continue;
                    }
                }

                $page->sections()->create([
                    'type' => $section['type'],
                    'name' => $section['name'],
                    'data' => $section['data'] ?? [],
                    'sort_order' => $position + 1,
                    'is_active' => true,
                ]);
            }
        }
    }

    /**
     * @return array<mixed>
     */
    protected function data(string $file): array
    {
        $path = __DIR__.'/data/'.$file;

        if (! file_exists($path)) {
            return [];
        }

        return json_decode((string) file_get_contents($path), true) ?: [];
    }
}
