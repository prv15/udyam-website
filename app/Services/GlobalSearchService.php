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

        return array_slice($results, 0, $limit);
    }

    private function searchPages(string $like, int $limit): array
    {
        return $this->query(
            "SELECT id, title, slug, status
             FROM pages
             WHERE deleted_at IS NULL
               AND (title LIKE :term_title ESCAPE '\\\\' OR slug LIKE :term_slug ESCAPE '\\\\')
             ORDER BY updated_at DESC LIMIT {$limit}",
            ['term_title' => $like, 'term_slug' => $like],
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
               AND (title LIKE :term_title ESCAPE '\\\\' OR slug LIKE :term_slug ESCAPE '\\\\')
             ORDER BY updated_at DESC LIMIT {$limit}",
            ['term_title' => $like, 'term_slug' => $like],
            static fn (array $row): array => [
                'group' => self::recordGroup((string) $row['module']),
                'type' => ucwords(str_replace('-', ' ', (string) $row['module'])),
                'icon' => self::recordIcon((string) $row['module']),
                'title' => (string) $row['title'],
                'meta' => ucfirst((string) $row['status']),
                'url' => url('/admin/' . rawurlencode((string) $row['module']) . '/edit/' . (int) $row['id']),
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
                    'group' => 'Customers',
                    'type' => 'Customer',
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
            "SELECT id, name, billing_cycle, price, status
             FROM subscription_plans
             WHERE deleted_at IS NULL
               AND name LIKE :term ESCAPE '\\\\'
             ORDER BY updated_at DESC LIMIT {$limit}",
            ['term' => $like],
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
            $statement = $this->db->prepare('SHOW TABLES LIKE :table');
            $statement->execute(['table' => $table]);
            return (bool) $statement->fetchColumn();
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
