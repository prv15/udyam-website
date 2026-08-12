<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Config\Database;

/**
 * A deliberately narrow, authenticated migration runner for hosting accounts
 * without shell access. It runs only the additive ERP extension; database
 * folders remain blocked from public web access by .htaccess.
 */
final class SystemMigrationController extends AdminController
{
    private const ERP_MIGRATION = BASE_PATH . '/database/migrations/20260807_enterprise_erp_extension.sql';
    private const STAFF_MIGRATION = BASE_PATH . '/database/migrations/20260807_staff_business_cards.sql';

    public function index(): void
    {
        $this->render('system/migrations', [
            'title' => 'System Updates',
            'erpInstalled' => $this->tableExists('customer_support_tickets')
                && $this->tableExists('admin_notifications')
                && $this->tableExists('subscription_requests')
                && $this->tableExists('tender_deadline_events'),
            'staffInstalled' => $this->columnExists('users', 'employee_code')
                && $this->tableExists('roles')
                && $this->tableExists('digital_business_cards')
                && $this->tableExists('digital_card_events'),
        ]);
    }

    public function applyStaffManagement(): never
    {
        if (!csrf_validate()) {
            $this->redirectError('/admin/system/migrations', 'Your session expired. Please refresh and try again.');
        }
        if (trim((string) ($_POST['confirmation'] ?? '')) !== 'RUN STAFF MIGRATION') {
            $this->redirectError('/admin/system/migrations', 'Type RUN STAFF MIGRATION to confirm the database update.');
        }
        try {
            $sql = (string) file_get_contents(self::STAFF_MIGRATION);
            if ($sql === '') { throw new \RuntimeException('The Staff migration file could not be read.'); }
            $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) { Database::connection()->exec($statement); }
            $this->redirectSuccess('/admin/system/migrations', 'Staff Management and Digital Business Card tables were installed successfully.');
        } catch (\Throwable $exception) {
            error_log('Staff migration runner failed: ' . $exception->getMessage());
            $this->redirectError('/admin/system/migrations', 'The Staff Management update could not be completed. Check the server error log.');
        }
    }

    public function applyEnterpriseExtension(): never
    {
        if (!csrf_validate()) {
            $this->redirectError('/admin/system/migrations', 'Your session expired. Please refresh and try again.');
        }

        if (trim((string) ($_POST['confirmation'] ?? '')) !== 'RUN ERP MIGRATION') {
            $this->redirectError('/admin/system/migrations', 'Type RUN ERP MIGRATION to confirm the database update.');
        }

        try {
            $sql = (string) file_get_contents(self::ERP_MIGRATION);
            if ($sql === '') {
                throw new \RuntimeException('The ERP migration file could not be read.');
            }

            $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
                Database::connection()->exec($statement);
            }

            $this->redirectSuccess('/admin/system/migrations', 'Enterprise ERP and tender deadline automation tables were updated successfully.');
        } catch (\Throwable $exception) {
            error_log('ERP migration runner failed: ' . $exception->getMessage());
            $this->redirectError('/admin/system/migrations', 'The update could not be completed. Check database credentials and contact support if it continues.');
        }
    }

    private function tableExists(string $table): bool
    {
        $statement = Database::connection()->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=?');
        $statement->execute([$table]);
        return (int) $statement->fetchColumn() > 0;
    }

    private function columnExists(string $table, string $column): bool
    {
        $statement = Database::connection()->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=? AND column_name=?');
        $statement->execute([$table, $column]);
        return (int) $statement->fetchColumn() > 0;
    }
}
