<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Config\Database;

$db = Database::connection();
$findMedia = $db->prepare('SELECT id FROM media WHERE filename = :filename AND deleted_at IS NULL ORDER BY id DESC LIMIT 1');
$mediaId = static function (string $filename) use ($findMedia): int {
    $findMedia->execute(['filename' => $filename]);
    return (int) ($findMedia->fetchColumn() ?: 0);
};

$building = $mediaId('udyam-hero-building.webp');
$advisory = $mediaId('project-advisory.webp');
$institution = $mediaId('institutional-development.webp');
$funding = $mediaId('funding.webp');

$expertise = [
    ['🏛', 'Institutional Development', 'We strengthen governance, organizational structures, quality frameworks, strategic planning, operational systems and internal processes that build resilient, credible and future-ready institutions.'],
    ['₹', 'Project Funding & Resource Mobilization', 'From identifying aligned opportunities to developing investment-ready projects, we support access to government programmes, CSR initiatives, philanthropic funding, institutional grants and strategic partnerships.'],
    ['♡', 'CSR, ESG & Social Impact Partnerships', 'We design and support impactful CSR initiatives aligned with business objectives, community priorities, ESG commitments and national development goals—from concept to monitoring and impact measurement.'],
    ['✓', 'Compliance & Governance Solutions', 'We create practical compliance support systems, governance frameworks, audit-readiness mechanisms, SOPs, operational manuals, documentation systems and quality-assurance processes.'],
    ['⌘', 'Technical & Implementation Support', 'End-to-end technical assistance across planning, implementation frameworks, monitoring systems, digital workflows, research, evaluation, documentation and knowledge management.'],
    ['◇', 'Organizational Branding & Profile Development', 'Professional institutional profiles, capability statements, annual and impact reports, corporate presentations, brochures, digital content and strategic communication assets that strengthen credibility.'],
    ['↗', 'Entrepreneurship & Enterprise Development', 'Practical business development, innovation support, growth strategy, market-readiness and enterprise development solutions for startups, MSMEs, entrepreneurs and aspiring business leaders.'],
    ['▥', 'Digital Knowledge & Learning Solutions', 'Capsule-based online programmes, implementation toolkits, digital resources, templates, knowledge repositories, sector intelligence and professional development modules designed for practical application.'],
    ['◎', 'Skill Development Ecosystem Support', 'Specialized support for training partners, educational institutions, sector organizations and implementation agencies across project planning, compliance, capacity building and employability-focused delivery.'],
];

