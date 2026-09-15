<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use PDOException;

final class GlobalSearchService
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function search(string $query, int $limit = 24): array
    {
        $query = trim(preg_replace('/\s+/', ' ', $query) ?? '');
        if (mb_strlen($query) < 2) {
            return [];
        }

        $like = '%' . $this->escapeLike($query) . '%';
        $perSource = max(3, min(8, (int) ceil($limit / 5)));
        $results = [];

        $results = array_merge($results, $this->searchPages($like, $perSource));
        $results = array_merge($results, $this->searchRecords($like, $perSource * 2));
        $results = array_merge($results, $this->searchCustomers($like, $perSource));
        $results = array_merge($results, $this->searchPlans($like, $perSource));
        $results = array_merge($results, $this->searchInvoices($like, $perSource));
        $results = array_merge($results, $this->searchMedia($like, $perSource));
        $results = array_merge($results, $this->searchUsers($like, $perSource));
        $results = array_merge($results, $this->searchCustomerSubscriptions($like, $perSource));
        $results = array_merge($results, $this->searchCustomerApplications($like, $perSource));
        $results = array_merge($results, $this->searchCustomerDocuments($like, $perSource));
        $results = array_merge($results, $this->searchPayments($like, $perSource));

        return array_slice($results, 0, $limit);
    }

    private function searchPages(string $like, int $limit): array
    {
        return $this->query(
            "SELECT id, title, slug, status
             FROM pages
             WHERE deleted_at IS NULL
               AND (title LIKE :term_title ESCAPE '\\\\' OR slug LIKE :term_slug ESCAPE '\\\\'
                    OR excerpt LIKE :term_excerpt ESCAPE '\\\\' OR content LIKE :term_content ESCAPE '\\\\')
             ORDER BY updated_at DESC LIMIT {$limit}",
            ['term_title' => $like, 'term_slug' => $like, 'term_excerpt' => $like, 'term_content' => $like],
            static fn (array $row): array => [
                'group' => 'Website',
                'type' => 'Page',
                'icon' => 'file-text',
                'title' => (string) $row['title'],
                'meta' => '/' . (string) $row['slug'] . ' · ' . ucfirst((string) $row['status']),
                'url' => url('/admin/pages/edit/' . (int) $row['id']),
            ]
        );
    }

    private function searchRecords(string $like, int $limit): array
    {
        return $this->query(
            "SELECT id, module, title, slug, status
             FROM admin_records
             WHERE deleted_at IS NULL
               AND (title LIKE :term_title ESCAPE '\\\\' OR slug LIKE :term_slug ESCAPE '\\\\'
                    OR data LIKE :term_data ESCAPE '\\\\')
             ORDER BY updated_at DESC LIMIT {$limit}",
            ['term_title' => $like, 'term_slug' => $like, 'term_data' => $like],
            static fn (array $row): array => [
                'group' => self::recordGroup((string) $row['module']),
                'type' => (string) $row['module'] === 'customers'
                    ? 'Partner'
                    : ucwords(str_replace('-', ' ', (string) $row['module'])),
                'icon' => self::recordIcon((string) $row['module']),
                'title' => (string) $row['title'],
                'meta' => ucfirst((string) $row['status']),
                'url' => (string) $row['module'] === 'customers'
                    ? url('/admin/customers/workspace/' . (int) $row['id'])
                    : url('/admin/' . rawurlencode((string) $row['module']) . '/edit/' . (int) $row['id']),
            ]
        );
    }

    private function searchCustomers(string $like, int $limit): array
    {
        if (!$this->tableExists('customer_profiles')) {
            return [];
        }

        return $this->query(
            "SELECT u.id, u.first_name, u.last_name, u.email, cp.company_name, cp.mobile, cp.customer_record_id
             FROM users u
             LEFT JOIN customer_profiles cp ON cp.user_id = u.id
             WHERE u.user_type = 'customer' AND u.deleted_at IS NULL
               AND (u.first_name LIKE :term_first ESCAPE '\\\\'
                    OR u.last_name LIKE :term_last ESCAPE '\\\\'
                    OR u.email LIKE :term_email ESCAPE '\\\\'
                    OR cp.company_name LIKE :term_company ESCAPE '\\\\'
                    OR cp.mobile LIKE :term_mobile ESCAPE '\\\\')
             ORDER BY u.updated_at DESC LIMIT {$limit}",
            [
                'term_first' => $like,
                'term_last' => $like,
                'term_email' => $like,
                'term_company' => $like,
                'term_mobile' => $like,
            ],
            static function (array $row): array {
                $name = trim((string) $row['first_name'] . ' ' . (string) $row['last_name']);
                $company = trim((string) ($row['company_name'] ?? ''));
                return [
                    'group' => 'Partners',
                    'type' => 'Partner',
                    'icon' => 'user-round',
                    'title' => $company !== '' ? $company : $name,
                    'meta' => $name . ' · ' . (string) $row['email'],
                    'url' => !empty($row['customer_record_id'])
                        ? url('/admin/customers/workspace/' . (int) $row['customer_record_id'])
                        : url('/admin/customers'),
                ];
            }
        );
    }

    private function searchPlans(string $like, int $limit): array
    {
        if (!$this->tableExists('subscription_plans')) {
            return [];
        }

        return $this->query(
            "SELECT id, name, description, billing_cycle, price, status
             FROM subscription_plans
             WHERE deleted_at IS NULL
               AND (name LIKE :term_name ESCAPE '\\\\'
                    OR description LIKE :term_description ESCAPE '\\\\'
                    OR billing_cycle LIKE :term_cycle ESCAPE '\\\\')
             ORDER BY updated_at DESC LIMIT {$limit}",
            ['term_name' => $like, 'term_description' => $like, 'term_cycle' => $like],
            static fn (array $row): array => [
                'group' => 'Subscriptions',
                'type' => 'Plan',
                'icon' => 'badge-indian-rupee',
                'title' => (string) $row['name'],
                'meta' => '₹' . number_format((float) $row['price']) . ' · ' . str_replace('_', ' ', (string) $row['billing_cycle']),
                'url' => url('/admin/subscription-plans/edit/' . (int) $row['id']),
            ]
        );
    }

    private function searchMedia(string $like, int $limit): array
    {
        if (!$this->tableExists('media')) return [];
        return $this->query(
            "SELECT id, original_name, title, alt_text, folder FROM media WHERE deleted_at IS NULL
             AND (original_name LIKE :name ESCAPE '\\\\' OR title LIKE :title ESCAPE '\\\\' OR alt_text LIKE :alt ESCAPE '\\\\')
             ORDER BY updated_at DESC LIMIT {$limit}",
            ['name' => $like, 'title' => $like, 'alt' => $like],
            static fn (array $row): array => [
                'group' => 'Website Assets', 'type' => 'Media', 'icon' => 'image',
                'title' => (string) ($row['title'] ?: $row['original_name']),
                'meta' => trim((string) ($row['folder'] ?? '')) ?: 'Media library',
                'url' => url('/admin/media'),
            ]
        );
    }

    private function searchUsers(string $like, int $limit): array
    {
        if (!$this->tableExists('users')) return [];
        return $this->query(
            "SELECT id,first_name,last_name,email,user_type,status FROM users WHERE deleted_at IS NULL
             AND (first_name LIKE :first ESCAPE '\\\\' OR last_name LIKE :last ESCAPE '\\\\' OR email LIKE :email ESCAPE '\\\\')
             ORDER BY updated_at DESC LIMIT {$limit}",
            ['first' => $like, 'last' => $like, 'email' => $like],
            static fn (array $row): array => [
                'group' => match ((string) $row['user_type']) { 'admin' => 'Administration', 'staff' => 'Udyam Staff', default => 'Partners' },
                'type' => ucfirst((string) $row['user_type']) . ' user', 'icon' => 'user-round',
                'title' => trim((string) $row['first_name'] . ' ' . (string) $row['last_name']),
                'meta' => (string) $row['email'] . ' · ' . ucfirst((string) $row['status']),
                'url' => match ((string) $row['user_type']) { 'admin' => url('/admin/users/edit/' . (int) $row['id']), 'staff' => url('/admin/staff/' . (int) $row['id']), default => url('/admin/customers') },
            ]
        );
    }

    private function searchCustomerSubscriptions(string $like, int $limit): array
    {
        if (!$this->tableExists('customer_subscriptions') || !$this->tableExists('customer_profiles')) return [];
        return $this->query(
            "SELECT cs.customer_id,cs.status,cs.expires_at,sp.name,u.first_name,u.last_name,cp.customer_record_id
             FROM customer_subscriptions cs JOIN subscription_plans sp ON sp.id=cs.plan_id JOIN users u ON u.id=cs.customer_id
             LEFT JOIN customer_profiles cp ON cp.user_id=u.id
             WHERE sp.name LIKE :plan ESCAPE '\\\\' OR u.first_name LIKE :first ESCAPE '\\\\' OR u.last_name LIKE :last ESCAPE '\\\\'
             ORDER BY cs.updated_at DESC LIMIT {$limit}",
            ['plan' => $like, 'first' => $like, 'last' => $like],
            static fn (array $row): array => [
                'group' => 'Subscriptions', 'type' => 'Customer membership', 'icon' => 'badge-indian-rupee',
                'title' => (string) $row['name'],
                'meta' => trim((string) $row['first_name'] . ' ' . (string) $row['last_name']) . ' · ' . ucfirst((string) $row['status']),
                'url' => !empty($row['customer_record_id']) ? url('/admin/customers/workspace/' . (int) $row['customer_record_id'] . '?tab=subscriptions') : url('/admin/customers'),
            ]
        );
    }

    private function searchCustomerApplications(string $like, int $limit): array
    {
        if (!$this->tableExists('customer_service_requests')) return [];
        return $this->query(
            "SELECT r.id,r.customer_id,r.application_number,r.project_name,r.status,s.title service_name,cp.customer_record_id
             FROM customer_service_requests r JOIN admin_records s ON s.id=r.service_record_id
             LEFT JOIN customer_profiles cp ON cp.user_id=r.customer_id
             WHERE r.application_number LIKE :number ESCAPE '\\\\' OR r.project_name LIKE :project ESCAPE '\\\\' OR s.title LIKE :service ESCAPE '\\\\'
             ORDER BY r.updated_at DESC LIMIT {$limit}",
            ['number' => $like, 'project' => $like, 'service' => $like],
            static fn (array $row): array => [
                'group' => 'Customer Operations', 'type' => 'Portal application', 'icon' => 'clipboard-list',
                'title' => (string) ($row['project_name'] ?: $row['service_name']),
                'meta' => (string) $row['application_number'] . ' · ' . ucfirst(str_replace('_', ' ', (string) $row['status'])),
                'url' => !empty($row['customer_record_id']) ? url('/admin/customers/workspace/' . (int) $row['customer_record_id'] . '?tab=applications') : url('/admin/applications'),
            ]
        );
    }

    private function searchCustomerDocuments(string $like, int $limit): array
    {
        if (!$this->tableExists('customer_documents')) return [];
        return $this->query(
            "SELECT d.id,d.customer_id,d.title,d.category,d.status,cp.customer_record_id FROM customer_documents d
             LEFT JOIN customer_profiles cp ON cp.user_id=d.customer_id
             WHERE d.deleted_at IS NULL AND (d.title LIKE :title ESCAPE '\\\\' OR d.original_name LIKE :name ESCAPE '\\\\')
             ORDER BY d.updated_at DESC LIMIT {$limit}",
            ['title' => $like, 'name' => $like],
            static fn (array $row): array => [
                'group' => 'Customer Operations', 'type' => 'Customer document', 'icon' => 'folder-open',
                'title' => (string) $row['title'], 'meta' => ucfirst((string) $row['category']) . ' · ' . ucfirst((string) $row['status']),
                'url' => !empty($row['customer_record_id']) ? url('/admin/customers/workspace/' . (int) $row['customer_record_id'] . '?tab=documents') : url('/admin/documents'),
            ]
        );
    }

    private function searchPayments(string $like, int $limit): array
    {
        if (!$this->tableExists('customer_payments')) return [];
        return $this->query(
            "SELECT p.customer_id,p.transaction_id,p.amount,p.status,i.invoice_number,cp.customer_record_id
             FROM customer_payments p JOIN customer_invoices i ON i.id=p.invoice_id
             LEFT JOIN customer_profiles cp ON cp.user_id=p.customer_id
             WHERE p.transaction_id LIKE :transaction ESCAPE '\\\\' OR i.invoice_number LIKE :invoice ESCAPE '\\\\'
             ORDER BY p.payment_date DESC LIMIT {$limit}",
            ['transaction' => $like, 'invoice' => $like],
            static fn (array $row): array => [
                'group' => 'Billing', 'type' => 'Payment', 'icon' => 'circle-check-big',
                'title' => (string) ($row['transaction_id'] ?: $row['invoice_number']),
                'meta' => '₹' . number_format((float) $row['amount']) . ' · ' . ucfirst((string) $row['status']),
                'url' => !empty($row['customer_record_id']) ? url('/admin/customers/workspace/' . (int) $row['customer_record_id'] . '?tab=payments') : url('/admin/customers'),
            ]
        );
    }

    private function searchInvoices(string $like, int $limit): array
    {
        if (!$this->tableExists('customer_invoices')) {
            return [];
        }

        return $this->query(
            "SELECT ci.id, ci.customer_id, ci.invoice_number, ci.invoice_type, ci.total_amount,
                    ci.status, u.first_name, u.last_name, cp.customer_record_id
             FROM customer_invoices ci
             JOIN users u ON u.id = ci.customer_id
             LEFT JOIN customer_profiles cp ON cp.user_id = u.id
             WHERE ci.invoice_number LIKE :term_invoice ESCAPE '\\\\'
                OR u.first_name LIKE :term_first ESCAPE '\\\\'
                OR u.last_name LIKE :term_last ESCAPE '\\\\'
             ORDER BY ci.updated_at DESC LIMIT {$limit}",
            ['term_invoice' => $like, 'term_first' => $like, 'term_last' => $like],
            static fn (array $row): array => [
                'group' => 'Billing',
                'type' => ucfirst((string) $row['invoice_type']) . ' invoice',
                'icon' => 'receipt-indian-rupee',
                'title' => (string) $row['invoice_number'],
                'meta' => trim((string) $row['first_name'] . ' ' . (string) $row['last_name'])
                    . ' · ₹' . number_format((float) $row['total_amount']) . ' · ' . ucfirst((string) $row['status']),
                'url' => !empty($row['customer_record_id'])
                    ? url('/admin/customers/workspace/' . (int) $row['customer_record_id'] . '?tab=billing')
                    : url('/admin/customers'),
            ]
        );
    }

    private function query(string $sql, array $params, callable $map): array
    {
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute($params);
            return array_map($map, $statement->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException) {
            return [];
        }
    }

    private function tableExists(string $table): bool
    {
        try {
            $statement = $this->db->prepare(
                'SELECT COUNT(*) FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name = :table'
            );
            $statement->execute(['table' => $table]);
            return (int) $statement->fetchColumn() > 0;
        } catch (PDOException) {
            return false;
        }
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    private static function recordGroup(string $module): string
    {
        return match ($module) {
            'customers', 'applications', 'documents', 'notifications' => 'Customer Operations',
            'contact-messages', 'newsletter' => 'Enquiries',
            default => 'Website',
        };
    }

    private static function recordIcon(string $module): string
    {
        return match ($module) {
            'services' => 'briefcase-business',
            'applications' => 'clipboard-list',
            'customers' => 'users-round',
            'documents' => 'folder-open',
            'contact-messages' => 'mail',
            'blog' => 'newspaper',
            'tenders' => 'megaphone',
            default => 'database',
        };
    }
}
