<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Config\Database;

$db = Database::connection();
$monthlyBenefits = [
    ['title' => 'Weekly Project Bulletins', 'description' => 'Curated government, CSR, RFP and funding opportunities.'],
    ['title' => 'High-Value Project Highlights', 'description' => 'Detailed analysis covering eligibility, funding amount and timeline.'],
    ['title' => 'Instant Project Alerts', 'description' => 'New projects and deadline extensions through WhatsApp and email.'],
    ['title' => 'Monthly Funding Calendar', 'description' => 'Upcoming funding opportunities and important deadlines.'],
    ['title' => 'Expert Webinar', 'description' => 'Proposal writing, CSR, tenders and government funding sessions.'],
];
$annualBenefits = [
    ['title' => 'All Monthly Plan Benefits', 'description' => 'Every bulletin, alert, calendar and webinar benefit is included.'],
    ['title' => 'Project Filters & Highlights', 'description' => 'Access project details with complete eligibility and funding insights.'],
    ['title' => 'Priority Project Alerts', 'description' => 'Receive high-priority projects before standard circulation.'],
    ['title' => 'Advanced Project Profile Report', 'description' => 'Quarterly in-depth analysis of your organization profile.'],
    ['title' => 'One-to-One Guidance & Support', 'description' => 'Personalized guidance to help you pursue suitable funding opportunities.'],
];
$plans = [
    [
        'name' => 'Udyam Project Partner', 'slug' => 'project-funding-monthly',
        'category' => 'Project Funding', 'subtitle' => 'Monthly Plan', 'badge' => 'Start Flexible',
        'description' => 'A flexible monthly membership for organizations exploring project funding opportunities.',
        'ideal_for' => 'Ideal for organizations that want to try the programme before committing annually.',
        'highlight_text' => 'Pay monthly. Cancel anytime. No long-term commitment.',
        'billing_cycle' => 'monthly', 'price' => 999, 'gst_rate' => 18, 'gst_inclusive' => 1,
        'initial_payment' => 999, 'followup_payment' => null, 'followup_due_days' => null,
        'featured' => 0, 'benefits' => $monthlyBenefits, 'sort_order' => 10,
    ],
    [
        'name' => 'Project Funding Annual - Full Payment', 'slug' => 'project-funding-annual-full',
        'category' => 'Project Funding', 'subtitle' => 'Annual Plan - Option 1', 'badge' => 'Best Value',
        'description' => 'Complete annual access with advanced funding intelligence and personalized support.',
        'ideal_for' => 'Best for organizations ready to build a sustained project funding pipeline.',
        'highlight_text' => '₹25,000 now - equivalent to approximately ₹2,083 per month.',
        'billing_cycle' => 'yearly', 'price' => 25000, 'gst_rate' => 18, 'gst_inclusive' => 1,
        'initial_payment' => 25000, 'followup_payment' => null, 'followup_due_days' => null,
        'featured' => 1, 'benefits' => $annualBenefits, 'sort_order' => 20,
    ],
    [
        'name' => 'Project Funding Annual - Flexible Instalments', 'slug' => 'project-funding-annual-flexible',
        'category' => 'Project Funding', 'subtitle' => 'Annual Plan - Option 2', 'badge' => 'Flexible',
        'description' => 'Annual membership split into two manageable payments without reducing plan benefits.',
        'ideal_for' => 'Designed for organizations preferring a phased annual payment schedule.',
        'highlight_text' => '₹15,000 now and ₹12,000 after 60 days.',
        'billing_cycle' => 'yearly', 'price' => 27000, 'gst_rate' => 18, 'gst_inclusive' => 1,
        'initial_payment' => 15000, 'followup_payment' => 12000, 'followup_due_days' => 60,
        'featured' => 0, 'benefits' => $annualBenefits, 'sort_order' => 30,
    ],
];

$statement = $db->prepare(
    'INSERT INTO subscription_plans
     (name,slug,category,subtitle,badge,description,ideal_for,highlight_text,billing_cycle,price,gst_rate,
      gst_inclusive,initial_payment,followup_payment,followup_due_days,featured,benefits,status,sort_order,disclaimer)
     VALUES
     (:name,:slug,:category,:subtitle,:badge,:description,:ideal_for,:highlight_text,:billing_cycle,:price,:gst_rate,
      :gst_inclusive,:initial_payment,:followup_payment,:followup_due_days,:featured,:benefits,"active",:sort_order,:disclaimer)
     ON DUPLICATE KEY UPDATE
      name=VALUES(name),category=VALUES(category),subtitle=VALUES(subtitle),badge=VALUES(badge),
      description=VALUES(description),ideal_for=VALUES(ideal_for),highlight_text=VALUES(highlight_text),
      billing_cycle=VALUES(billing_cycle),price=VALUES(price),gst_rate=VALUES(gst_rate),
      gst_inclusive=VALUES(gst_inclusive),initial_payment=VALUES(initial_payment),
      followup_payment=VALUES(followup_payment),followup_due_days=VALUES(followup_due_days),
      featured=VALUES(featured),benefits=VALUES(benefits),status="active",sort_order=VALUES(sort_order),
      disclaimer=VALUES(disclaimer),deleted_at=NULL'
);

foreach ($plans as $plan) {
    $statement->execute(array_merge($plan, [
        'benefits' => json_encode($plan['benefits'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'disclaimer' => 'Udyam does not guarantee project sanction. Submission charges are separate from subscription fees.',
    ]));
}

// Retire only the earlier development placeholder so the live catalogue shows
// the approved Project Funding products from the supplied plan sheet.
$db->prepare("UPDATE subscription_plans SET status='archived' WHERE slug='business-advisory-demo'")
    ->execute();

echo "Project Funding subscription plans are ready.\n";
