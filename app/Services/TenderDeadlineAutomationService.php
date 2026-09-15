<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use PDO;
use Throwable;

final class TenderDeadlineAutomationService
{
    public function __construct(private readonly PDO $db)
    {
    }

    /**
     * @return array{tenders_checked:int,expired:int,admin_notifications:int,customer_notifications:int,dry_run:bool}
     */
    public function run(?DateTimeImmutable $today = null, bool $dryRun = false): array
    {
        $today ??= new DateTimeImmutable('today');
        if (!$dryRun) $this->assertSchemaReady();
        $summary = [
            'tenders_checked' => 0,
            'expired' => 0,
            'admin_notifications' => 0,
            'customer_notifications' => 0,
            'dry_run' => $dryRun,
        ];

        $tenders = $this->activeTenders();
        $summary['tenders_checked'] = count($tenders);
        $customers = $this->activeSubscribers();
        $reminderDays = $this->reminderDays();

        foreach ($tenders as $tender) {
            $deadline = $this->deadline($tender);
            if ($deadline === null) continue;

            $daysRemaining = (int) $today->diff($deadline)->format('%r%a');
            if ($daysRemaining < 0) {
                $eventKey = 'expired:' . $deadline->format('Y-m-d');
                if ($dryRun) {
                    $summary['expired']++;
                    $summary['admin_notifications']++;
                    continue;
                }
                [$expired, $notified] = $this->expireAndNotifyAdmin($tender, $eventKey, $deadline);
                if ($expired) $summary['expired']++;
                if ($notified) {
                    $summary['admin_notifications']++;
                }
                continue;
            }

            if (!in_array($daysRemaining, $reminderDays, true)) continue;
            $eventKey = 'reminder:' . $daysRemaining . ':' . $deadline->format('Y-m-d');
            $title = $daysRemaining === 1 ? 'Tender closes tomorrow' : 'Tender closes in ' . $daysRemaining . ' days';
            $message = $this->reminderMessage($tender, $deadline, $daysRemaining);

            if ($dryRun) {
                $summary['admin_notifications']++;
                $summary['customer_notifications'] += count($customers);
                continue;
            }

            if ($this->notifyAdmin($tender, $eventKey, $title, $message)) {
                $summary['admin_notifications']++;
            }
            foreach ($customers as $customerId) {
                if ($this->notifyCustomer($customerId, $tender, $eventKey, $title, $message)) {
                    $summary['customer_notifications']++;
                }
            }
        }

        return $summary;
    }

    private function activeTenders(): array
    {
        return $this->db->query(
            "SELECT id,title,data FROM admin_records
             WHERE module='tenders' AND status IN ('active','published') AND deleted_at IS NULL
             ORDER BY id"
        )->fetchAll();
    }

    /** @return list<int> */
    private function activeSubscribers(): array
    {
        $rows = $this->db->query(
            "SELECT DISTINCT cs.customer_id FROM customer_subscriptions cs
             JOIN users u ON u.id=cs.customer_id
             WHERE cs.status='active' AND (cs.expires_at IS NULL OR cs.expires_at>=CURDATE())
               AND u.user_type='customer' AND u.status='active' AND u.deleted_at IS NULL"
        )->fetchAll(PDO::FETCH_COLUMN);
        return array_map('intval', $rows);
    }

    /** @return list<int> */
    private function reminderDays(): array
    {
        $configured = (string) ($_ENV['TENDER_DEADLINE_REMINDER_DAYS'] ?? '7,3,1');
        $days = array_values(array_unique(array_filter(
            array_map('intval', preg_split('/\s*,\s*/', $configured) ?: []),
            static fn (int $day): bool => $day >= 0 && $day <= 365
        )));
        rsort($days, SORT_NUMERIC);
        return $days === [] ? [7, 3, 1] : $days;
    }

