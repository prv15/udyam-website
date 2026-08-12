<?php

return [

    'MAIN' => [
        [
            'title' => 'Dashboard',
            'icon'  => 'layout-dashboard',
            'route' => '/admin/dashboard',
            'enabled' => true,
        ],
    ],

    'WEBSITE' => [
        [
            'title' => 'Pages',
            'icon'  => 'file-text',
            'route' => '/admin/pages',
            'enabled' => true,
        ],
        [
            'title' => 'Services',
            'icon'  => 'briefcase-business',
            'route' => '/admin/services',
        ],
        [
            'title' => 'Focus Areas',
            'icon'  => 'target',
            'route' => '/admin/focus-areas',
        ],
        [
            'title' => 'Blog',
            'icon'  => 'newspaper',
            'route' => '/admin/blog',
        ],
        [
            'title' => 'Tenders & Notices',
            'icon'  => 'megaphone',
            'route' => '/admin/tenders',
        ],
        [
            'title' => 'Testimonials',
            'icon'  => 'message-square-quote',
            'route' => '/admin/testimonials',
        ],
        [
            'title' => 'FAQs',
            'icon'  => 'circle-help',
            'route' => '/admin/faqs',
        ],
        [
            'title' => 'Team Members',
            'icon'  => 'users',
            'route' => '/admin/team',
        ],
        [
            'title' => 'Partners',
            'icon'  => 'handshake',
            'route' => '/admin/partners',
        ],
        [
            'title' => 'Media Library',
            'icon'  => 'images',
            'route' => '/admin/media',
            'enabled' => true,
        ],
        [
            'title' => 'Menu Manager',
            'icon'  => 'menu-square',
            'route' => '/admin/menu',
        ],
    ],

    'PARTNER PORTAL' => [
        [
            'title' => 'Partners',
            'icon'  => 'users-round',
            'route' => '/admin/customers',
            'children' => [
                ['title' => 'Partner List', 'route' => '/admin/customers'],
                ['title' => 'Companies', 'route' => '/admin/customers?view=companies'],
                ['title' => 'Partner Activity', 'route' => '/admin/customers?view=activity'],
            ],
        ],
        [
            'title' => 'Applications',
            'icon'  => 'clipboard-list',
            'route' => '/admin/applications',
            'children' => [
                ['title' => 'All Applications', 'route' => '/admin/applications'],
                ['title' => 'Pending Review', 'route' => '/admin/applications?status=submitted'],
                ['title' => 'Approved', 'route' => '/admin/applications?status=approved'],
                ['title' => 'Rejected', 'route' => '/admin/applications?status=rejected'],
            ],
        ],
        [
            'title' => 'Documents',
            'icon'  => 'folder-open',
            'route' => '/admin/documents',
        ],
        [
            'title' => 'Notifications',
            'icon'  => 'bell-ring',
            'route' => '/admin/notification-center',
        ],
        [
            'title' => 'Subscriptions', 'icon'  => 'badge-indian-rupee', 'route' => '/admin/subscription-plans',
            'children' => [
                ['title' => 'Subscription Plans', 'route' => '/admin/subscription-plans'],
                ['title' => 'Active Subscriptions', 'route' => '/admin/customers?view=subscriptions'],
                ['title' => 'Requests to Review', 'route' => '/admin/subscription-requests'],
            ],
        ],
        [
            'title' => 'Billing', 'icon' => 'receipt-indian-rupee', 'route' => '/admin/customers?view=billing',
            'children' => [
                ['title' => 'Invoices', 'route' => '/admin/customers?view=invoices'],
                ['title' => 'Payments', 'route' => '/admin/customers?view=payments'],
                ['title' => 'Revenue Dashboard', 'route' => '/admin/dashboard#revenue'],
            ],
        ],
    ],

    'UDYAM VENTURES' => [
        [
            'title' => 'Staff Management',
            'icon' => 'contact-round',
            'route' => '/admin/staff',
        ],
        [
            'title' => 'Digital Business Cards',
            'icon' => 'scan-qr-code',
            'route' => '/admin/digital-business-cards',
        ],
        [
            'title' => 'Projects',
            'icon' => 'folder-kanban',
            'route' => '/admin/projects',
            'enabled' => false,
            'children' => [
                ['title' => 'Project Management', 'route' => '/admin/projects'],
                ['title' => 'Task Management', 'route' => '/admin/tasks'],
            ],
        ],
    ],

    'ENQUIRIES' => [
        [
            'title' => 'Contact Messages',
            'icon'  => 'mail',
            'route' => '/admin/contact-messages',
        ],
        [
            'title' => 'Newsletter',
            'icon'  => 'mail-plus',
            'route' => '/admin/newsletter',
        ],
    ],

    'SETTINGS' => [
        [
            'title' => 'System Updates',
            'icon'  => 'database-zap',
            'route' => '/admin/system/migrations',
        ],
        [
            'title' => 'General Settings',
            'icon'  => 'globe',
            'route' => '/admin/settings',
        ],
        [
            'title' => 'SEO Manager',
            'icon'  => 'search',
            'route' => '/admin/seo',
        ],
        [
            'title' => 'Users & Roles',
            'icon'  => 'shield-check',
            'route' => '/admin/users',
        ],
        [
            'title' => 'Roles & Permissions',
            'icon' => 'shield-ellipsis',
            'route' => '/admin/roles',
        ],
        [
            'title' => 'My Profile',
            'icon'  => 'user-circle',
            'route' => '/admin/profile',
        ],
        [
            'title' => 'Change Password',
            'icon'  => 'key-round',
            'route' => '/admin/change-password',
        ],
    ],

];
