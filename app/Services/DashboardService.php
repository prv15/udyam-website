<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use PDOException;

final class DashboardService
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function summary(): array
    {
        $portalCustomers = $this->safeCount(
            "SELECT COUNT(*) FROM users WHERE user_type = 'customer' AND status = 'active' AND deleted_at IS NULL"
        );
        $activeSubscriptions = $this->tableExists('customer_subscriptions')
            ? $this->safeCount("SELECT COUNT(DISTINCT customer_id, plan_id) FROM customer_subscriptions WHERE status = 'active'")
            : 0;
        $outstanding = $this->tableExists('customer_invoices')
            ? $this->safeAmount(
                "SELECT COALESCE(SUM(GREATEST(total_amount - paid_amount, 0)), 0)
                 FROM customer_invoices WHERE status IN ('issued','partial','overdue')"
            )
            : 0.0;
        $monthRevenue = $this->tableExists('customer_payments')
            ? $this->safeAmount(
                "SELECT COALESCE(SUM(amount), 0) FROM customer_payments
                 WHERE status = 'successful'
                   AND payment_date >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')"
            )
            : 0.0;
        $portalApplications = $this->tableExists('customer_service_requests')
            ? $this->safeCount('SELECT COUNT(*) FROM customer_service_requests')
            : 0;
        $pendingPortalApplications = $this->tableExists('customer_service_requests')
            ? $this->safeCount(
                "SELECT COUNT(*) FROM customer_service_requests
                 WHERE status IN ('submitted','under_review','information_requested')"
            )
            : 0;

        return [
            'pages' => $this->count('SELECT COUNT(*) FROM pages WHERE deleted_at IS NULL'),
            'publishedPages' => $this->safeCount("SELECT COUNT(*) FROM pages WHERE status = 'published' AND deleted_at IS NULL"),
            'services' => $this->moduleCount('services'),
            'applications' => max($portalApplications, $this->moduleCount('applications')),
            'customers' => max($portalCustomers, $this->moduleCount('customers')),
            'media' => $this->count('SELECT COUNT(*) FROM media WHERE deleted_at IS NULL'),
            'pendingApplications' => max($pendingPortalApplications, $this->moduleCount('applications', 'draft')),
            'newMessages' => $this->moduleCountByStatuses('contact-messages', ['new', 'active']),
            'activeSubscriptions' => $activeSubscriptions,
            'outstanding' => $outstanding,
            'monthRevenue' => $monthRevenue,
        ];
    }

    public function businessInsights(): array
    {
        $insights = [
            'activePlans' => 0,
            'expiringSoon' => 0,
            'pendingRequests' => 0,
            'overdueInvoices' => 0,
            'collectionRate' => 0.0,
            'previousMonthRevenue' => 0.0,
        ];

        if ($this->tableExists('subscription_plans')) {
            $insights['activePlans'] = $this->safeCount(
                "SELECT COUNT(*) FROM subscription_plans WHERE status = 'active' AND deleted_at IS NULL"
            );
        }
        if ($this->tableExists('customer_subscriptions')) {
            $insights['expiringSoon'] = $this->safeCount(
                "SELECT COUNT(*) FROM customer_subscriptions
                 WHERE status = 'active' AND expires_at BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)"
            );
        }
        if ($this->tableExists('customer_service_requests')) {
            $insights['pendingRequests'] = $this->safeCount(
                "SELECT COUNT(*) FROM customer_service_requests
                 WHERE status IN ('submitted','under_review','information_requested')"
            );
        }
        if ($this->tableExists('customer_invoices')) {
            $insights['overdueInvoices'] = $this->safeCount(
                "SELECT COUNT(*) FROM customer_invoices
                 WHERE status IN ('issued','partial','overdue') AND due_date < CURRENT_DATE"
            );
            $totals = $this->safeRow(
                "SELECT COALESCE(SUM(total_amount),0) billed, COALESCE(SUM(paid_amount),0) collected
                 FROM customer_invoices WHERE status <> 'cancelled'"
            );
            $billed = (float) ($totals['billed'] ?? 0);
            $insights['collectionRate'] = $billed > 0
                ? min(100, round(((float) ($totals['collected'] ?? 0) / $billed) * 100, 1))
                : 0.0;
        }
        if ($this->tableExists('customer_payments')) {
            $insights['previousMonthRevenue'] = $this->safeAmount(
                "SELECT COALESCE(SUM(amount),0) FROM customer_payments
                 WHERE status = 'successful'
                   AND payment_date >= DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH), '%Y-%m-01')
                   AND payment_date < DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')"
            );
        }

        return $insights;
    }

    /** Executive ERP metrics built only from the existing operational tables. */
    public function erpOverview(): array
    {
        $customers = [
            'total' => $this->safeCount("SELECT COUNT(*) FROM users WHERE user_type='customer' AND deleted_at IS NULL"),
            'active' => $this->safeCount("SELECT COUNT(*) FROM users WHERE user_type='customer' AND status='active' AND deleted_at IS NULL"),
            'inactive' => $this->safeCount("SELECT COUNT(*) FROM users WHERE user_type='customer' AND status<>'active' AND deleted_at IS NULL"),
            'new' => $this->safeCount("SELECT COUNT(*) FROM users WHERE user_type='customer' AND deleted_at IS NULL AND created_at>=DATE_FORMAT(CURRENT_DATE,'%Y-%m-01')"),
        ];
        $subscriptions = $this->statusCounts('customer_subscriptions', ['active','pending','expired','cancelled','suspended']);
        $applications = $this->statusCounts('customer_service_requests', ['submitted','under_review','approved','rejected','in_progress','completed']);
        $payments = $this->statusCounts('customer_payments', ['pending','successful','failed']);
        $documents = [
            'today' => $this->tableExists('customer_documents') ? $this->safeCount('SELECT COUNT(*) FROM customer_documents WHERE DATE(created_at)=CURRENT_DATE AND deleted_at IS NULL') : 0,
            'pending' => $this->tableExists('customer_documents') ? $this->safeCount("SELECT COUNT(*) FROM customer_documents WHERE status IN ('requested','uploaded') AND deleted_at IS NULL") : 0,
            'missing' => $this->tableExists('customer_documents') ? $this->safeCount("SELECT COUNT(*) FROM customer_documents WHERE status='requested' AND deleted_at IS NULL") : 0,
        ];
        $revenue = [
            'today' => $this->paymentAmount('payment_date>=CURDATE()'),
            'week' => $this->paymentAmount('payment_date>=DATE_SUB(CURDATE(),INTERVAL 6 DAY)'),
            'month' => $this->paymentAmount("payment_date>=DATE_FORMAT(CURRENT_DATE,'%Y-%m-01')"),
            'year' => $this->paymentAmount("payment_date>=DATE_FORMAT(CURRENT_DATE,'%Y-01-01')"),
        ];
        $lastMonth = $this->paymentAmount("payment_date>=DATE_FORMAT(DATE_SUB(CURRENT_DATE,INTERVAL 1 MONTH),'%Y-%m-01') AND payment_date<DATE_FORMAT(CURRENT_DATE,'%Y-%m-01')");
        $trend = $lastMonth > 0 ? round((($revenue['month'] - $lastMonth) / $lastMonth) * 100, 1) : 0.0;
        return [
            'customers' => $customers, 'subscriptions' => $subscriptions, 'applications' => $applications,
            'payments' => $payments, 'documents' => $documents, 'revenue' => $revenue,
            'revenueTrend' => $trend, 'renewalDue' => $this->tableExists('customer_subscriptions') ? $this->safeCount("SELECT COUNT(*) FROM customer_subscriptions WHERE status='active' AND expires_at BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE,INTERVAL 30 DAY)") : 0,
            'funding' => [
                'tenders' => $this->moduleCountByStatuses('tenders', ['published','active']),
                'notices' => $this->moduleCountByStatuses('tenders', ['published','active']),
                'schemes' => $this->moduleCountByStatuses('focus-areas', ['published','active']),
            ],
            'customerGrowth' => $this->customerGrowthTrend(),
            'paymentTrend' => $this->revenueTrend(),
        ];
    }

    public function revenueTrend(): array
    {
        $months = [];
        for ($offset = 5; $offset >= 0; $offset--) {
            $stamp = strtotime("-{$offset} months");
            $key = date('Y-m', $stamp);
            $months[$key] = ['label' => date('M', $stamp), 'value' => 0.0];
        }

        if ($this->tableExists('customer_payments')) {
            $rows = $this->safeRows(
                "SELECT DATE_FORMAT(payment_date, '%Y-%m') month_key, SUM(amount) value
                 FROM customer_payments
                 WHERE status = 'successful'
                   AND payment_date >= DATE_FORMAT(DATE_SUB(CURRENT_DATE, INTERVAL 5 MONTH), '%Y-%m-01')
                 GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
                 ORDER BY month_key"
            );
            foreach ($rows as $row) {
                $key = (string) $row['month_key'];
                if (isset($months[$key])) {
                    $months[$key]['value'] = (float) $row['value'];
                }
            }
        }

        return [
            'labels' => array_column($months, 'label'),
            'values' => array_column($months, 'value'),
        ];
    }

    public function subscriptionMix(): array
    {
        if (!$this->tableExists('subscription_plans') || !$this->tableExists('customer_subscriptions')) {
            return [];
        }

        return $this->safeRows(
            "SELECT sp.name, sp.billing_cycle, COUNT(DISTINCT cs.customer_id) subscriptions,
                    COALESCE(SUM(cs.amount),0) value
             FROM subscription_plans sp
             LEFT JOIN customer_subscriptions cs ON cs.plan_id = sp.id AND cs.status = 'active'
             WHERE sp.deleted_at IS NULL AND sp.status = 'active'
             GROUP BY sp.id, sp.name, sp.billing_cycle
             ORDER BY subscriptions DESC, sp.sort_order ASC
             LIMIT 5"
        );
    }

    public function applicationPipeline(): array
    {
        if ($this->tableExists('customer_service_requests')) {
            $rows = $this->safeRows(
                "SELECT status, COUNT(*) total FROM customer_service_requests GROUP BY status"
            );
        } else {
            $rows = $this->safeRows(
                "SELECT status, COUNT(*) total FROM admin_records
                 WHERE module = 'applications' AND deleted_at IS NULL GROUP BY status"
            );
        }

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = ucwords(str_replace('_', ' ', (string) $row['status']));
            $values[] = (int) $row['total'];
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function latestCustomers(int $limit = 5): array
    {
        if (!$this->tableExists('customer_profiles')) {
            return [];
        }

        return $this->safeRows(
            "SELECT u.id, u.first_name, u.last_name, u.email, u.status, u.created_at,
                    cp.company_name, cp.customer_record_id,
                    (SELECT COUNT(*) FROM customer_subscriptions cs
                     WHERE cs.customer_id = u.id AND cs.status = 'active') active_subscriptions
             FROM users u
             LEFT JOIN customer_profiles cp ON cp.user_id = u.id
             WHERE u.user_type = 'customer' AND u.deleted_at IS NULL
             ORDER BY u.created_at DESC
             LIMIT " . max(1, min(10, $limit))
        );
    }

    public function moduleDistribution(): array
    {
        $modules = ['services', 'blog', 'team', 'customers', 'applications', 'contact-messages'];
        $labels = [];
        $values = [];
        foreach ($modules as $module) {
            $labels[] = ucwords(str_replace('-', ' ', $module));
            $values[] = $this->moduleCount($module);
        }
        return ['labels' => $labels, 'values' => $values];
    }

    public function recentActivity(int $limit = 8): array
    {
        $recordStatement = $this->db->prepare(
            'SELECT module AS source, title, status AS action_status, updated_at
             FROM admin_records
             WHERE deleted_at IS NULL
             ORDER BY updated_at DESC
             LIMIT :limit'
        );
        $recordStatement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $recordStatement->execute();

        $pageStatement = $this->db->prepare(
            "SELECT 'pages' AS source, title, status AS action_status, updated_at
             FROM pages
             WHERE deleted_at IS NULL
             ORDER BY updated_at DESC
             LIMIT :limit"
        );
        $pageStatement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $pageStatement->execute();

        $activities = array_merge(
            $recordStatement->fetchAll(),
            $pageStatement->fetchAll()
        );

        usort(
            $activities,
            static fn (array $left, array $right): int =>
                strcmp((string) $right['updated_at'], (string) $left['updated_at'])
        );

        return array_slice($activities, 0, $limit);
    }

    public function systemStatus(): array
    {
        $total = @disk_total_space(BASE_PATH);
        $free = @disk_free_space(BASE_PATH);
        return [
            'database' => true,
            'php' => PHP_VERSION,
            'disk' => ($total && $free !== false) ? (int) round((($total - $free) / $total) * 100) : null,
            'https' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'),
            'environment' => (string) ($_ENV['APP_ENV'] ?? 'production'),
        ];
    }

    private function moduleCount(string $module, ?string $status = null): int
    {
        $sql = 'SELECT COUNT(*) FROM admin_records WHERE module = :module AND deleted_at IS NULL';
        $params = ['module' => $module];
        if ($status !== null) {
            $sql .= ' AND status = :status';
            $params['status'] = $status;
        }
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return (int) $statement->fetchColumn();
    }

    private function statusCounts(string $table, array $statuses): array
    {
        $result = array_fill_keys($statuses, 0);
        if (!$this->tableExists($table)) return $result;
        foreach ($this->safeRows("SELECT status,COUNT(*) total FROM {$table} GROUP BY status") as $row) {
            if (array_key_exists((string) $row['status'], $result)) $result[(string) $row['status']] = (int) $row['total'];
        }
        return $result;
    }

    private function paymentAmount(string $condition): float
    {
        if (!$this->tableExists('customer_payments')) return 0.0;
        return $this->safeAmount("SELECT COALESCE(SUM(amount),0) FROM customer_payments WHERE status='successful' AND {$condition}");
    }

    private function customerGrowthTrend(): array
    {
        $months = [];
        for ($offset = 5; $offset >= 0; $offset--) {
            $stamp = strtotime("-{$offset} months");
            $months[date('Y-m', $stamp)] = ['label' => date('M', $stamp), 'value' => 0];
        }
        foreach ($this->safeRows("SELECT DATE_FORMAT(created_at,'%Y-%m') period,COUNT(*) total FROM users WHERE user_type='customer' AND deleted_at IS NULL AND created_at>=DATE_FORMAT(DATE_SUB(CURRENT_DATE,INTERVAL 5 MONTH),'%Y-%m-01') GROUP BY period") as $row) {
            if (isset($months[(string) $row['period']])) $months[(string) $row['period']]['value'] = (int) $row['total'];
        }
        return ['labels' => array_column($months, 'label'), 'values' => array_column($months, 'value')];
    }

    private function moduleCountByStatuses(string $module, array $statuses): int
    {
        $statuses = array_values(array_filter($statuses, 'is_string'));
        if ($statuses === []) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($statuses), '?'));
        $statement = $this->db->prepare(
            "SELECT COUNT(*) FROM admin_records
             WHERE module = ? AND deleted_at IS NULL AND status IN ({$placeholders})"
        );
        $statement->execute(array_merge([$module], $statuses));

        return (int) $statement->fetchColumn();
    }

    private function count(string $sql): int
    {
        return (int) $this->db->query($sql)->fetchColumn();
    }

    private function safeCount(string $sql): int
    {
        try {
            return (int) $this->db->query($sql)->fetchColumn();
        } catch (PDOException) {
            return 0;
        }
    }

    private function safeAmount(string $sql): float
    {
        try {
            return (float) $this->db->query($sql)->fetchColumn();
        } catch (PDOException) {
            return 0.0;
        }
    }

    private function safeRow(string $sql): array
    {
        try {
            return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException) {
            return [];
        }
    }

    private function safeRows(string $sql): array
    {
        try {
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
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
}
