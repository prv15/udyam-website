<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Config\Database;

$db = Database::connection();
$findPage = $db->prepare('SELECT id FROM pages WHERE slug = "contact" AND deleted_at IS NULL LIMIT 1');
$findPage->execute();
$pageId = (int) $findPage->fetchColumn();
if ($pageId === 0) {
    throw new RuntimeException('Contact page does not exist. Run database/seed_website_pages.php first.');
}

$findMedia = $db->prepare('SELECT id FROM media WHERE filename = :filename AND deleted_at IS NULL ORDER BY id DESC LIMIT 1');
$findMedia->execute(['filename' => 'udyam-hero-building.webp']);
$heroImage = (int) ($findMedia->fetchColumn() ?: 0);

$db->prepare(
    'UPDATE pages SET title = "Contact Udyam", excerpt = :excerpt,
     seo_title = "Contact Udyam Ventures | Delhi & Noida Offices",
     seo_description = :seo_description, template = "sectioned", status = "published"
     WHERE id = :id'
)->execute([
    'id' => $pageId,
    'excerpt' => 'Connect with Udyam Ventures at our New Delhi registered office or Noida corporate office for projects, partnerships and institutional advisory.',
    'seo_description' => 'Connect with Udyam Ventures at our New Delhi registered office or Noida corporate office for projects, partnerships and institutional advisory.',
]);

$sections = [
    'page_hero' => [
        'eyebrow' => 'Connect With Udyam',
        'heading' => 'Conversations that move ideas forward.',
        'description' => 'Whether you are exploring a project, institutional partnership, funding opportunity or long-term growth initiative, our team is ready to understand your priorities and identify the right pathway.',
        'image' => $heroImage,
        'primary_button_label' => 'View Our Offices', 'primary_button_url' => '#contact-details',
        'secondary_button_label' => 'Book Consultation', 'secondary_button_url' => '#',
    ],
    'content_blocks' => ['items' => [
        [
            'heading' => 'Start with your objective. We will bring the right expertise.',
            'content' => 'Share your organization’s current priorities, geography, stage and expected outcomes. Our integrated team will connect you with the appropriate advisory, institutional development, funding, research, compliance, digital or implementation capability.',
            'image_url' => '/uploads/media/original/home/project-advisory.webp',
        ],
    ]],
    'contact_details' => [
        'eyebrow' => 'Our Locations',
        'heading' => 'Two offices. One connected team.',
        'description' => 'Meet Udyam Ventures in the heart of New Delhi or at our corporate workspace in Noida.',
        'offices' => [
            [
                'title' => 'Registered Office',
                'address' => "1/22, Workspace, 2nd Floor, Asaf Ali Road\nNear Hamdard Headquarters\nNew Delhi – 110002",
                'map_query' => '1/22 Workspace 2nd Floor Asaf Ali Road Hamdard Headquarters New Delhi 110002',
            ],
            [
                'title' => 'Corporate Office',
                'address' => "WeWork – Berger Delhi One\nFloor 19, C-001/A2, Sector 16B\nNoida, Uttar Pradesh",
                'map_query' => 'WeWork Berger Delhi One Floor 19 Sector 16B Noida Uttar Pradesh',
            ],
        ],
        'contact_lines' => [
            ['type' => 'phone', 'label' => 'Call our team', 'value' => '+91 99051 06228', 'url' => 'tel:+919905106228'],
            ['type' => 'phone', 'label' => 'Alternative number', 'value' => '+91 92297 96727', 'url' => 'tel:+919229796727'],
            ['type' => 'email', 'label' => 'Projects & partnerships', 'value' => 'projects@udyamventures.com', 'url' => 'mailto:projects@udyamventures.com'],
            ['type' => 'email', 'label' => 'General enquiries', 'value' => 'udyamventures@gmail.com', 'url' => 'mailto:udyamventures@gmail.com'],
        ],
    ],
    'page_faqs' => [
        'heading' => 'Before You Connect',
        'items' => [
            ['question' => 'Which office should I contact?', 'answer' => 'You may contact either office. All project and partnership enquiries are routed internally to the appropriate Udyam practice team.'],
            ['question' => 'What information should I share?', 'answer' => 'A short note covering your organization, objective, geography, current stage, expected timeline and desired outcomes will help us respond effectively.'],
            ['question' => 'Can I schedule an online consultation?', 'answer' => 'Yes. Use the Book Consultation button to share your requirement and preferred contact method.'],
        ],
    ],
    'page_cta' => [
        'heading' => 'Let’s build something meaningful.',
        'description' => 'Tell us about the institution, opportunity or challenge you are working on.',
        'button_label' => 'Book Consultation', 'button_url' => '#',
    ],
];

$upsert = $db->prepare(
    'INSERT INTO page_sections (page_id, section_key, sort_order, is_enabled, data)
     VALUES (:page_id, :section_key, :sort_order, 1, :data)
     ON DUPLICATE KEY UPDATE sort_order = VALUES(sort_order), is_enabled = 1, data = VALUES(data)'
);
foreach (array_values(array_keys($sections)) as $order => $key) {
    $upsert->execute([
        'page_id' => $pageId, 'section_key' => $key, 'sort_order' => $order,
        'data' => json_encode($sections[$key], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
}

$disable = $db->prepare('UPDATE page_sections SET is_enabled = 0 WHERE page_id = :page_id AND section_key IN ("feature_grid", "profile_statements", "expertise_grid", "page_stats")');
$disable->execute(['page_id' => $pageId]);

echo "Contact page updated with editable office locations, maps and contact channels.\n";
