-- Project Funding subscription catalogue and payment checkout extension.
-- Additive and backward compatible with the existing customer billing tables.

ALTER TABLE subscription_plans
    ADD COLUMN IF NOT EXISTS category VARCHAR(120) NULL AFTER slug,
    ADD COLUMN IF NOT EXISTS subtitle VARCHAR(255) NULL AFTER category,
    ADD COLUMN IF NOT EXISTS badge VARCHAR(80) NULL AFTER subtitle,
    ADD COLUMN IF NOT EXISTS ideal_for VARCHAR(500) NULL AFTER description,
    ADD COLUMN IF NOT EXISTS highlight_text VARCHAR(255) NULL AFTER ideal_for,
    ADD COLUMN IF NOT EXISTS gst_inclusive TINYINT(1) NOT NULL DEFAULT 0 AFTER gst_rate,
    ADD COLUMN IF NOT EXISTS initial_payment DECIMAL(12,2) NULL AFTER gst_inclusive,
    ADD COLUMN IF NOT EXISTS followup_payment DECIMAL(12,2) NULL AFTER initial_payment,
    ADD COLUMN IF NOT EXISTS followup_due_days INT UNSIGNED NULL AFTER followup_payment,
    ADD COLUMN IF NOT EXISTS featured TINYINT(1) NOT NULL DEFAULT 0 AFTER followup_due_days,
    ADD COLUMN IF NOT EXISTS disclaimer VARCHAR(500) NULL AFTER featured;

ALTER TABLE customer_invoices
    ADD COLUMN IF NOT EXISTS public_token CHAR(64) NULL AFTER invoice_number,
    ADD COLUMN IF NOT EXISTS installment_number INT UNSIGNED NULL AFTER billing_cycle,
    ADD UNIQUE KEY IF NOT EXISTS uq_customer_invoices_public_token (public_token);

CREATE TABLE IF NOT EXISTS customer_payment_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    invoice_id BIGINT UNSIGNED NOT NULL,
    subscription_id BIGINT UNSIGNED NULL,
    public_token CHAR(64) NOT NULL,
    provider VARCHAR(40) NOT NULL DEFAULT 'payyantra',
    provider_order_id VARCHAR(150) NULL,
    provider_session_id VARCHAR(255) NULL,
    amount DECIMAL(12,2) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'INR',
    status ENUM('created','pending','paid','failed','cancelled','expired') NOT NULL DEFAULT 'created',
    checkout_url VARCHAR(1000) NULL,
    request_payload JSON NULL,
    response_payload JSON NULL,
    callback_payload JSON NULL,
    failure_reason VARCHAR(500) NULL,
    expires_at DATETIME NULL,
    paid_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_customer_payment_orders_token (public_token),
    UNIQUE KEY uq_customer_payment_orders_provider_order (provider, provider_order_id),
    INDEX idx_customer_payment_orders_customer (customer_id, status, created_at),
    CONSTRAINT fk_customer_payment_orders_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_customer_payment_orders_invoice FOREIGN KEY (invoice_id) REFERENCES customer_invoices(id) ON DELETE CASCADE,
    CONSTRAINT fk_customer_payment_orders_subscription FOREIGN KEY (subscription_id) REFERENCES customer_subscriptions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
