<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Config\Database;

$db = Database::connection();
$userId = (int) ($db->query("SELECT id FROM users WHERE user_type = 'admin' AND deleted_at IS NULL ORDER BY id LIMIT 1")->fetchColumn() ?: 0);

$media = [];
$findMedia = $db->prepare('SELECT id FROM media WHERE filename = :filename AND deleted_at IS NULL ORDER BY id DESC LIMIT 1');
foreach ([
    'building' => 'udyam-hero-building.webp',
    'advisory' => 'project-advisory.webp',
    'institution' => 'institutional-development.webp',
    'funding' => 'funding.webp',
    'report' => 'featured-report.webp',
] as $key => $filename) {
    $findMedia->execute(['filename' => $filename]);
    $media[$key] = (int) ($findMedia->fetchColumn() ?: 0);
}

$asset = static fn (string $filename): string => '/uploads/media/original/home/' . $filename;
$pages = [
    'about-us' => [
        'title' => 'About Udyam Ventures',
        'excerpt' => 'A national knowledge and institutional development partner helping organizations translate ambition into measurable impact.',
        'keywords' => 'Udyam Ventures, institutional development, advisory, impact',
        'hero' => ['About Udyam Ventures', 'Building institutions that create lasting value.', 'We partner with governments, enterprises, universities, development organizations and purpose-led leaders to strengthen institutions, unlock opportunities and deliver sustainable outcomes.', 'Explore Our Work', '/services', 'Meet Our Team', '/contact', 'building'],
        'blocks' => [
            ['Who We Are', 'Udyam Ventures is a multidisciplinary business and project advisory organization. We combine strategy, funding, institutional strengthening, research, technology and implementation support to help organizations move from ideas to outcomes.', $asset('project-advisory.webp')],
            ['Our Approach', 'Our work begins with context and evidence. We co-create practical roadmaps, mobilize the right partners and resources, strengthen delivery systems, and remain engaged through execution, monitoring and learning.', $asset('institutional-development.webp')],
        ],
        'features' => ['What Defines Udyam', 'An integrated advisory model designed around institutional outcomes.', [
            ['◇', 'Purpose-led strategy', 'Clear priorities connected to measurable public and business value.'],
            ['◎', 'Cross-sector expertise', 'Experience across government, industry, academia, CSR and development.'],
            ['↗', 'Execution orientation', 'Practical support that carries strategy into implementation and impact.'],
        ]],
        'stats' => [['500+', 'Projects Delivered'], ['25+', 'States Impacted'], ['200+', 'Institutions Strengthened'], ['100+', 'Expert Advisors']],
        'faqs' => ['Working With Udyam', [['Who does Udyam work with?', 'We work with public institutions, businesses, MSMEs, universities, CSR foundations, startups and development organizations.'], ['What makes the Udyam model different?', 'We bring strategy, resource mobilization, implementation, digital systems and knowledge support together under one institutional partnership.']]],
        'cta' => ['Build the next chapter of your institution.', 'Talk to our team about your priorities, constraints and opportunities.', 'Start a Conversation', '/contact'],
    ],
    'services' => [
        'title' => 'Services',
        'excerpt' => 'Integrated advisory, funding, research, institutional development and transformation services.',
        'keywords' => 'project advisory, funding, research, digital transformation, capacity building',
        'hero' => ['Our Services', 'From strategy to measurable outcomes.', 'Udyam brings together advisory, capital, knowledge, technology and delivery capabilities to solve complex institutional and development challenges.', 'Discuss Your Requirement', '/contact', 'View Focus Areas', '/focus-areas', 'advisory'],
        'blocks' => [
            ['Integrated Project Advisory', 'We support programmes and projects from opportunity assessment and design through planning, partnership development, implementation support, monitoring and closure.', $asset('project-advisory.webp')],
            ['Funding & Resource Mobilization', 'We help institutions structure credible opportunities, identify aligned funding channels, prepare investment-ready propositions and build relationships with public, private and development partners.', $asset('funding.webp')],
            ['Institutional & Digital Transformation', 'We strengthen governance, operating processes, teams, performance systems and digital workflows so organizations can deliver with greater clarity, speed and accountability.', $asset('institutional-development.webp')],
        ],
        'features' => ['Core Capabilities', 'Modular services that can work independently or as one transformation programme.', [
            ['◇', 'Project Advisory', 'Concept-to-completion programme and project support.'], ['₹', 'Funding Mobilization', 'Opportunity mapping, structuring and funding facilitation.'], ['🏛', 'Institutional Development', 'Governance, processes and organizational strengthening.'], ['◎', 'Research & Insights', 'Evidence, assessment and strategic intelligence.'], ['⌘', 'Digital Transformation', 'Platforms, workflows and institutional technology.'], ['♧', 'Capacity Building', 'Training, mentoring and capability development.'],
        ]],
        'stats' => [['360°', 'Advisory Model'], ['6', 'Integrated Capabilities'], ['100+', 'Expert Advisors'], ['Pan-India', 'Delivery Network']],
        'faqs' => ['Service Questions', [['Can services be engaged individually?', 'Yes. Each capability can be engaged independently or combined into a tailored institutional programme.'], ['Does Udyam support implementation?', 'Yes. Our model extends beyond reports and strategy into programme management, implementation support, monitoring and learning.']]],
        'cta' => ['Need a tailored institutional solution?', 'We will shape the right combination of capabilities around your objectives.', 'Book Consultation', '/contact'],
    ],
    'focus-areas' => [
        'title' => 'Focus Areas',
        'excerpt' => 'Sector-focused solutions for government, industry, skills, CSR, research and entrepreneurship ecosystems.',
        'keywords' => 'government advisory, MSME, skill development, CSR, startups, research',
        'hero' => ['Focus Areas', 'Deep context. Relevant solutions. Better outcomes.', 'Our sector teams combine domain understanding with institutional, financial, research and delivery capabilities to address each ecosystem on its own terms.', 'Explore Sectors', '#sector-capabilities', 'Partner With Us', '/contact', 'institution'],
        'blocks' => [
            ['Government & Public Sector', 'Policy support, programme design, PMU services, institutional capacity, public systems and outcome-focused delivery for government departments and agencies.', $asset('institutional-development.webp')],
            ['Enterprise, MSME & Industry', 'Growth strategy, market readiness, capital access, cluster development, digital transformation and competitiveness support for enterprises and industry ecosystems.', $asset('funding.webp')],
            ['Knowledge, Skills & Social Impact', 'Research partnerships, workforce development, CSR programme strategy, impact measurement and innovation ecosystems for universities, foundations and development organizations.', $asset('featured-report.webp')],
        ],
        'features' => ['Sector Capabilities', 'Focused expertise connected by a common institutional delivery model.', [
            ['🏛', 'Government & Public Sector', 'Public systems, programmes and institutional delivery.'], ['⌂', 'MSME & Industries', 'Growth, competitiveness and resource access.'], ['♧', 'Skill Development', 'Workforce and institutional capability.'], ['♡', 'CSR & Social Impact', 'Purpose-led programmes and measurable outcomes.'], ['◎', 'Research & Academia', 'Knowledge, evidence and institutional partnerships.'], ['↗', 'Startups & Entrepreneurship', 'Innovation, enterprise development and acceleration.'],
        ]],
        'stats' => [['6', 'Priority Ecosystems'], ['25+', 'States'], ['500+', 'Assignments'], ['200+', 'Institutions']],
        'faqs' => ['Focus Area Questions', [['Can Udyam work across multiple sectors in one programme?', 'Yes. Many institutional challenges cross sector boundaries, and our teams are structured to build integrated programmes.']]],
        'cta' => ['Bring sector depth to your next initiative.', 'Connect with the Udyam team working in your ecosystem.', 'Speak With an Expert', '/contact'],
    ],
    'knowledge-centre' => [
        'title' => 'Knowledge Centre',
        'excerpt' => 'Research, analysis, frameworks and practical intelligence for institutional leaders.',
        'keywords' => 'knowledge centre, research reports, policy insights, institutional intelligence',
        'hero' => ['Knowledge Centre', 'Ideas that improve institutional decisions.', 'Access research, field intelligence, frameworks and perspectives developed to help leaders navigate policy, markets, institutions and impact.', 'Explore Insights', '#knowledge-resources', 'View Downloads', '/downloads', 'report'],
        'blocks' => [
            ['Evidence for Better Decisions', 'Our knowledge products connect research with practical institutional choices. We translate data, policy, stakeholder insight and field experience into useful intelligence.', $asset('featured-report.webp')],
            ['From Learning to Application', 'Reports are complemented by frameworks, tools, case studies and advisory conversations that help organizations apply evidence in their own operating context.', $asset('project-advisory.webp')],
        ],
        'features' => ['Knowledge Resources', 'A growing institutional library for decision-makers and practitioners.', [
            ['▥', 'Research Reports', 'In-depth studies and sector analysis.'], ['◇', 'Policy & Market Insights', 'Timely perspectives on changing contexts.'], ['□', 'Frameworks & Toolkits', 'Practical resources for institutional teams.'], ['◎', 'Case Studies', 'Learning from implementation and outcomes.'], ['↗', 'Expert Perspectives', 'Ideas from Udyam specialists and partners.'], ['↓', 'Download Library', 'Reports, briefs and working resources.'],
        ]],
        'stats' => [['Research', 'Driven'], ['Practice', 'Informed'], ['Impact', 'Focused'], ['Open', 'Knowledge']],
        'faqs' => ['Knowledge Centre', [['Can reports be downloaded?', 'Resources marked for public download are available through the Downloads section.'], ['Can Udyam undertake commissioned research?', 'Yes. We undertake assessments, studies, evaluations and strategic research assignments.']]],
        'cta' => ['Need evidence for an important decision?', 'Discuss a research, assessment or knowledge partnership with us.', 'Talk to Research Team', '/contact'],
    ],
    'research' => [
        'title' => 'Research & Insights',
        'excerpt' => 'Decision-focused research, assessment, evaluation and strategic intelligence.',
        'keywords' => 'institutional research, evaluation, policy research, market assessment',
        'hero' => ['Research & Insights', 'Evidence designed for action.', 'We deliver rigorous, decision-focused research that helps institutions understand context, test assumptions, measure progress and choose stronger pathways.', 'Commission Research', '/contact', 'Knowledge Centre', '/knowledge-centre', 'report'],
        'blocks' => [
            ['Applied Institutional Research', 'Our research work is designed around real decisions. We combine quantitative analysis, qualitative inquiry, stakeholder consultation and sector expertise.', $asset('featured-report.webp')],
            ['Assessment, Evaluation & Learning', 'We assess institutions, programmes, markets and ecosystems; develop useful baselines and frameworks; and generate learning that improves delivery and outcomes.', $asset('project-advisory.webp')],
        ],
        'features' => ['Research Capabilities', 'Rigour, context and practical interpretation in one research partnership.', [
            ['◎', 'Policy Research', 'Evidence for policy and programme choices.'], ['◇', 'Market Assessment', 'Demand, ecosystem and opportunity intelligence.'], ['▤', 'Programme Evaluation', 'Performance, outcomes and learning.'], ['⌘', 'Data & Analytics', 'Structured analysis and decision dashboards.'], ['♧', 'Stakeholder Research', 'Perspectives across complex ecosystems.'], ['□', 'Knowledge Products', 'Reports, briefs, frameworks and tools.'],
        ]],
        'stats' => [['Mixed', 'Methods'], ['Decision', 'Focused'], ['Sector', 'Expertise'], ['Actionable', 'Outputs']],
        'faqs' => ['Research Engagements', [['Does Udyam conduct primary research?', 'Yes. We design and deliver primary and secondary research using methods appropriate to the assignment.'], ['Can research include implementation recommendations?', 'Yes. Practical implications and implementation pathways are central to our approach.']]],
        'cta' => ['Turn uncertainty into institutional intelligence.', 'Share your research question or decision challenge with our team.', 'Start a Research Brief', '/contact'],
    ],
    'notice-tender' => [
        'title' => 'Notices & Tenders',
        'excerpt' => 'Current tenders, notices, corrigenda and institutional opportunities.',
        'keywords' => 'tenders, notices, corrigendum, institutional opportunities',
        'hero' => ['Notices & Tenders', 'Opportunities and updates in one place.', 'Track active tenders, institutional notices, corrigenda and partnership opportunities relevant to the Udyam ecosystem.', 'View Current Updates', '#current-updates', 'Contact Team', '/contact', 'building'],
        'blocks' => [['Transparent Opportunity Access', 'This section provides a centralized view of current opportunities and formal updates. Each listing includes the issuing department, closing date and related documentation where available.', $asset('institutional-development.webp')]],
        'features' => ['Current Updates', 'Use the category and date information to identify relevant opportunities.', [['▤', 'Tenders', 'Open procurement and project opportunities.'], ['□', 'Notices', 'Official institutional announcements.'], ['◇', 'Corrigenda', 'Changes and clarifications to active listings.']]],
        'stats' => [['Updated', 'Regularly'], ['Clear', 'Closing Dates'], ['Direct', 'Documents'], ['Open', 'Access']],
        'faqs' => ['Tender Information', [['Where are tender documents available?', 'When a document is available, the listing provides a direct View Details or download link.'], ['How often are listings updated?', 'The admin team can publish and update listings through the Tenders module.']]],
        'cta' => ['Need clarification on an opportunity?', 'Contact the relevant team with the notice or tender reference.', 'Contact Us', '/contact'],
    ],
    'downloads' => [
        'title' => 'Downloads',
        'excerpt' => 'Reports, publications, briefs, toolkits and institutional resources.',
        'keywords' => 'downloads, reports, publications, toolkits',
        'hero' => ['Downloads', 'Knowledge you can use.', 'Browse reports, publications, briefs, frameworks and practical resources produced by Udyam Ventures and its knowledge partners.', 'Browse Resources', '#download-library', 'Knowledge Centre', '/knowledge-centre', 'report'],
        'blocks' => [['A Practical Resource Library', 'The download library brings together institutional knowledge products designed for leaders, programme teams, researchers and development practitioners.', $asset('featured-report.webp')]],
        'features' => ['Download Library', 'Resources are organized for quick discovery and practical use.', [['↓', 'Research Reports', 'Detailed studies and strategic analysis.'], ['▤', 'Policy Briefs', 'Concise decision-focused intelligence.'], ['□', 'Frameworks & Toolkits', 'Reusable institutional resources.'], ['◇', 'Programme Documents', 'Guides, templates and reference material.']]],
        'stats' => [['Reports', 'Research'], ['Briefs', 'Insight'], ['Tools', 'Application'], ['Resources', 'Learning']],
        'faqs' => ['Using Downloads', [['Are downloads free?', 'Public resources can be downloaded directly. Some commissioned or restricted resources may require access approval.']]],
        'cta' => ['Looking for a specific publication?', 'Our team can help you locate relevant Udyam knowledge resources.', 'Request a Resource', '/contact'],
    ],
    'resource-center' => [
        'title' => 'Resource Center',
        'excerpt' => 'Institutional tools, frameworks, case studies and implementation resources.',
        'keywords' => 'resource center, institutional tools, frameworks, case studies',
        'hero' => ['Resource Center', 'Practical tools for stronger institutions.', 'Explore frameworks, implementation resources, case studies and tools that help teams plan, deliver, monitor and improve institutional initiatives.', 'Explore Resources', '#resource-tools', 'Ask an Expert', '/contact', 'institution'],
        'blocks' => [['From Knowledge to Practice', 'Our resource center is designed for application. Each resource connects institutional concepts with practical steps, templates or examples.', $asset('institutional-development.webp')]],
        'features' => ['Resource Tools', 'Useful formats for different stages of the institutional journey.', [['◇', 'Diagnostic Tools', 'Understand institutional strengths and gaps.'], ['◎', 'Planning Frameworks', 'Structure priorities, roadmaps and responsibilities.'], ['⌘', 'Implementation Tools', 'Support delivery, coordination and tracking.'], ['↗', 'Monitoring Resources', 'Measure progress, outcomes and learning.'], ['□', 'Case Studies', 'Learn from programmes and institutional change.'], ['▥', 'Expert Guides', 'Practical perspectives from domain specialists.']]],
        'stats' => [['Diagnose', 'Context'], ['Plan', 'Priorities'], ['Deliver', 'Programmes'], ['Measure', 'Impact']],
        'faqs' => ['Resource Center', [['Can resources be adapted for our institution?', 'Yes. Public tools can be adapted, and Udyam can support institution-specific customization and facilitation.']]],
        'cta' => ['Put the right tools behind your strategy.', 'Talk to us about adapting a framework for your institution.', 'Work With Udyam', '/contact'],
    ],
    'careers' => [
        'title' => 'Careers',
        'excerpt' => 'Build a career at the intersection of knowledge, institutions and impact.',
        'keywords' => 'Udyam careers, advisory jobs, development sector careers',
        'hero' => ['Careers at Udyam', 'Do work that strengthens institutions.', 'Join a multidisciplinary team working across strategy, research, funding, technology, programmes and institutional transformation.', 'View Opportunities', '#career-paths', 'Share Your Profile', '/contact', 'advisory'],
        'blocks' => [
            ['A Place for Curious Problem Solvers', 'Udyam brings together people who are rigorous, collaborative and motivated by meaningful institutional outcomes. Our assignments offer exposure to diverse sectors and complex challenges.', $asset('project-advisory.webp')],
            ['Grow Through Real Responsibility', 'Team members work closely with clients, partners and experts, developing both domain depth and the ability to translate ideas into practical delivery.', $asset('institutional-development.webp')],
        ],
        'features' => ['Career Paths', 'Contribute through specialist expertise or integrated project roles.', [['◇', 'Advisory & Strategy', 'Institutional and programme advisory roles.'], ['◎', 'Research & Knowledge', 'Research, evaluation and content roles.'], ['₹', 'Funding & Partnerships', 'Resource mobilization and ecosystem roles.'], ['⌘', 'Digital & Data', 'Technology, analytics and product roles.'], ['♧', 'Programme Delivery', 'PMU and implementation roles.'], ['↗', 'Internships & Associates', 'Early-career learning opportunities.']]],
        'stats' => [['Learn', 'Continuously'], ['Work', 'Collaboratively'], ['Own', 'Outcomes'], ['Create', 'Impact']],
        'faqs' => ['Careers at Udyam', [['How can I apply?', 'Open roles will be listed on this page. You can also share your profile through the contact channel for future opportunities.'], ['Does Udyam offer internships?', 'Internship and associate opportunities may be offered based on active projects and team requirements.']]],
        'cta' => ['Bring your perspective to Udyam.', 'Tell us about your experience, interests and the impact you want to create.', 'Share Your Profile', '/contact'],
    ],
    'contact' => [
        'title' => 'Contact Us',
        'excerpt' => 'Connect with Udyam Ventures for advisory, research, funding, partnerships and institutional support.',
        'keywords' => 'contact Udyam Ventures, consultation, partnership',
        'hero' => ['Contact Udyam', 'Let’s turn your priority into a practical roadmap.', 'Tell us about your institution, opportunity or challenge. Our team will connect you with the relevant Udyam capability and sector experts.', 'Book Consultation', '#contact-options', 'Explore Services', '/services', 'building'],
        'blocks' => [['Start With a Conversation', 'Whether you are shaping a new programme, strengthening an institution, seeking funding, commissioning research or building a partnership, we begin by understanding the outcome you need.', $asset('project-advisory.webp')]],
        'features' => ['Contact Options', 'Choose the conversation closest to your requirement.', [['◇', 'Advisory Enquiry', 'Strategy, projects and institutional development.'], ['₹', 'Funding & Partnerships', 'Capital, resources and ecosystem collaboration.'], ['◎', 'Research Requirement', 'Studies, assessment and strategic intelligence.'], ['⌘', 'Digital Transformation', 'Platforms, processes and institutional technology.'], ['♧', 'Training & Capacity', 'Workshops, programmes and capability development.'], ['↗', 'General Enquiry', 'Media, careers and other questions.']]],
        'stats' => [['One', 'Integrated Team'], ['Multiple', 'Sector Experts'], ['Practical', 'Next Steps'], ['Outcome', 'Focused']],
        'faqs' => ['Before You Contact Us', [['What information should I share?', 'A brief description of your organization, objective, current stage, geography and expected timeline will help us route your enquiry.'], ['When will the team respond?', 'The relevant team will review your enquiry and respond through the contact details provided.']]],
        'cta' => ['Ready to begin?', 'Share your requirement and let us identify the right path forward.', 'Send an Enquiry', '/contact'],
    ],
];

