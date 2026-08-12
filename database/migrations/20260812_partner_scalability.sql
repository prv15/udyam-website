-- Partner directory filtering and high-concurrency checkout indexes.
ALTER TABLE customer_profiles
    ADD COLUMN IF NOT EXISTS annual_turnover_range VARCHAR(40) NULL AFTER business_type,
    ADD INDEX IF NOT EXISTS idx_customer_profiles_state (state),
    ADD INDEX IF NOT EXISTS idx_customer_profiles_turnover (annual_turnover_range),
    ADD INDEX IF NOT EXISTS idx_customer_profiles_company (company_name);

ALTER TABLE customer_subscriptions
    ADD INDEX IF NOT EXISTS idx_customer_subscriptions_checkout (customer_id, plan_id, status, updated_at);

ALTER TABLE customer_payment_orders
    ADD INDEX IF NOT EXISTS idx_payment_orders_subscription_status (subscription_id, status, expires_at);
