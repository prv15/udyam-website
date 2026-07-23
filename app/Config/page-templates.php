<?php

declare(strict_types=1);

return [
    'default' => [
        'label' => 'Standard Content Page',
        'sections' => [],
    ],
    'sectioned' => [
        'label' => 'Flexible Sectioned Page',
        'sections' => [
            'page_hero' => [
                'label' => 'Page Hero',
                'description' => 'Page introduction, headline, supporting copy and hero image.',
                'fields' => [
                    'eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
                    'heading' => ['label' => 'Heading', 'type' => 'text'],
                    'description' => ['label' => 'Description', 'type' => 'textarea'],
                    'image' => ['label' => 'Hero Image', 'type' => 'media'],
                    'primary_button_label' => ['label' => 'Primary Button Label', 'type' => 'text'],
                    'primary_button_url' => ['label' => 'Primary Button URL', 'type' => 'text'],
                    'secondary_button_label' => ['label' => 'Secondary Button Label', 'type' => 'text'],
                    'secondary_button_url' => ['label' => 'Secondary Button URL', 'type' => 'text'],
                ],
            ],
            'content_blocks' => [
                'label' => 'Content Blocks',
                'description' => 'Repeatable content blocks for the main page story.',
                'fields' => [
                    'items' => ['label' => 'Blocks', 'type' => 'repeater', 'fields' => [
                        'heading' => 'Heading', 'content' => 'Content', 'image_url' => 'Image URL',
                    ]],
                ],
            ],
            'feature_grid' => [
                'label' => 'Feature Grid',
                'description' => 'Repeatable feature or benefit cards.',
                'fields' => [
                    'heading' => ['label' => 'Section Heading', 'type' => 'text'],
                    'description' => ['label' => 'Section Description', 'type' => 'textarea'],
                    'items' => ['label' => 'Features', 'type' => 'repeater', 'fields' => [
                        'title' => 'Title', 'description' => 'Description', 'icon' => 'Icon Name',
                    ]],
                ],
            ],
            'page_stats' => [
                'label' => 'Statistics',
                'description' => 'Optional impact statistics.',
                'fields' => [
                    'items' => ['label' => 'Statistics', 'type' => 'repeater', 'fields' => [
                        'value' => 'Value', 'label' => 'Label',
                    ]],
                ],
            ],
            'page_faqs' => [
                'label' => 'Frequently Asked Questions',
                'description' => 'Page-specific questions and answers.',
                'fields' => [
                    'heading' => ['label' => 'Section Heading', 'type' => 'text'],
                    'items' => ['label' => 'Questions', 'type' => 'repeater', 'fields' => [
                        'question' => 'Question', 'answer' => 'Answer',
                    ]],
                ],
            ],
            'page_cta' => [
                'label' => 'Call to Action',
                'description' => 'Final conversion section.',
                'fields' => [
                    'heading' => ['label' => 'Heading', 'type' => 'text'],
                    'description' => ['label' => 'Description', 'type' => 'textarea'],
                    'button_label' => ['label' => 'Button Label', 'type' => 'text'],
                    'button_url' => ['label' => 'Button URL', 'type' => 'text'],
                ],
            ],
        ],
    ],
    'home' => [
        'label' => 'Home Page',
        'sections' => [
            'header' => [
                'label' => 'Header & Navigation',
                'description' => 'Logo, primary navigation and header calls to action.',
                'fields' => [
                    'logo' => ['label' => 'Logo', 'type' => 'media'],
                    'navigation' => ['label' => 'Navigation Links', 'type' => 'repeater', 'fields' => [
                        'label' => 'Label', 'url' => 'URL',
                    ]],
                    'resources_label' => ['label' => 'Resources Menu Label', 'type' => 'text'],
                    'resource_links' => ['label' => 'Resources Dropdown Links', 'type' => 'repeater', 'fields' => [
                        'label' => 'Label', 'description' => 'Short Description', 'url' => 'URL', 'icon' => 'Icon / Symbol',
                    ]],
                    'portal_label' => ['label' => 'Portal Button Label', 'type' => 'text'],
                    'portal_url' => ['label' => 'Portal Button URL', 'type' => 'text'],
                    'consultation_label' => ['label' => 'Consultation Button Label', 'type' => 'text'],
                    'consultation_url' => ['label' => 'Consultation Button URL', 'type' => 'text'],
                ],
            ],
            'hero' => [
                'label' => 'Hero Section',
                'description' => 'Main headline, introduction, calls to action and hero imagery.',
                'fields' => [
                    'eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
                    'heading' => ['label' => 'Primary Heading', 'type' => 'text'],
                    'accent_heading' => ['label' => 'Gold Accent Heading', 'type' => 'text'],
                    'description' => ['label' => 'Description', 'type' => 'textarea'],
                    'primary_cta_label' => ['label' => 'Primary Button Label', 'type' => 'text'],
                    'primary_cta_url' => ['label' => 'Primary Button URL', 'type' => 'text'],
                    'secondary_cta_label' => ['label' => 'Secondary Button Label', 'type' => 'text'],
                    'secondary_cta_url' => ['label' => 'Secondary Button URL', 'type' => 'text'],
                    'hero_image' => ['label' => 'Building/Hero Image', 'type' => 'media'],
                    'diagram_image' => ['label' => 'Strategy Diagram Image', 'type' => 'media'],
                    'capability_cards' => ['label' => 'Strategy Capability Cards', 'type' => 'repeater', 'fields' => [
                        'title' => 'Title', 'description' => 'Description', 'icon' => 'Icon / Symbol',
                    ]],
                ],
            ],
            'audiences' => [
                'label' => 'Audience Cards',
                'description' => 'Government, CSR, MSME, startups, universities and skill development cards.',
                'fields' => [
                    'items' => ['label' => 'Cards', 'type' => 'repeater', 'fields' => [
                        'title' => 'Title', 'icon' => 'Icon Name', 'url' => 'URL',
                    ]],
                ],
            ],
            'impact_stats' => [
                'label' => 'Impact Statistics',
                'description' => 'Projects, funding, institutions, states, advisors and experience figures.',
                'fields' => [
                    'items' => ['label' => 'Statistics', 'type' => 'repeater', 'fields' => [
                        'value' => 'Value', 'label' => 'Label', 'icon' => 'Icon Name',
                    ]],
                ],
            ],
            'tenders' => [
                'label' => 'Notices & Tenders',
                'description' => 'Introductory text and display settings; entries come from the Tenders module.',
                'fields' => [
                    'heading' => ['label' => 'Heading', 'type' => 'text'],
                    'description' => ['label' => 'Description', 'type' => 'textarea'],
                    'button_label' => ['label' => 'Button Label', 'type' => 'text'],
                    'limit' => ['label' => 'Number of Entries', 'type' => 'number'],
                    'artwork' => ['label' => 'Section Artwork', 'type' => 'media'],
                ],
            ],
            'journey' => [
                'label' => 'Our Journey With You',
                'description' => 'Ordered steps from idea through impact.',
                'fields' => [
                    'heading' => ['label' => 'Heading', 'type' => 'text'],
                    'items' => ['label' => 'Journey Steps', 'type' => 'repeater', 'fields' => [
                        'title' => 'Title', 'description' => 'Description', 'icon' => 'Icon Name',
                    ]],
                ],
            ],
            'why_udyam' => [
                'label' => 'Why Udyam Ventures?',
                'description' => 'Key reasons and supporting artwork.',
                'fields' => [
                    'heading' => ['label' => 'Heading', 'type' => 'text'],
                    'items' => ['label' => 'Reasons', 'type' => 'repeater', 'fields' => [
                        'text' => 'Reason',
                    ]],
                    'artwork' => ['label' => 'Artwork', 'type' => 'media'],
                ],
            ],
            'services' => [
                'label' => 'Core Services',
                'description' => 'Heading and display count; cards come from the Services module.',
                'fields' => [
                    'heading' => ['label' => 'Heading', 'type' => 'text'],
                    'limit' => ['label' => 'Number of Services', 'type' => 'number'],
                ],
            ],
            'focus_areas' => [
                'label' => 'Focus Areas',
                'description' => 'Heading and display count; entries come from the Focus Areas module.',
                'fields' => [
                    'heading' => ['label' => 'Heading', 'type' => 'text'],
                    'limit' => ['label' => 'Number of Focus Areas', 'type' => 'number'],
                    'button_label' => ['label' => 'Button Label', 'type' => 'text'],
                    'button_url' => ['label' => 'Button URL', 'type' => 'text'],
                ],
            ],
            'ecosystem' => [
                'label' => 'Digital Ecosystem',
                'description' => 'Corporate website, knowledge centre and client portal pathway.',
                'fields' => [
                    'heading' => ['label' => 'Heading', 'type' => 'text'],
                    'tagline' => ['label' => 'Tagline', 'type' => 'text'],
                    'items' => ['label' => 'Platforms', 'type' => 'repeater', 'fields' => [
                        'title' => 'Title', 'url' => 'URL', 'icon' => 'Icon Name',
                    ]],
                    'button_label' => ['label' => 'Button Label', 'type' => 'text'],
                    'button_url' => ['label' => 'Button URL', 'type' => 'text'],
                ],
            ],
            'featured_insight' => [
                'label' => 'Featured Insight',
                'description' => 'Highlighted research report or publication.',
                'fields' => [
                    'kicker' => ['label' => 'Kicker', 'type' => 'text'],
                    'heading' => ['label' => 'Heading', 'type' => 'text'],
                    'description' => ['label' => 'Description', 'type' => 'textarea'],
                    'button_label' => ['label' => 'Button Label', 'type' => 'text'],
                    'button_url' => ['label' => 'Download/Detail URL', 'type' => 'text'],
                    'cover_image' => ['label' => 'Report Cover', 'type' => 'media'],
                ],
            ],
            'ecosystem_network' => [
                'label' => 'Institutional Ecosystem',
                'description' => 'Connected sectors and a featured sector impact story.',
                'fields' => [
                    'eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
                    'heading' => ['label' => 'Heading', 'type' => 'text'],
                    'description' => ['label' => 'Description', 'type' => 'textarea'],
                    'items' => ['label' => 'Connected Sectors', 'type' => 'repeater', 'fields' => [
                        'title' => 'Sector', 'description' => 'Short Description', 'icon' => 'Icon Name',
                    ]],
                    'featured_title' => ['label' => 'Featured Sector Title', 'type' => 'text'],
                    'featured_description' => ['label' => 'Featured Sector Description', 'type' => 'textarea'],
                    'featured_stats' => ['label' => 'Featured Statistics', 'type' => 'repeater', 'fields' => [
                        'value' => 'Value', 'label' => 'Label',
                    ]],
                ],
            ],
            'knowledge_centre' => [
                'label' => 'Knowledge Centre',
                'description' => 'Section copy and latest insight count; cards come from Blog.',
                'fields' => [
                    'eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
                    'heading' => ['label' => 'Heading', 'type' => 'text'],
                    'description' => ['label' => 'Description', 'type' => 'textarea'],
                    'limit' => ['label' => 'Number of Latest Insights', 'type' => 'number'],
                ],
            ],
            'partners' => [
                'label' => 'Partners',
                'description' => 'Heading and display count; logos come from the Partners module.',
                'fields' => [
                    'heading' => ['label' => 'Heading', 'type' => 'text'],
                    'limit' => ['label' => 'Number of Partners', 'type' => 'number'],
                ],
            ],
            'closing_cta' => [
                'label' => 'Closing Call to Action',
                'description' => 'Final conversion message above the footer.',
                'fields' => [
                    'heading' => ['label' => 'Heading', 'type' => 'text'],
                    'description' => ['label' => 'Description', 'type' => 'textarea'],
                    'button_label' => ['label' => 'Button Label', 'type' => 'text'],
                    'button_url' => ['label' => 'Button URL', 'type' => 'text'],
                ],
            ],
            'footer' => [
                'label' => 'Footer & Newsletter',
                'description' => 'Footer brand content, navigation columns, social profiles, newsletter and legal links.',
                'fields' => [
                    'brand_heading' => ['label' => 'Brand Heading', 'type' => 'text'],
                    'description' => ['label' => 'Brand Description', 'type' => 'textarea'],
                    'social_links' => ['label' => 'Social Profiles', 'type' => 'repeater', 'fields' => [
                        'label' => 'Platform', 'url' => 'Profile URL', 'icon' => 'Icon Name',
                    ]],
                    'services_heading' => ['label' => 'Services Column Heading', 'type' => 'text'],
                    'service_links' => ['label' => 'Services Links', 'type' => 'repeater', 'fields' => [
                        'label' => 'Label', 'url' => 'URL',
                    ]],
                    'company_heading' => ['label' => 'Company Column Heading', 'type' => 'text'],
                    'company_links' => ['label' => 'Company Links', 'type' => 'repeater', 'fields' => [
                        'label' => 'Label', 'url' => 'URL',
                    ]],
                    'resources_heading' => ['label' => 'Resources Column Heading', 'type' => 'text'],
                    'resource_links' => ['label' => 'Resources Links', 'type' => 'repeater', 'fields' => [
                        'label' => 'Label', 'url' => 'URL',
                    ]],
                    'newsletter_heading' => ['label' => 'Newsletter Heading', 'type' => 'text'],
                    'newsletter_text' => ['label' => 'Newsletter Text', 'type' => 'textarea'],
                    'copyright' => ['label' => 'Copyright Text', 'type' => 'text'],
                    'legal_links' => ['label' => 'Legal Links', 'type' => 'repeater', 'fields' => [
                        'label' => 'Label', 'url' => 'URL',
                    ]],
                ],
            ],
        ],
    ],
];