$pages = [
    'about-us' => [
        'title' => 'About Udyam',
        'excerpt' => 'Empowering organizations, enabling growth and creating sustainable impact through stronger institutions.',
        'sections' => [
            'page_hero' => [
                'eyebrow' => 'About Udyam Ventures',
                'heading' => 'Empowering Organizations. Enabling Growth.',
                'description' => 'Lasting impact begins with strong organizations. Udyam Ventures partners with institutions, enterprises and development leaders to build capabilities, unlock opportunities and create sustainable, future-ready organizations.',
                'image' => $building,
                'primary_button_label' => 'Discover Our Expertise', 'primary_button_url' => '#areas-of-expertise',
                'secondary_button_label' => 'Partner With Udyam', 'secondary_button_url' => '/contact',
            ],
            'content_blocks' => ['items' => [
                ['heading' => 'Development is driven by strong organizations—not just projects.', 'content' => 'At Udyam Ventures Pvt. Ltd., we believe lasting impact begins with strong organizations. We partner with businesses, educational institutions, NGOs, startups, social enterprises, implementation agencies, industry bodies, corporate organizations and public-sector institutions to strengthen institutional capabilities, unlock growth opportunities and build sustainable, future-ready organizations.', 'image_url' => '/uploads/media/original/home/project-advisory.webp'],
                ['heading' => 'From strong ideas to implementation excellence.', 'content' => 'As a Project Advisory and Institutional Development Partner, we transform ideas into impactful initiatives through strategic planning, institutional strengthening, project development, compliance support, capacity building and implementation excellence. Our scalable, outcome-oriented solutions improve performance, governance, operational efficiency and long-term value.', 'image_url' => '/uploads/media/original/home/institutional-development.webp'],
                ['heading' => 'An integrated institutional ecosystem.', 'content' => 'Sustainable development demands clear vision, credible documentation, efficient systems, strategic partnerships and disciplined execution. Rather than delivering isolated services, we combine domain expertise, technology, innovation and implementation experience to make partners funding-ready, compliance-ready, partnership-ready and future-ready.', 'image_url' => '/uploads/media/original/home/funding.webp'],
            ]],
            'profile_statements' => [
                'eyebrow' => 'What Guides Us', 'heading' => 'Purpose translated into institutional strength.',
                'description' => 'Our vision, mission and philosophy keep every engagement focused on durable capability and measurable outcomes.',
                'items' => [
                    ['icon' => '◉', 'title' => 'Our Vision', 'content' => "To build stronger institutions that drive inclusive growth, innovation and sustainable development while contributing to India's social and economic transformation."],
                    ['icon' => '↗', 'title' => 'Our Mission', 'content' => 'To empower organizations with knowledge, systems, strategic partnerships, technical expertise and innovative solutions that strengthen institutions, improve governance, mobilize resources and create lasting impact.'],
                    ['icon' => '◇', 'title' => 'Our Philosophy', 'content' => 'Sustainable development is achieved when institutions have the right knowledge, systems, partnerships and capabilities. We simplify complexity and unlock every organization’s potential to deliver meaningful outcomes.'],
                ],
            ],
            'expertise_grid' => [
                'eyebrow' => 'Integrated Capabilities', 'heading' => 'Our areas of expertise.',
                'description' => 'Nine connected practice areas designed to strengthen organizational foundations and accelerate sustainable growth.',
                'items' => array_map(static fn (array $item): array => ['icon' => $item[0], 'title' => $item[1], 'description' => $item[2]], $expertise),
            ],
            'page_stats' => ['items' => [['value' => '360°', 'label' => 'Institutional Perspective'], ['value' => '9', 'label' => 'Integrated Practice Areas'], ['value' => '1', 'label' => 'Trusted Knowledge Partner'], ['value' => 'Impact', 'label' => 'Designed for the Long Term']]],
            'page_faqs' => ['heading' => 'Why Udyam?', 'items' => [
                ['question' => 'Why choose Udyam as a long-term growth partner?', 'answer' => 'We bring strategy, governance, compliance, technology, capacity building, project development, knowledge management and implementation support together under one multidisciplinary ecosystem.'],
                ['question' => 'Who does Udyam work with?', 'answer' => 'We work with enterprises, educational institutions, NGOs, startups, social enterprises, implementation agencies, industry bodies, corporates and public-sector institutions.'],
                ['question' => 'What outcomes does Udyam help create?', 'answer' => 'We help partners strengthen operations, mobilize resources, improve compliance, build strategic partnerships, enhance institutional credibility and deliver measurable, sustainable impact.'],
            ]],
            'page_cta' => ['heading' => 'Strong institutions create a stronger nation.', 'description' => 'Build a resilient, opportunity-ready and future-ready organization with Udyam Ventures.', 'button_label' => 'Start a Conversation', 'button_url' => '/contact'],
        ],
    ],
    'services' => [
        'title' => 'Our Services',
        'excerpt' => 'Nine integrated practice areas that strengthen institutions, mobilize resources and turn strategy into sustainable impact.',
        'sections' => [
            'page_hero' => [
                'eyebrow' => 'Integrated Institutional Solutions',
                'heading' => 'Expertise that moves organizations forward.',
                'description' => 'From institutional development and funding readiness to digital learning and implementation support, Udyam brings every critical capability into one connected growth ecosystem.',
                'image' => $advisory ?: $building,
                'primary_button_label' => 'Explore Capabilities', 'primary_button_url' => '#areas-of-expertise',
                'secondary_button_label' => 'Discuss Your Requirement', 'secondary_button_url' => '/contact',
            ],
            'content_blocks' => ['items' => [
                ['heading' => 'One ecosystem. Every capability needed for sustainable growth.', 'content' => 'Organizations rarely need isolated advice. They need connected strategy, systems, funding, compliance, partnerships, knowledge and disciplined implementation. Udyam assembles the right capabilities around each institutional objective and remains focused on practical execution.', 'image_url' => '/uploads/media/original/home/project-advisory.webp'],
                ['heading' => 'Designed around readiness and measurable outcomes.', 'content' => 'Our engagement model strengthens the fundamentals that make organizations credible and opportunity-ready: governance, operating systems, high-quality documentation, implementation frameworks, institutional identity, technical capability and evidence-led decision-making.', 'image_url' => '/uploads/media/original/home/funding.webp'],
            ]],
            'profile_statements' => [
                'eyebrow' => 'How We Work', 'heading' => 'A partnership built around your institutional journey.',
                'description' => 'Every engagement combines clarity, capability and execution.',
                'items' => [
                    ['icon' => '◎', 'title' => 'Diagnose & Design', 'content' => 'We understand aspirations, institutional context and constraints, then translate them into a practical, sequenced roadmap.'],
                    ['icon' => '⌘', 'title' => 'Strengthen & Mobilize', 'content' => 'We build systems, documentation, teams, partnerships and resource pathways needed to move the roadmap forward.'],
                    ['icon' => '↗', 'title' => 'Implement & Improve', 'content' => 'We support delivery, monitoring, knowledge capture and continuous improvement to create measurable, lasting outcomes.'],
                ],
            ],
            'expertise_grid' => [
                'eyebrow' => 'Our Service Portfolio', 'heading' => 'Nine practices. One integrated advantage.',
                'description' => 'Engage an individual capability or combine multiple practices into a tailored institutional development programme.',
                'items' => array_map(static fn (array $item): array => ['icon' => $item[0], 'title' => $item[1], 'description' => $item[2]], $expertise),
            ],
            'page_stats' => ['items' => [['value' => '9', 'label' => 'Integrated Practices'], ['value' => '360°', 'label' => 'Advisory Approach'], ['value' => 'End-to-End', 'label' => 'Technical Support'], ['value' => 'Outcome', 'label' => 'Oriented Delivery']]],
            'page_faqs' => ['heading' => 'Working With Udyam', 'items' => [
                ['question' => 'Can services be engaged individually?', 'answer' => 'Yes. Every practice can be engaged independently or combined into a tailored, multi-disciplinary institutional programme.'],
                ['question' => 'Does Udyam support implementation after strategy?', 'answer' => 'Yes. We provide end-to-end technical and implementation support including frameworks, workflows, documentation, monitoring, evaluation and knowledge management.'],
                ['question' => 'Can Udyam help make our organization funding-ready?', 'answer' => 'Yes. We strengthen project design, institutional documentation, governance systems, implementation planning and partnership positioning required for credible funding engagement.'],
            ]],
            'page_cta' => ['heading' => 'Build the right solution around your ambition.', 'description' => 'Tell us where your organization is today and the outcome you want to create.', 'button_label' => 'Book Consultation', 'button_url' => '/contact'],
        ],
    ],
];

