<?php

/*
|--------------------------------------------------------------------------
| CMS Section & Layout Schemas for Innovative Engineering BD
|--------------------------------------------------------------------------
|
| Every page is an ordered list of sections. Each section "type" maps to
| resources/views/sections/{type}.blade.php and declares the fields the
| admin editor renders. The same field format describes site-wide settings.
|
*/

$backgrounds = [
    '' => 'Default (White)',
    'sec--grey' => 'Light Grey',
    'sec--navy' => 'Navy Blue',
    'bg-soft' => 'Soft Blue',
    'bg-dark' => 'Dark',
];

$buttonFields = [
    ['key' => 'label', 'label' => 'Label', 'type' => 'text', 'width' => 'half'],
    ['key' => 'url', 'label' => 'Link', 'type' => 'url', 'width' => 'half'],
    ['key' => 'style', 'label' => 'Style', 'type' => 'select', 'width' => 'half', 'options' => [
        'btn' => 'Primary (Gold/Navy)',
        'btn btn--ghost' => 'Ghost (Border)',
        'btn btn--navy' => 'Solid Navy',
        'btn btn--white' => 'White',
        'btn btn--wa' => 'WhatsApp Green',
        'btn btn--sm' => 'Small',
    ]],
    ['key' => 'icon', 'label' => 'Icon (FontAwesome class, e.g. fas fa-phone)', 'type' => 'icon', 'width' => 'half'],
    ['key' => 'new_tab', 'label' => 'Open in new tab', 'type' => 'toggle'],
];

$buttons = fn (string $key = 'buttons', string $label = 'Buttons') => [
    'key' => $key,
    'label' => $label,
    'type' => 'repeater',
    'item_label' => 'label',
    'fields' => $buttonFields,
];

$linkFields = [
    ['key' => 'label', 'label' => 'Label', 'type' => 'text', 'width' => 'half'],
    ['key' => 'url', 'label' => 'Link', 'type' => 'url', 'width' => 'half'],
    ['key' => 'new_tab', 'label' => 'Open in new tab', 'type' => 'toggle'],
];

