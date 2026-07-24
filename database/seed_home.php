<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Config\Database;

$db = Database::connection();
$home = $db->query(
    "SELECT id FROM pages WHERE template = 'home' AND deleted_at IS NULL ORDER BY status = 'published' DESC, id DESC LIMIT 1"
)->fetch();

if (!$home) {
    throw new RuntimeException('Create a page using the Home Page template before running this setup.');
}

$pageId = (int) $home['id'];
$userId = (int) ($db->query("SELECT id FROM users WHERE user_type = 'admin' AND deleted_at IS NULL ORDER BY id LIMIT 1")->fetchColumn() ?: 0);

$assets = [
    'logo' => ['udyam-ventures-logo-cropped.png', 'Udyam Ventures Primary Logo', 'Udyam Ventures'],
    'hero' => ['udyam-hero-building.webp', 'Udyam Hero Building', 'Modern institutional building'],
    'mark' => ['udyam-mark.png', 'Udyam Strategy Mark', 'Udyam Ventures strategy mark'],
    'report' => ['featured-report.webp', 'Featured Research Report', 'Featured Udyam research report'],
    'advisory' => ['project-advisory.webp', 'Project Advisory', 'Project advisory meeting'],
    'institution' => ['institutional-development.webp', 'Institutional Development', 'Institutional development'],
    'funding' => ['funding.webp', 'Funding and Resource Mobilization', 'Funding and resource mobilization'],
];

$mediaIds = [];
$findMedia = $db->prepare('SELECT id FROM media WHERE folder = :folder AND filename = :filename AND deleted_at IS NULL LIMIT 1');
$insertMedia = $db->prepare(
    'INSERT INTO media
    (filename, original_name, mime_type, extension, disk, folder, file_size, width, height,
     original_width, original_height, title, alt_text, uploaded_by)
    VALUES
    (:filename, :original_name, :mime_type, :extension, "local", "original/home", :file_size, :width, :height,
     :original_width, :original_height, :title, :alt_text, :uploaded_by)'
);

foreach ($assets as $key => [$filename, $title, $alt]) {
    $findMedia->execute(['folder' => 'original/home', 'filename' => $filename]);
    $id = (int) ($findMedia->fetchColumn() ?: 0);
    if ($id === 0) {
        $path = dirname(__DIR__) . '/uploads/media/original/home/' . $filename;
        $dimensions = @getimagesize($path) ?: [null, null, null, null, null, null, null, mime_content_type($path)];
        $insertMedia->execute([
            'filename' => $filename,
            'original_name' => $filename,
            'mime_type' => $dimensions['mime'] ?? mime_content_type($path),
            'extension' => pathinfo($filename, PATHINFO_EXTENSION),
            'file_size' => filesize($path),
            'width' => $dimensions[0],
            'height' => $dimensions[1],
            'original_width' => $dimensions[0],
            'original_height' => $dimensions[1],
            'title' => $title,
            'alt_text' => $alt,
            'uploaded_by' => $userId ?: null,
        ]);
        $id = (int) $db->lastInsertId();
    }
    $mediaIds[$key] = $id;
}