$findPage = $db->prepare('SELECT id FROM pages WHERE slug = :slug AND deleted_at IS NULL LIMIT 1');
$updatePage = $db->prepare('UPDATE pages SET title = :title, excerpt = :excerpt, seo_title = :seo_title, seo_description = :seo_description, status = "published", template = "sectioned" WHERE id = :id');
$upsertSection = $db->prepare(
    'INSERT INTO page_sections (page_id, section_key, sort_order, is_enabled, data)
     VALUES (:page_id, :section_key, :sort_order, 1, :data)
     ON DUPLICATE KEY UPDATE sort_order = VALUES(sort_order), is_enabled = 1, data = VALUES(data)'
);

foreach ($pages as $slug => $definition) {
    $findPage->execute(['slug' => $slug]);
    $pageId = (int) $findPage->fetchColumn();
    if ($pageId === 0) {
        throw new RuntimeException("Page '{$slug}' does not exist. Run database/seed_website_pages.php first.");
    }
    $updatePage->execute([
        'id' => $pageId, 'title' => $definition['title'], 'excerpt' => $definition['excerpt'],
        'seo_title' => $definition['title'] . ' | Udyam Ventures', 'seo_description' => $definition['excerpt'],
    ]);
    foreach (array_values(array_keys($definition['sections'])) as $order => $sectionKey) {
        $upsertSection->execute([
            'page_id' => $pageId, 'section_key' => $sectionKey, 'sort_order' => $order,
            'data' => json_encode($definition['sections'][$sectionKey], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
}

echo "About and Services pages updated with premium editable content.\n";