return [

    'backgrounds' => $backgrounds,

    'sections' => [

        'hero' => [
            'label' => 'Hero Banner & Lift Display',
            'icon' => 'fa-solid fa-panorama',
            'description' => 'Main homepage hero banner with background slides, headline, animated lift shaft and quick stats.',
            'fields' => [
                ['key' => 'anchor', 'label' => 'Anchor ID', 'type' => 'text', 'width' => 'half', 'group' => 'Settings'],
                ['key' => 'floor', 'label' => 'Floor Tag (e.g. G)', 'type' => 'text', 'width' => 'half', 'group' => 'Settings'],
                ['key' => 'badge', 'label' => 'Badge Text', 'type' => 'text', 'width' => 'half', 'group' => 'Heading'],
                ['key' => 'badge_icon', 'label' => 'Badge Icon', 'type' => 'icon', 'width' => 'half', 'group' => 'Heading'],
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'width' => 'half', 'group' => 'Heading'],
                ['key' => 'title_highlight', 'label' => 'Highlighted Words', 'type' => 'text', 'width' => 'half', 'group' => 'Heading'],
                ['key' => 'rotator_prefix', 'label' => 'Rotator Prefix (e.g. We)', 'type' => 'text', 'width' => 'half', 'group' => 'Heading'],
                ['key' => 'rotator_words', 'label' => 'Rotating Words (JSON array or comma separated)', 'type' => 'list', 'group' => 'Heading'],
                ['key' => 'rotator_suffix', 'label' => 'Rotator Suffix (e.g. for homes, offices...)', 'type' => 'text', 'group' => 'Heading'],
                ['key' => 'chips', 'label' => 'Feature Chips under headline', 'type' => 'repeater', 'item_label' => 'text', 'group' => 'Features', 'fields' => [
                    ['key' => 'icon', 'label' => 'Icon', 'type' => 'icon', 'width' => 'half'],
                    ['key' => 'text', 'label' => 'Text', 'type' => 'text', 'width' => 'half'],
                ]],
                $buttons() + ['group' => 'Actions'],
                ['key' => 'slides', 'label' => 'Background Slide Images', 'type' => 'repeater', 'item_label' => 'image', 'group' => 'Background', 'fields' => [
                    ['key' => 'image', 'label' => 'Image path', 'type' => 'image'],
                ]],
                ['key' => 'stats', 'label' => 'Quick Stats Bar', 'type' => 'repeater', 'item_label' => 'label', 'group' => 'Stats', 'fields' => [
                    ['key' => 'icon', 'label' => 'Icon', 'type' => 'icon', 'width' => 'half'],
                    ['key' => 'value', 'label' => 'Display Value (e.g. 24/7 or 100+)', 'type' => 'text', 'width' => 'half'],
                    ['key' => 'label', 'label' => 'Label', 'type' => 'text', 'width' => 'half'],
                    ['key' => 'count', 'label' => 'Animated Count (number only)', 'type' => 'text', 'width' => 'half'],
                    ['key' => 'suffix', 'label' => 'Count Suffix (e.g. +, min)', 'type' => 'text', 'width' => 'half'],
                ]],
            ],
        ],

        'about' => [
            'label' => 'About Us Section',
            'icon' => 'fa-solid fa-building',
            'description' => 'Company background, experience badge, key bullet points and direct phone contact.',
            'fields' => [
                ['key' => 'anchor', 'label' => 'Anchor ID', 'type' => 'text', 'width' => 'half'],
                ['key' => 'floor', 'label' => 'Floor Tag', 'type' => 'text', 'width' => 'half'],
                ['key' => 'floor_title', 'label' => 'Floor Label (e.g. 01 About Us)', 'type' => 'text'],
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'width' => 'half'],
                ['key' => 'title_highlight', 'label' => 'Highlighted Words', 'type' => 'text', 'width' => 'half'],
                ['key' => 'description', 'label' => 'Description paragraph', 'type' => 'textarea'],
                ['key' => 'main_image', 'label' => 'Main Photo', 'type' => 'image', 'width' => 'half'],
                ['key' => 'sub_image', 'label' => 'Secondary Photo', 'type' => 'image', 'width' => 'half'],
                ['key' => 'exp_count', 'label' => 'Experience Years (e.g. 15)', 'type' => 'text', 'width' => 'third'],
                ['key' => 'exp_suffix', 'label' => 'Experience Suffix (e.g. +)', 'type' => 'text', 'width' => 'third'],
                ['key' => 'exp_label', 'label' => 'Experience Label', 'type' => 'text', 'width' => 'third'],
                ['key' => 'points', 'label' => 'Highlights List', 'type' => 'repeater', 'item_label' => 'title', 'fields' => [
                    ['key' => 'icon', 'label' => 'Icon', 'type' => 'icon', 'width' => 'half'],
                    ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'width' => 'half'],
                    ['key' => 'description', 'label' => 'Description', 'type' => 'textarea'],
                ]],
                ['key' => 'button_label', 'label' => 'Button Label', 'type' => 'text', 'width' => 'half'],
                ['key' => 'button_url', 'label' => 'Button Link', 'type' => 'url', 'width' => 'half'],
                ['key' => 'call_label', 'label' => 'Call Subtitle (e.g. Call us any time)', 'type' => 'text', 'width' => 'half'],
                ['key' => 'call_number', 'label' => 'Phone Number (e.g. 01714-216666)', 'type' => 'text', 'width' => 'half'],
            ],
        ],

        'services' => [
            'label' => 'Services Grid',
            'icon' => 'fa-solid fa-screwdriver-wrench',
            'description' => 'Grid of 6 main engineering services with modal trigger and contact links.',
            'fields' => [
                ['key' => 'anchor', 'label' => 'Anchor ID', 'type' => 'text', 'width' => 'half'],
                ['key' => 'floor', 'label' => 'Floor Tag', 'type' => 'text', 'width' => 'half'],
                ['key' => 'floor_title', 'label' => 'Floor Label (e.g. 02 Our Services)', 'type' => 'text'],
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'width' => 'half'],
                ['key' => 'title_highlight', 'label' => 'Highlighted Words', 'type' => 'text', 'width' => 'half'],
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'textarea'],
                ['key' => 'items', 'label' => 'Services List', 'type' => 'repeater', 'item_label' => 'title', 'fields' => [
                    ['key' => 'num', 'label' => 'Number (e.g. 01)', 'type' => 'text', 'width' => 'fourth'],
                    ['key' => 'icon', 'label' => 'Icon', 'type' => 'icon', 'width' => 'fourth'],
                    ['key' => 'service_key', 'label' => 'Service Key (install, escalator, repair, amc, parts, diagnosis)', 'type' => 'text', 'width' => 'half'],
                    ['key' => 'title', 'label' => 'Service Title', 'type' => 'text', 'width' => 'half'],
                    ['key' => 'image', 'label' => 'Photo', 'type' => 'image', 'width' => 'half'],
                    ['key' => 'description', 'label' => 'Short Description', 'type' => 'textarea'],
                ]],
            ],
        ],

        'products' => [
            'label' => 'Products Catalogue',
            'icon' => 'fa-solid fa-elevator',
            'description' => 'Tabbed products showcase with categories, modal specs and pricing enquiries.',
            'fields' => [
                ['key' => 'anchor', 'label' => 'Anchor ID', 'type' => 'text', 'width' => 'half'],
                ['key' => 'floor', 'label' => 'Floor Tag', 'type' => 'text', 'width' => 'half'],
                ['key' => 'floor_title', 'label' => 'Floor Label', 'type' => 'text'],
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'width' => 'half'],
                ['key' => 'title_highlight', 'label' => 'Highlighted Words', 'type' => 'text', 'width' => 'half'],
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'textarea'],
                ['key' => 'note', 'label' => 'Bottom Note Text', 'type' => 'text'],
            ],
        ],

        'projects' => [
            'label' => 'Projects & Works Gallery',
            'icon' => 'fa-solid fa-images',
            'description' => 'Installed lift installations and portfolio photos with lightbox.',
            'fields' => [
                ['key' => 'anchor', 'label' => 'Anchor ID', 'type' => 'text', 'width' => 'half'],
                ['key' => 'floor', 'label' => 'Floor Tag', 'type' => 'text', 'width' => 'half'],
                ['key' => 'floor_title', 'label' => 'Floor Label', 'type' => 'text'],
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'width' => 'half'],
                ['key' => 'title_highlight', 'label' => 'Highlighted Words', 'type' => 'text', 'width' => 'half'],
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'textarea'],
            ],
        ],

        'why_us' => [
            'label' => 'Why Choose Us',
            'icon' => 'fa-solid fa-shield-heart',
            'description' => 'Navy blue band displaying 6 reasons building owners choose Innovative Engineering.',
            'fields' => [
                ['key' => 'anchor', 'label' => 'Anchor ID', 'type' => 'text', 'width' => 'half'],
                ['key' => 'floor', 'label' => 'Floor Tag', 'type' => 'text', 'width' => 'half'],
                ['key' => 'floor_title', 'label' => 'Floor Label', 'type' => 'text'],
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'width' => 'half'],
                ['key' => 'title_highlight', 'label' => 'Highlighted Words', 'type' => 'text', 'width' => 'half'],
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'textarea'],
                ['key' => 'cards', 'label' => 'Reason Cards', 'type' => 'repeater', 'item_label' => 'title', 'fields' => [
                    ['key' => 'num', 'label' => 'Number (e.g. 01)', 'type' => 'text', 'width' => 'third'],
                    ['key' => 'icon', 'label' => 'Icon', 'type' => 'icon', 'width' => 'third'],
                    ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'width' => 'third'],
                    ['key' => 'description', 'label' => 'Description', 'type' => 'textarea'],
                ]],
            ],
        ],

        'maintenance' => [
            'label' => 'Monthly Servicing & AMC',
            'icon' => 'fa-solid fa-calendar-check',
            'description' => 'Maintenance plan features, checklist benefits and 4-step contracting process.',
            'fields' => [
                ['key' => 'anchor', 'label' => 'Anchor ID', 'type' => 'text', 'width' => 'half'],
                ['key' => 'floor', 'label' => 'Floor Tag', 'type' => 'text', 'width' => 'half'],
                ['key' => 'floor_title', 'label' => 'Floor Label', 'type' => 'text'],
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'width' => 'half'],
                ['key' => 'title_highlight', 'label' => 'Highlighted Words', 'type' => 'text', 'width' => 'half'],
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'textarea'],
                ['key' => 'benefits', 'label' => 'Key Benefits List', 'type' => 'repeater', 'item_label' => 'text', 'fields' => [
                    ['key' => 'icon', 'label' => 'Icon', 'type' => 'icon', 'width' => 'half'],
                    ['key' => 'text', 'label' => 'Text', 'type' => 'text', 'width' => 'half'],
                ]],
                ['key' => 'plan_ribbon', 'label' => 'Plan Ribbon', 'type' => 'text', 'group' => 'Plan Card'],
                ['key' => 'plan_title', 'label' => 'Plan Title', 'type' => 'text', 'width' => 'half', 'group' => 'Plan Card'],
                ['key' => 'plan_subtitle', 'label' => 'Plan Subtitle', 'type' => 'text', 'width' => 'half', 'group' => 'Plan Card'],
                ['key' => 'plan_features', 'label' => 'Plan Checklist Items', 'type' => 'list', 'group' => 'Plan Card'],
                ['key' => 'steps_title', 'label' => 'Steps Section Title', 'type' => 'text', 'group' => 'Steps'],
                ['key' => 'steps', 'label' => 'Contract Steps', 'type' => 'repeater', 'item_label' => 'title', 'group' => 'Steps', 'fields' => [
                    ['key' => 'icon', 'label' => 'Icon', 'type' => 'icon', 'width' => 'half'],
                    ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'width' => 'half'],
                    ['key' => 'description', 'label' => 'Description', 'type' => 'textarea'],
                ]],
            ],
        ],

        'emergency_band' => [
            'label' => 'Emergency Breakdown Callout',
            'icon' => 'fa-solid fa-phone-volume',
            'description' => '24/7 urgent service banner with quick call and WhatsApp actions.',
            'fields' => [
                ['key' => 'title', 'label' => 'Title (e.g. Lift stuck or not working?)', 'type' => 'text'],
                ['key' => 'description', 'label' => 'Description', 'type' => 'text'],
                ['key' => 'phone', 'label' => 'Phone Number', 'type' => 'text', 'width' => 'half'],
                ['key' => 'phone_label', 'label' => 'Phone Display', 'type' => 'text', 'width' => 'half'],
                ['key' => 'whatsapp_url', 'label' => 'WhatsApp Link', 'type' => 'url', 'width' => 'half'],
                ['key' => 'whatsapp_label', 'label' => 'WhatsApp Label', 'type' => 'text', 'width' => 'half'],
            ],
        ],

        'contact' => [
            'label' => 'Contact & Inquiry Form',
            'icon' => 'fa-regular fa-envelope',
            'description' => 'Offices address cards, interactive contact form with department selection and Google Map.',
            'fields' => [
                ['key' => 'anchor', 'label' => 'Anchor ID', 'type' => 'text', 'width' => 'half'],
                ['key' => 'floor', 'label' => 'Floor Tag', 'type' => 'text', 'width' => 'half'],
                ['key' => 'floor_title', 'label' => 'Floor Label', 'type' => 'text'],
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'width' => 'half'],
                ['key' => 'title_highlight', 'label' => 'Highlighted Words', 'type' => 'text', 'width' => 'half'],
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'textarea'],
                ['key' => 'form_title', 'label' => 'Form Box Title', 'type' => 'text', 'width' => 'half'],
                ['key' => 'form_subtitle', 'label' => 'Form Box Subtitle', 'type' => 'text', 'width' => 'half'],
                ['key' => 'map_iframe_url', 'label' => 'Google Maps Embed URL', 'type' => 'url'],
            ],
        ],

        'page_hero' => [
            'label' => 'Inner Page Hero',
            'icon' => 'fa-solid fa-heading',
            'description' => 'Header banner for subpages with title, breadcrumb and lead text.',
            'fields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'width' => 'half'],
                ['key' => 'title_highlight', 'label' => 'Highlighted Words', 'type' => 'text', 'width' => 'half'],
                ['key' => 'eyebrow', 'label' => 'Eyebrow', 'type' => 'text', 'width' => 'half'],
                ['key' => 'lead', 'label' => 'Lead Description', 'type' => 'textarea'],
            ],
        ],

        'custom_html' => [
            'label' => 'Custom HTML Block',
            'icon' => 'fa-solid fa-code',
            'description' => 'Raw HTML snippet for custom designs.',
            'fields' => [
                ['key' => 'html', 'label' => 'HTML Code', 'type' => 'html'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Site Layout & Widget Settings (stored as JSON in settings table)
    |--------------------------------------------------------------------------
    */

    'layout' => [

        'menu_primary' => [
            'label' => 'Header Menu',
            'group' => 'Header',
            'icon' => 'fa-solid fa-bars',
            'description' => 'Top navigation links shown on desktop and mobile drawer.',
            'list' => true,
            'item_label' => 'label',
            'fields' => [
                ['key' => 'label', 'label' => 'Menu label', 'type' => 'text', 'width' => 'half'],
                ['key' => 'url', 'label' => 'Link', 'type' => 'url', 'width' => 'half'],
                ['key' => 'icon', 'label' => 'Icon (mobile menu)', 'type' => 'icon', 'width' => 'half'],
            ],
        ],

        'layout_header' => [
            'label' => 'Header Topbar & Actions',
            'group' => 'Header',
            'icon' => 'fa-solid fa-window-maximize',
            'description' => 'Topbar phone, email, address notice and emergency badge.',
            'fields' => [
                ['key' => 'phone', 'label' => 'Topbar Phone', 'type' => 'text', 'width' => 'half'],
                ['key' => 'email', 'label' => 'Topbar Email', 'type' => 'text', 'width' => 'half'],
                ['key' => 'location_notice', 'label' => 'Locations Text', 'type' => 'text', 'width' => 'half'],
                ['key' => 'emergency_badge', 'label' => 'Emergency Badge Text', 'type' => 'text', 'width' => 'half'],
                ['key' => 'cta_label', 'label' => 'Quote Button Label', 'type' => 'text', 'width' => 'half'],
                ['key' => 'cta_url', 'label' => 'Quote Button URL', 'type' => 'url', 'width' => 'half'],
            ],
        ],

        'social_links' => [
            'label' => 'Social & Messenger Links',
            'group' => 'Header',
            'icon' => 'fa-solid fa-share-nodes',
            'description' => 'Social media links shown in footer and floating quick buttons.',
            'list' => true,
            'item_label' => 'label',
            'fields' => [
                ['key' => 'label', 'label' => 'Platform Name', 'type' => 'text', 'width' => 'third'],
                ['key' => 'icon', 'label' => 'Icon (e.g. fab fa-facebook-f)', 'type' => 'icon', 'width' => 'third'],
                ['key' => 'url', 'label' => 'Link', 'type' => 'url', 'width' => 'third'],
            ],
        ],

        'menu_footer' => [
            'label' => 'Footer Menus',
            'group' => 'Footer',
            'icon' => 'fa-solid fa-sitemap',
            'description' => 'Quick links columns shown in the website footer.',
            'list' => true,
            'item_label' => 'label',
            'fields' => [
                ['key' => 'label', 'label' => 'Link label', 'type' => 'text', 'width' => 'half'],
                ['key' => 'url', 'label' => 'Link', 'type' => 'url', 'width' => 'half'],
            ],
        ],

        'layout_footer' => [
            'label' => 'Footer Content & Offices',
            'group' => 'Footer',
            'icon' => 'fa-solid fa-table-columns',
            'description' => 'Footer intro paragraph, office addresses and copyright text.',
            'fields' => [
                ['key' => 'intro', 'label' => 'Company Brief', 'type' => 'textarea'],
                ['key' => 'head_office', 'label' => 'Head Office Address', 'type' => 'textarea'],
                ['key' => 'branch_office', 'label' => 'Branch Office Address', 'type' => 'textarea'],
                ['key' => 'copyright', 'label' => 'Copyright Notice', 'type' => 'text', 'width' => 'half'],
                ['key' => 'tagline', 'label' => 'Bottom Tagline', 'type' => 'text', 'width' => 'half'],
            ],
        ],
    ],
];
