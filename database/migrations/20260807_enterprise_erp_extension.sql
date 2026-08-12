-- Enterprise ERP extension for the existing Udyam CMS.
-- Additive only: customer, subscription, invoice, payment, document and activity tables remain unchanged.

CREATE TABLE IF NOT EXISTS admin_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(80) NOT NULL DEFAULT 'general',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    action_url VARCHAR(500) NULL,
    customer_id BIGINT UNSIGNED NULL,
    source_type VARCHAR(80) NULL,
    source_id BIGINT UNSIGNED NULL,
    read_at DATETIME NULL,
    deleted_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin_notifications_inbox (deleted_at, read_at, created_at),
    INDEX idx_admin_notifications_customer (customer_id, created_at),
    CONSTRAINT fk_admin_notifications_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS subscription_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    subscription_id BIGINT UNSIGNED NOT NULL,
    request_type ENUM('pause','resume','cancellation') NOT NULL,
    reason TEXT NULL,
    status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
    admin_note TEXT NULL,
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_subscription_requests_admin (status, created_at),
    INDEX idx_subscription_requests_customer (customer_id, status, created_at),
    CONSTRAINT fk_subscription_requests_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_subscription_requests_subscription FOREIGN KEY (subscription_id) REFERENCES customer_subscriptions(id) ON DELETE CASCADE,
    CONSTRAINT fk_subscription_requests_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_support_tickets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    topic ENUM('subscription','billing','application','document','technical','general') NOT NULL DEFAULT 'general',
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
    admin_reply TEXT NULL,
    replied_by BIGINT UNSIGNED NULL,
    replied_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer_support_customer (customer_id, status, created_at),
    INDEX idx_customer_support_status (status, created_at),
    CONSTRAINT fk_customer_support_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_customer_support_replied_by FOREIGN KEY (replied_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
