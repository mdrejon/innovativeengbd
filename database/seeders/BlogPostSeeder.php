<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'category' => 'Lift Maintenance',
                'title' => 'Why Monthly Elevator Servicing is Essential for High-Rise Safety',
                'slug' => 'why-monthly-elevator-servicing-essential',
                'excerpt' => 'Regular monthly servicing prevents unexpected lift breakdowns, reduces expensive motor replacements, and ensures passenger safety in commercial and residential buildings.',
                'content' => '<p>Lift maintenance is not just a regulatory checklist; it is an absolute necessity for occupant safety and building convenience. A regular 30-point monthly inspection ensures pulleys, brake shoes, ropes, door sensors and motor oil levels are thoroughly verified.</p>',
                'author_name' => 'Engr. Palash',
                'read_time' => '4 min read',
                'is_published' => true,
                'published_at' => now(),
            ],
            [
                'category' => 'Technology & Design',
                'title' => 'Machine Room Less (MRL) vs Traction Lifts: Which Should You Choose?',
                'slug' => 'mrl-vs-traction-lifts-guide',
                'excerpt' => 'Explore the advantages of MRL (Machine-Room-Less) elevators for modern apartments and why architects prefer them for rooftop aesthetics and space saving.',
                'content' => '<p>Modern architectural layouts in Bangladesh are rapidly adopting gearless MRL elevators. By eliminating the penthouse motor room atop the building, developers gain usable rooftop space while lowering electricity consumption with VVVF drives.</p>',
                'author_name' => 'Innovative Engineering Team',
                'read_time' => '5 min read',
                'is_published' => true,
                'published_at' => now()->subDays(3),
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::query()->firstOrCreate(['slug' => $post['slug']], $post);
        }
    }
}