$defaults = [
    'header' => [
        'logo' => $mediaIds['logo'],
        'navigation' => [
            ['label' => 'About Us', 'url' => '/about-us'],
            ['label' => 'Services', 'url' => '/#services'],
            ['label' => 'Focus Areas', 'url' => '/#focus-areas'],
            ['label' => 'Contact', 'url' => '/contact'],
        ],
        'resources_label' => 'Resources',
        'resource_links' => [
            ['label' => 'Notice & Tender', 'description' => 'Latest opportunities and notices', 'url' => '/#tenders', 'icon' => '▤'],
            ['label' => 'Knowledge Center', 'description' => 'Research, insights and ideas', 'url' => '/#knowledge-centre', 'icon' => '▥'],
            ['label' => 'Downloads', 'description' => 'Reports, documents and publications', 'url' => '/#featured-insight', 'icon' => '↓'],
            ['label' => 'Resource Center', 'description' => 'Tools and institutional resources', 'url' => '/#resource-center', 'icon' => '◇'],
        ],
        'portal_label' => 'Customer Portal', 'portal_url' => '/customer/login',
        'consultation_label' => 'Book Consultation', 'consultation_url' => '/contact',
    ],
    'hero' => [
        'eyebrow' => 'National Knowledge & Institutional Development Partner',
        'heading' => 'Building Strong Organizations.',
        'accent_heading' => 'Creating Sustainable Impact.',
        'description' => 'Empowering institutions, businesses, startups, universities and development organizations through strategic advisory, institutional strengthening and digital transformation.',
        'primary_cta_label' => 'Explore Services', 'primary_cta_url' => '/#services',
        'secondary_cta_label' => 'Partner With Us', 'secondary_cta_url' => '/contact',
        'hero_image' => $mediaIds['hero'], 'diagram_image' => $mediaIds['mark'],
        'capability_cards' => [
            ['title' => 'Knowledge', 'description' => 'Research-driven insights for better decisions.', 'icon' => '▥'],
            ['title' => 'Strategy', 'description' => 'Roadmap for growth and transformation.', 'icon' => '◎'],
            ['title' => 'Funding', 'description' => 'Connecting opportunities with the right resources.', 'icon' => '₹'],
            ['title' => 'Execution', 'description' => 'End-to-end implementation support.', 'icon' => '⚙'],
            ['title' => 'Impact', 'description' => 'Measurable outcomes for a better tomorrow.', 'icon' => '↗'],
        ],
    ],
    'audiences' => ['items' => [
        ['title' => 'Government Projects', 'icon' => 'government', 'url' => '/#focus-areas'],
        ['title' => 'CSR Initiatives', 'icon' => 'csr', 'url' => '/#focus-areas'],
        ['title' => 'MSME Growth', 'icon' => 'msme', 'url' => '/#focus-areas'],
        ['title' => 'Startups & Innovation', 'icon' => 'startup', 'url' => '/#focus-areas'],
        ['title' => 'Universities & Research', 'icon' => 'university', 'url' => '/#knowledge-centre'],
        ['title' => 'Skill Development', 'icon' => 'skills', 'url' => '/#services'],
    ]],
    'impact_stats' => ['items' => [
        ['value' => '500+', 'label' => 'Projects Delivered', 'icon' => '◎'],
        ['value' => '₹2500 Cr+', 'label' => 'Funding Facilitated', 'icon' => '▥'],
        ['value' => '200+', 'label' => 'Institutions Strengthened', 'icon' => '◇'],
        ['value' => '25+', 'label' => 'States Impacted', 'icon' => '⌖'],
        ['value' => '100+', 'label' => 'Expert Advisors', 'icon' => '♧'],
        ['value' => '10+', 'label' => 'Years of Excellence', 'icon' => '♙'],
    ]],
    'tenders' => [
        'heading' => 'Notices & Tenders',
        'description' => 'Stay updated with the latest government tenders, notices and opportunities.',
        'button_label' => 'View All Tenders', 'limit' => 5, 'artwork' => $mediaIds['mark'],
    ],
    'journey' => [
        'heading' => 'Our Journey With You',
        'items' => [
            ['title' => 'Idea', 'description' => 'Understanding the vision', 'icon' => 'idea'],
            ['title' => 'Strategy', 'description' => 'Crafting the right approach', 'icon' => 'strategy'],
            ['title' => 'Planning', 'description' => 'Roadmap for success', 'icon' => 'planning'],
            ['title' => 'Funding', 'description' => 'Mobilising the right resources', 'icon' => 'funding'],
            ['title' => 'Execution', 'description' => 'Driving plans into action', 'icon' => 'execution'],
            ['title' => 'Monitoring', 'description' => 'Tracking progress & performance', 'icon' => 'monitoring'],
            ['title' => 'Impact', 'description' => 'Creating lasting performance', 'icon' => 'impact'],
        ],
    ],
    'why_udyam' => [
        'heading' => 'Why Udyam Ventures?',
        'items' => [
            ['text' => 'Deep domain expertise across sectors'],
            ['text' => 'Strong network with government and industry'],
            ['text' => 'End-to-end solutions under one roof'],
            ['text' => 'Data-driven approach for better outcomes'],
            ['text' => 'Commitment to transparency and accountability'],
        ],
        'artwork' => $mediaIds['mark'],
    ],
    'services' => ['heading' => 'Our Core Services', 'limit' => 6],
    'focus_areas' => ['heading' => 'Focus Areas', 'limit' => 8, 'button_label' => 'Explore All Focus Areas', 'button_url' => '/#focus-areas'],
    'ecosystem' => [
        'heading' => 'Our Digital Ecosystem', 'tagline' => 'Integrated. Intelligent. Impactful.',
        'items' => [
            ['title' => 'Corporate Website', 'url' => '/', 'icon' => 'website'],
            ['title' => 'Knowledge Centre', 'url' => '/#knowledge-centre', 'icon' => 'knowledge'],
            ['title' => 'Client Portal', 'url' => '/customer/login', 'icon' => 'portal'],
        ],
        'button_label' => 'Explore Digital Platforms', 'button_url' => '/#knowledge-centre',
    ],
    'featured_insight' => [
        'kicker' => 'Research Report', 'heading' => "India's CSR Landscape 2024-25",
        'description' => 'Key trends, opportunities and impact analysis for institutions and enterprises.',
        'button_label' => 'Download Report', 'button_url' => '/#knowledge-centre',
        'cover_image' => $mediaIds['report'],
    ],
    'ecosystem_network' => [
        'eyebrow' => 'Connected for greater impact',
        'heading' => 'An Institutional Ecosystem Built Around Outcomes',
        'description' => 'We connect public institutions, enterprises, knowledge partners and communities to move ideas from strategy to measurable impact.',
        'items' => [
            ['title' => 'Public Sector', 'description' => 'Policy and programme enablement', 'icon' => '🏛'],
            ['title' => 'MSMEs', 'description' => 'Growth and market readiness', 'icon' => '◎'],
            ['title' => 'Universities', 'description' => 'Research and innovation', 'icon' => '◇'],
            ['title' => 'CSR', 'description' => 'Purpose-led programmes', 'icon' => '♡'],
            ['title' => 'Startups', 'description' => 'Enterprise acceleration', 'icon' => '↗'],
            ['title' => 'Communities', 'description' => 'Inclusive development', 'icon' => '◉'],
        ],
        'featured_title' => 'From institutional capacity to national impact',
        'featured_description' => 'A single platform for advisory, funding, execution, monitoring and knowledge.',
        'featured_stats' => [
            ['value' => '360°', 'label' => 'Delivery Model'],
            ['value' => '25+', 'label' => 'States'],
            ['value' => '500+', 'label' => 'Projects'],
        ],
    ],
    'knowledge_centre' => [
        'eyebrow' => 'Knowledge Centre',
        'heading' => 'Ideas, Research and Practical Intelligence',
        'description' => 'Explore analysis and field insight designed to help institutions make stronger decisions.',
        'limit' => 4,
    ],
    'partners' => ['heading' => 'Our Partners', 'limit' => 10],
    'closing_cta' => [
        'heading' => 'Ready to build stronger institutions?',
        'description' => 'Let us shape a practical roadmap for sustainable growth and measurable impact.',
        'button_label' => 'Start a Conversation', 'button_url' => '/contact',
    ],
    'footer' => [
        'brand_heading' => 'Udyam Ventures',
        'description' => 'Business & Project Advisors helping governments, institutions and enterprises create measurable impact.',
        'social_links' => [
            ['label' => 'LinkedIn', 'url' => 'https://www.linkedin.com', 'icon' => 'linkedin'],
            ['label' => 'Facebook', 'url' => 'https://www.facebook.com', 'icon' => 'facebook'],
            ['label' => 'Instagram', 'url' => 'https://www.instagram.com', 'icon' => 'instagram'],
            ['label' => 'YouTube', 'url' => 'https://www.youtube.com', 'icon' => 'youtube'],
        ],
        'services_heading' => 'Services',
        'service_links' => [
            ['label' => 'Project Funding', 'url' => '/services'],
            ['label' => 'Business Advisory', 'url' => '/services'],
            ['label' => 'Research', 'url' => '/services'],
            ['label' => 'PMU Support', 'url' => '/services'],
            ['label' => 'Capacity Building', 'url' => '/services'],
        ],
        'company_heading' => 'Company',
        'company_links' => [
            ['label' => 'About Us', 'url' => '/about'],
            ['label' => 'Focus Areas', 'url' => '/focus-areas'],
            ['label' => 'Insights', 'url' => '/knowledge-centre'],
            ['label' => 'Partners', 'url' => '/#partners'],
            ['label' => 'Contact', 'url' => '/contact'],
        ],
        'resources_heading' => 'Resources',
        'resource_links' => [
            ['label' => 'Articles', 'url' => '/knowledge-centre'],
            ['label' => 'Research Reports', 'url' => '/downloads'],
            ['label' => 'Case Studies', 'url' => '/resource-center'],
            ['label' => 'Downloads', 'url' => '/downloads'],
            ['label' => 'FAQs', 'url' => '/faqs'],
        ],
        'newsletter_heading' => 'Stay Updated',
        'newsletter_text' => 'Receive funding opportunities, research reports and policy updates directly in your inbox.',
        'copyright' => '© ' . date('Y') . ' Udyam Ventures. All Rights Reserved.',
        'legal_links' => [
            ['label' => 'Privacy Policy', 'url' => '/privacy-policy'],
            ['label' => 'Terms', 'url' => '/terms'],
        ],
    ],
];

