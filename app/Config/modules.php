<?php

declare(strict_types=1);

$text = static fn (string $label, bool $required = true): array => [
    'label' => $label, 'type' => 'text', 'required' => $required, 'maxlength' => 255,
];
$area = static fn (string $label, bool $required = false): array => [
    'label' => $label, 'type' => 'textarea', 'required' => $required,
];
$email = static fn (string $label): array => [
    'label' => $label, 'type' => 'email', 'required' => true, 'maxlength' => 255,
];
$date = static fn (string $label, bool $required = false): array => [
    'label' => $label, 'type' => 'date', 'required' => $required,
];
$select = static fn (string $label, array $options): array => [
    'label' => $label, 'type' => 'select', 'required' => true, 'options' => $options,
];

return [
    'services' => ['title' => 'Services', 'singular' => 'Service', 'fields' => [
        'title' => $text('Title'), 'slug' => $text('Slug'), 'icon' => $text('Icon', false),
        'summary' => $area('Summary'), 'content' => $area('Content', true),
        'image' => $text('Image URL', false),
    ]],
    'focus-areas' => ['title' => 'Focus Areas', 'singular' => 'Focus Area', 'fields' => [
        'title' => $text('Title'), 'slug' => $text('Slug'), 'icon' => $text('Icon Name', false), 'summary' => $area('Summary'),
        'content' => $area('Content'), 'image' => $text('Image URL', false),
    ]],
    'blog' => ['title' => 'Blog', 'singular' => 'Blog Post', 'fields' => [
        'title' => $text('Title'), 'slug' => $text('Slug'), 'excerpt' => $area('Excerpt'),
        'content' => $area('Content', true), 'featured_image' => $text('Featured Image URL', false),
        'published_at' => $date('Publish Date'),
    ]],
    'tenders' => ['title' => 'Tenders & Notices', 'singular' => 'Tender / Notice', 'fields' => [
        'title' => $text('Title'), 'type' => $select('Type', [
            'tender' => 'Tender', 'notice' => 'Notice', 'corrigendum' => 'Corrigendum',
        ]), 'department' => $text('Department'), 'closing_date' => $date('Closing Date'),
        'document_url' => $text('Document URL', false), 'description' => $area('Description'),
    ]],
    'testimonials' => ['title' => 'Testimonials', 'singular' => 'Testimonial', 'fields' => [
        'title' => $text('Person Name'), 'designation' => $text('Designation', false),
        'company' => $text('Company', false), 'quote' => $area('Testimonial', true),
        'photo' => $text('Photo URL', false),
    ]],
    'faqs' => ['title' => 'FAQs', 'singular' => 'FAQ', 'fields' => [
        'title' => $text('Question'), 'answer' => $area('Answer', true),
        'category' => $text('Category', false),
    ]],
    'team' => ['title' => 'Team Members', 'singular' => 'Team Member', 'fields' => [
        'title' => $text('Name'), 'designation' => $text('Designation'),
        'bio' => $area('Biography'), 'email' => ['label' => 'Email', 'type' => 'email', 'required' => false],
        'linkedin' => $text('LinkedIn URL', false), 'photo' => $text('Photo URL', false),
    ]],
    'partners' => ['title' => 'Partners', 'singular' => 'Partner', 'fields' => [
        'title' => $text('Name'), 'website' => $text('Website URL', false),
        'description' => $area('Description'), 'logo' => $text('Logo URL', false),
    ]],
    'menu' => ['title' => 'Menu Manager', 'singular' => 'Menu Item', 'fields' => [
        'title' => $text('Label'), 'url' => $text('URL'), 'location' => $select('Location', [
            'header' => 'Header', 'footer' => 'Footer',
        ]), 'parent' => $text('Parent Label', false), 'target' => $select('Target', [
            '_self' => 'Same Window', '_blank' => 'New Window',
        ]),
    ]],
    'customers' => ['title' => 'Customers', 'singular' => 'Customer', 'fields' => [
        'title' => $text('Full Name'), 'email' => $email('Email'),
        'phone' => $text('Phone', false), 'company' => $text('Company', false),
        'address' => $area('Address'), 'joined_at' => $date('Joined Date'),
    ]],
    'applications' => ['title' => 'Applications', 'singular' => 'Application', 'fields' => [
        'title' => $text('Application Title'), 'customer_email' => $email('Customer Email'),
        'application_type' => $text('Application Type'), 'reference' => $text('Reference Number', false),
        'notes' => $area('Notes'), 'submitted_at' => $date('Submitted Date'),
    ]],
    'documents' => ['title' => 'Documents', 'singular' => 'Document', 'fields' => [
        'title' => $text('Document Name'), 'customer_email' => $email('Customer Email'),
        'document_type' => $text('Document Type'), 'file_url' => $text('File URL'),
        'expires_at' => $date('Expiry Date'),
    ]],
    'notifications' => ['title' => 'Notifications', 'singular' => 'Notification', 'fields' => [
        'title' => $text('Subject'), 'recipient' => $text('Recipient'),
        'channel' => $select('Channel', ['email' => 'Email', 'portal' => 'Portal', 'sms' => 'SMS']),
        'message' => $area('Message', true), 'scheduled_at' => $date('Scheduled Date'),
    ]],
    'contact-messages' => ['title' => 'Contact Messages', 'singular' => 'Contact Message', 'fields' => [
        'title' => $text('Name'), 'email' => $email('Email'), 'phone' => $text('Phone', false),
        'subject' => $text('Subject'), 'message' => $area('Message', true),
    ]],
    'newsletter' => ['title' => 'Newsletter', 'singular' => 'Subscriber', 'fields' => [
        'title' => $text('Name', false), 'email' => $email('Email'),
        'source' => $text('Source', false), 'subscribed_at' => $date('Subscribed Date'),
    ]],
    'settings' => ['title' => 'General Settings', 'singular' => 'Setting', 'fields' => [
        'title' => $text('Setting Name'), 'key' => $text('Setting Key'),
        'value' => $area('Value', true), 'group' => $text('Group', false),
    ]],
    'seo' => ['title' => 'SEO Manager', 'singular' => 'SEO Entry', 'fields' => [
        'title' => $text('Page Name'), 'path' => $text('Page Path'),
        'meta_title' => $text('Meta Title'), 'meta_description' => $area('Meta Description'),
        'keywords' => $text('Keywords', false), 'canonical_url' => $text('Canonical URL', false),
    ]],
];