    private function deadline(array $tender): ?DateTimeImmutable
    {
        $data = json_decode((string) ($tender['data'] ?? ''), true);
        $value = trim((string) (is_array($data) ? ($data['closing_date'] ?? '') : ''));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) return null;
        $deadline = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $deadline instanceof DateTimeImmutable ? $deadline : null;
    }

    /** @return array{bool,bool} */
    private function expireAndNotifyAdmin(array $tender, string $eventKey, DateTimeImmutable $deadline): array
    {
        $expired = false;
        $notified = $this->dispatchOnce((int) $tender['id'], 'admin', $eventKey, function () use ($tender, $deadline, &$expired): void {
            $update = $this->db->prepare(
                "UPDATE admin_records SET status='expired',updated_at=CURRENT_TIMESTAMP
                 WHERE id=? AND module='tenders' AND status IN ('active','published') AND deleted_at IS NULL"
            );
            $update->execute([(int) $tender['id']]);
            $expired = $update->rowCount() === 1;
            $notification = $this->db->prepare(
                "INSERT INTO admin_notifications (type,title,message,action_url,source_type,source_id)
                 VALUES ('tender_deadline','Tender deadline passed',?,?,'tender',?)"
            );
            $notification->execute([
                $this->expiredMessage($tender, $deadline),
                '/admin/tenders/edit/' . (int) $tender['id'],
                (int) $tender['id'],
            ]);
        });
        return [$expired, $notified];
    }

    private function notifyAdmin(array $tender, string $eventKey, string $title, string $message): bool
    {
        return $this->dispatchOnce((int) $tender['id'], 'admin', $eventKey, function () use ($tender, $title, $message): void {
            $statement = $this->db->prepare(
                "INSERT INTO admin_notifications (type,title,message,action_url,source_type,source_id)
                 VALUES ('tender_deadline',?,?,?,'tender',?)"
            );
            $statement->execute([$title, $message, '/admin/tenders/edit/' . (int) $tender['id'], (int) $tender['id']]);
        });
    }

    private function notifyCustomer(int $customerId, array $tender, string $eventKey, string $title, string $message): bool
    {
        return $this->dispatchOnce((int) $tender['id'], 'customer:' . $customerId, $eventKey, function () use ($customerId, $tender, $title, $message): void {
            $statement = $this->db->prepare(
                "INSERT INTO customer_notifications (customer_id,type,title,message,action_url)
                 VALUES (?,'general',?,?,?)"
            );
            $statement->execute([$customerId, $title, $message, '/customer/tenders/' . (int) $tender['id']]);
        });
    }

    private function dispatchOnce(int $tenderId, string $recipientKey, string $eventKey, callable $notify): bool
    {
        $this->db->beginTransaction();
        try {
            $claim = $this->db->prepare(
                'INSERT IGNORE INTO tender_deadline_events (tender_record_id,recipient_key,event_key) VALUES (?,?,?)'
            );
            $claim->execute([$tenderId, $recipientKey, $eventKey]);
            if ($claim->rowCount() !== 1) {
                $this->db->commit();
                return false;
            }
            $notify();
            $this->db->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }

    private function assertSchemaReady(): void
    {
        $required = ['admin_notifications', 'customer_notifications', 'tender_deadline_events'];
        $placeholders = implode(',', array_fill(0, count($required), '?'));
        $statement = $this->db->prepare(
            "SELECT table_name FROM information_schema.tables
             WHERE table_schema=DATABASE() AND table_name IN ({$placeholders})"
        );
        $statement->execute($required);
        $available = array_map('strtolower', $statement->fetchAll(PDO::FETCH_COLUMN));
        $missing = array_diff($required, $available);
        if ($missing !== []) {
            throw new \RuntimeException(
                'Tender deadline automation is not installed. Apply the database migration first (missing: '
                . implode(', ', $missing) . ').'
            );
        }
    }

    private function reminderMessage(array $tender, DateTimeImmutable $deadline, int $daysRemaining): string
    {
        $when = $daysRemaining === 0 ? 'today' : ($daysRemaining === 1 ? 'tomorrow' : 'in ' . $daysRemaining . ' days');
        return '“' . (string) $tender['title'] . '” closes ' . $when . ' (' . $deadline->format('d M Y') . ').';
    }

    private function expiredMessage(array $tender, DateTimeImmutable $deadline): string
    {
        return '“' . (string) $tender['title'] . '” passed its ' . $deadline->format('d M Y') . ' deadline and was marked expired automatically.';
    }
}