$upsertPage = $db->prepare(
    'INSERT INTO pages
    (title, slug, template, excerpt, content, featured_image, seo_title, seo_description, seo_keywords, status, sort_order, created_by, updated_by)
    VALUES (:title, :slug, "sectioned", :excerpt, "", :featured_image, :seo_title, :seo_description, :seo_keywords, "published", :sort_order, :created_by, :updated_by)
    ON DUPLICATE KEY UPDATE
      title = VALUES(title), template = "sectioned", excerpt = VALUES(excerpt),
      seo_title = VALUES(seo_title), seo_description = VALUES(seo_description),
      seo_keywords = VALUES(seo_keywords), status = "published", deleted_at = NULL'
);
$findPage = $db->prepare('SELECT id FROM pages WHERE slug = :slug LIMIT 1');
$insertSection = $db->prepare(
    'INSERT IGNORE INTO page_sections (page_id, section_key, sort_order, is_enabled, data)
     VALUES (:page_id, :section_key, :sort_order, 1, :data)'
);

$order = 10;
foreach ($pages as $slug => $definition) {
    $hero = $definition['hero'];
    $heroImage = $media[$hero[7]] ?: $media['building'];
    $upsertPage->execute([
        'title' => $definition['title'], 'slug' => $slug, 'excerpt' => $definition['excerpt'],
        'featured_image' => $heroImage ?: null,
        'seo_title' => $definition['title'] . ' | Udyam Ventures',
        'seo_description' => $definition['excerpt'], 'seo_keywords' => $definition['keywords'],
        'sort_order' => $order++, 'created_by' => $userId ?: null, 'updated_by' => $userId ?: null,
    ]);
    $findPage->execute(['slug' => $slug]);
    $pageId = (int) $findPage->fetchColumn();

    $sections = [
        'page_hero' => [
            'eyebrow' => $hero[0], 'heading' => $hero[1], 'description' => $hero[2], 'image' => $heroImage,
            'primary_button_label' => $hero[3], 'primary_button_url' => $hero[4],
            'secondary_button_label' => $hero[5], 'secondary_button_url' => $hero[6],
        ],
        'content_blocks' => ['items' => array_map(static fn (array $block): array => [
            'heading' => $block[0], 'content' => $block[1], 'image_url' => $block[2],
        ], $definition['blocks'])],
        'feature_grid' => ['heading' => $definition['features'][0], 'description' => $definition['features'][1], 'items' => array_map(static fn (array $item): array => [
            'icon' => $item[0], 'title' => $item[1], 'description' => $item[2],
        ], $definition['features'][2])],
        'page_stats' => ['items' => array_map(static fn (array $stat): array => ['value' => $stat[0], 'label' => $stat[1]], $definition['stats'])],
        'page_faqs' => ['heading' => $definition['faqs'][0], 'items' => array_map(static fn (array $faq): array => ['question' => $faq[0], 'answer' => $faq[1]], $definition['faqs'][1])],
        'page_cta' => ['heading' => $definition['cta'][0], 'description' => $definition['cta'][1], 'button_label' => $definition['cta'][2], 'button_url' => $definition['cta'][3]],
    ];
    $sectionOrder = 0;
    foreach ($sections as $sectionKey => $sectionData) {
        $insertSection->execute([
            'page_id' => $pageId, 'section_key' => $sectionKey, 'sort_order' => $sectionOrder++,
            'data' => json_encode($sectionData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
}

echo count($pages) . " sectioned website pages are ready and editable in Admin → Pages.\n";