$mergeMissing = static function (array $current, array $fallback) use (&$mergeMissing): array {
    foreach ($fallback as $key => $value) {
        if (!array_key_exists($key, $current) || $current[$key] === '' || $current[$key] === null || $current[$key] === []) {
            $current[$key] = $value;
        } elseif (is_array($value) && is_array($current[$key]) && !array_is_list($value) && !array_is_list($current[$key])) {
            $current[$key] = $mergeMissing($current[$key], $value);
        }
    }
    return $current;
};

$findSection = $db->prepare('SELECT data FROM page_sections WHERE page_id = :page_id AND section_key = :section_key LIMIT 1');
$saveSection = $db->prepare(
    'INSERT INTO page_sections (page_id, section_key, sort_order, is_enabled, data)
     VALUES (:page_id, :section_key, :sort_order, 1, :data)
     ON DUPLICATE KEY UPDATE data = VALUES(data), is_enabled = 1'
);
$sort = 0;
foreach ($defaults as $sectionKey => $defaultData) {
    $findSection->execute(['page_id' => $pageId, 'section_key' => $sectionKey]);
    $current = json_decode((string) ($findSection->fetchColumn() ?: '{}'), true);
    $data = $mergeMissing(is_array($current) ? $current : [], $defaultData);
    if ($sectionKey === 'header' && ($data['portal_label'] ?? '') === 'Client Portal') {
        $data['portal_label'] = 'Customer Portal';
    }
    if ($sectionKey === 'header') {
        $navigationRoutes = [
            'about us' => '/about-us', 'services' => '/services',
            'focus areas' => '/focus-areas', 'contact' => '/contact',
        ];
        if (isset($data['navigation']) && is_array($data['navigation'])) {
            foreach ($data['navigation'] as &$navigationItem) {
                $label = strtolower(trim((string) ($navigationItem['label'] ?? '')));
                if (isset($navigationRoutes[$label])) $navigationItem['url'] = $navigationRoutes[$label];
            }
            unset($navigationItem);
        }
        $resourceRoutes = [
            'notice & tender' => '/notice-tender', 'notice & tenders' => '/notice-tender',
            'knowledge center' => '/knowledge-centre', 'knowledge centre' => '/knowledge-centre',
            'downloads' => '/downloads', 'resource center' => '/resource-center',
        ];
        if (isset($data['resource_links']) && is_array($data['resource_links'])) {
            foreach ($data['resource_links'] as &$resourceItem) {
                $label = strtolower(trim((string) ($resourceItem['label'] ?? '')));
                if (isset($resourceRoutes[$label])) $resourceItem['url'] = $resourceRoutes[$label];
            }
            unset($resourceItem);
        }
    }
    if ($sectionKey === 'hero' && ($data['primary_cta_url'] ?? '') === '/#services') {
        $data['primary_cta_url'] = '/services';
    }
    if ($sectionKey === 'audiences') {
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as &$audienceItem) {
                if (($audienceItem['url'] ?? '') === '/#services') $audienceItem['url'] = '/services';
            }
            unset($audienceItem);
        }
    }
    $saveSection->execute([
        'page_id' => $pageId, 'section_key' => $sectionKey, 'sort_order' => $sort++,
        'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
}

$moduleDefaults = [
    'services' => [
        ['Project Advisory', 'project-advisory', ['icon' => '◇', 'summary' => 'Concept-to-completion support for government, CSR and private projects.', 'content' => 'Strategy, planning, implementation support and monitoring.', 'image' => media_url(['folder' => 'original/home', 'filename' => 'project-advisory.webp'])]],
        ['Funding & Resource Mobilization', 'funding-resource-mobilization', ['icon' => '◈', 'summary' => 'Connecting high-impact opportunities with the right resources.', 'content' => 'Funding strategy, programme structuring and stakeholder alignment.', 'image' => media_url(['folder' => 'original/home', 'filename' => 'funding.webp'])]],
        ['Institutional Development', 'institutional-development', ['icon' => '🏛', 'summary' => 'Strengthening systems, processes and governance for long-term growth.', 'content' => 'Institution design, process improvement and capacity enhancement.', 'image' => media_url(['folder' => 'original/home', 'filename' => 'institutional-development.webp'])]],
        ['Research & Insights', 'research-insights', ['icon' => '◎', 'summary' => 'Evidence-led research and policy insight for strategic decisions.', 'content' => 'Research, assessment, knowledge products and advisory.', 'image' => media_url(['folder' => 'original/home', 'filename' => 'featured-report.webp'])]],
        ['Digital Transformation', 'digital-transformation', ['icon' => '⌘', 'summary' => 'Technology solutions for efficiency, transparency and scale.', 'content' => 'Digital platforms, workflow systems and transformation roadmaps.', 'image' => media_url(['folder' => 'original/home', 'filename' => 'udyam-hero-building.webp'])]],
        ['Training & Capacity Building', 'training-capacity-building', ['icon' => '♧', 'summary' => 'Building skills and capabilities for people and institutions.', 'content' => 'Training design, workshops, mentoring and implementation support.', 'image' => media_url(['folder' => 'original/home', 'filename' => 'project-advisory.webp'])]],
    ],
    'focus-areas' => [
        ['Government & Public Sector', 'government-public-sector', ['icon' => 'government', 'summary' => 'Public systems and programme delivery', 'content' => 'Institutional and programme support for public outcomes.']],
        ['MSME & Industries', 'msme-industries', ['icon' => 'industry', 'summary' => 'Enterprise growth and competitiveness', 'content' => 'Advisory and ecosystem development for industry.']],
        ['Skill Development', 'skill-development', ['icon' => 'skills', 'summary' => 'Workforce and institutional capability', 'content' => 'Demand-led skills and capacity building.']],
        ['CSR & Social Impact', 'csr-social-impact', ['icon' => 'csr', 'summary' => 'Outcome-oriented development programmes', 'content' => 'CSR strategy, delivery and impact measurement.']],
        ['Research & Academia', 'research-academia', ['icon' => 'research', 'summary' => 'Knowledge, research and innovation', 'content' => 'Institutional research and knowledge partnerships.']],
        ['Startups & Entrepreneurship', 'startups-entrepreneurship', ['icon' => 'startup', 'summary' => 'Innovation and venture enablement', 'content' => 'Startup advisory, acceleration and market access.']],
    ],
    'tenders' => [
        ['Empanelment of Agencies for Skill Development Programs', 'skill-development-empanelment', ['type' => 'tender', 'department' => 'Ministry of Skill Development', 'closing_date' => '2026-08-30', 'document_url' => '#', 'description' => 'Empanelment opportunity.']],
        ['Pre-Qualification for Infrastructure Development Projects', 'infrastructure-prequalification', ['type' => 'tender', 'department' => 'Public Works Department', 'closing_date' => '2026-08-28', 'document_url' => '#', 'description' => 'Pre-qualification notice.']],
        ['CSR Project Proposal for Rural Development Initiatives', 'csr-rural-development', ['type' => 'notice', 'department' => 'Ministry of Corporate Affairs', 'closing_date' => '2026-08-24', 'document_url' => '#', 'description' => 'Call for project proposals.']],
        ['Consultancy Services for Capacity Building & Training', 'capacity-building-consultancy', ['type' => 'tender', 'department' => 'NITI Aayog', 'closing_date' => '2026-08-22', 'document_url' => '#', 'description' => 'Consultancy services opportunity.']],
        ['Development of Digital Learning Platform for Students', 'digital-learning-platform', ['type' => 'tender', 'department' => 'Ministry of Education', 'closing_date' => '2026-08-20', 'document_url' => '#', 'description' => 'Digital learning platform tender.']],
    ],
    'blog' => [
        ['Building Resilient Institutions for the Next Decade', 'building-resilient-institutions', ['excerpt' => 'A practical framework for institutional strength, adaptability and performance.', 'content' => 'Institutional resilience requires strategy, capable teams and useful digital systems.', 'published_at' => date('Y-m-d')]],
        ['Mobilising Capital for Sustainable Development', 'capital-sustainable-development', ['excerpt' => 'How stronger project design connects ideas to responsible capital.', 'content' => 'Well-structured programmes create clarity for funders and implementation partners.', 'published_at' => date('Y-m-d')]],
        ['Digital Public Infrastructure: From Platform to Outcomes', 'digital-public-infrastructure', ['excerpt' => 'Design principles that turn technology investment into inclusive public value.', 'content' => 'Digital systems succeed when they are usable, interoperable and outcome-led.', 'published_at' => date('Y-m-d')]],
        ['Measuring What Matters in CSR Programmes', 'measuring-csr-impact', ['excerpt' => 'Moving from activity reporting to useful evidence and measurable change.', 'content' => 'Impact systems should support better decisions throughout programme delivery.', 'published_at' => date('Y-m-d')]],
    ],
    'partners' => [
        ['Government Institutions', 'government-institutions', ['website' => '#', 'description' => 'Public-sector implementation partners']],
        ['Development Agencies', 'development-agencies', ['website' => '#', 'description' => 'National and international development partners']],
        ['Industry & Enterprises', 'industry-enterprises', ['website' => '#', 'description' => 'Corporate and industry ecosystem partners']],
        ['Knowledge Partners', 'knowledge-partners', ['website' => '#', 'description' => 'Universities and research institutions']],
    ],
];

$recordExists = $db->prepare('SELECT id FROM admin_records WHERE module = :module AND slug = :slug LIMIT 1');
$insertRecord = $db->prepare(
    'INSERT INTO admin_records (module, title, slug, status, sort_order, data, created_by, updated_by)
     VALUES (:module, :title, :slug, "published", :sort_order, :data, :created_by, :updated_by)'
);
foreach ($moduleDefaults as $module => $records) {
    foreach ($records as $order => [$title, $slug, $data]) {
        $recordExists->execute(['module' => $module, 'slug' => $slug]);
        if ($recordExists->fetchColumn()) {
            continue;
        }
        $insertRecord->execute([
            'module' => $module, 'title' => $title, 'slug' => $slug, 'sort_order' => $order,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_by' => $userId ?: null, 'updated_by' => $userId ?: null,
        ]);
    }
}

echo "Home page {$pageId} configured with dynamic sections, media and starter module content.\n";
