-- Idempotency ledger for recurring tender deadline alerts.

CREATE TABLE IF NOT EXISTS tender_deadline_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tender_record_id BIGINT UNSIGNED NOT NULL,
    recipient_key VARCHAR(100) NOT NULL,
    event_key VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_tender_deadline_dispatch (tender_record_id, recipient_key, event_key),
    INDEX idx_tender_deadline_events_created (created_at),
    CONSTRAINT fk_tender_deadline_events_tender FOREIGN KEY (tender_record_id) REFERENCES admin_records(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
