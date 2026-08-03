-- Udyam CMS customer portal and billing extension.
-- Additive only: existing users/admin_records tables are not replaced or altered.

CREATE TABLE IF NOT EXISTS customer_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    customer_record_id BIGINT UNSIGNED NULL,
    company_name VARCHAR(255) NULL,
    mobile VARCHAR(30) NULL,
    gst_number VARCHAR(30) NULL,
    pan_number VARCHAR(20) NULL,
    address_line_1 VARCHAR(255) NULL,
    address_line_2 VARCHAR(255) NULL,
    city VARCHAR(120) NULL,
    state VARCHAR(120) NULL,
    postal_code VARCHAR(20) NULL,
    country VARCHAR(100) NOT NULL DEFAULT 'India',
    logo_path VARCHAR(500) NULL,
    profile_photo_path VARCHAR(500) NULL,
    contact_person VARCHAR(255) NULL,
    business_type VARCHAR(100) NULL,
    social_links JSON NULL,
    preferred_communication ENUM('email','phone','whatsapp','portal') NOT NULL DEFAULT 'email',
    email_verified_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_customer_profiles_user (user_id),
    UNIQUE KEY uq_customer_profiles_record (customer_record_id),
    CONSTRAINT fk_customer_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_customer_profiles_record FOREIGN KEY (customer_record_id) REFERENCES admin_records(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_auth_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    purpose ENUM('verify_email','reset_password') NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_customer_auth_token (token_hash),
    INDEX idx_customer_auth_user_purpose (user_id, purpose, used_at),
    CONSTRAINT fk_customer_auth_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_auth_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identity_hash CHAR(64) NOT NULL,
    action VARCHAR(40) NOT NULL,
    attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer_attempts_window (identity_hash, action, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscription_plans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT NULL,
    billing_cycle ENUM('monthly','quarterly','half_yearly','yearly','one_time') NOT NULL DEFAULT 'yearly',
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    gst_rate DECIMAL(5,2) NOT NULL DEFAULT 18,
    benefits JSON NULL,
    status ENUM('draft','active','archived') NOT NULL DEFAULT 'draft',
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY uq_subscription_plans_slug (slug),
    INDEX idx_subscription_plans_status (status, deleted_at),
    CONSTRAINT fk_subscription_plans_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_subscription_plans_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pending','active','expired','cancelled','suspended') NOT NULL DEFAULT 'pending',
    starts_at DATE NULL,
    expires_at DATE NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    auto_renew TINYINT(1) NOT NULL DEFAULT 0,
    previous_subscription_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer_subscriptions_customer (customer_id, status, expires_at),
    CONSTRAINT fk_customer_subscriptions_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_customer_subscriptions_plan FOREIGN KEY (plan_id) REFERENCES subscription_plans(id),
    CONSTRAINT fk_customer_subscriptions_previous FOREIGN KEY (previous_subscription_id) REFERENCES customer_subscriptions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_service_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    service_record_id BIGINT UNSIGNED NOT NULL,
    application_record_id BIGINT UNSIGNED NULL,
    application_number VARCHAR(40) NOT NULL,
    project_name VARCHAR(255) NULL,
    assigned_user_id BIGINT UNSIGNED NULL,
    status ENUM('submitted','under_review','information_requested','approved','in_progress','completed','rejected','closed') NOT NULL DEFAULT 'submitted',
    customer_remarks TEXT NULL,
    admin_remarks TEXT NULL,
    submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_customer_service_application_number (application_number),
    INDEX idx_customer_service_requests_customer (customer_id, status),
    CONSTRAINT fk_customer_service_requests_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_customer_service_requests_service FOREIGN KEY (service_record_id) REFERENCES admin_records(id),
    CONSTRAINT fk_customer_service_requests_application FOREIGN KEY (application_record_id) REFERENCES admin_records(id) ON DELETE SET NULL,
    CONSTRAINT fk_customer_service_requests_assignee FOREIGN KEY (assigned_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_application_timeline (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_request_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    remarks TEXT NULL,
    visible_to_customer TINYINT(1) NOT NULL DEFAULT 1,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer_timeline_request (service_request_id, created_at),
    CONSTRAINT fk_customer_timeline_request FOREIGN KEY (service_request_id) REFERENCES customer_service_requests(id) ON DELETE CASCADE,
    CONSTRAINT fk_customer_timeline_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    service_request_id BIGINT UNSIGNED NULL,
    document_record_id BIGINT UNSIGNED NULL,
    category ENUM('personal','gst','pan','company','applications','contracts','invoices','others') NOT NULL DEFAULT 'others',
    title VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL,
    source ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    status ENUM('uploaded','requested','verified','rejected','replaced') NOT NULL DEFAULT 'uploaded',
    uploaded_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_customer_documents_customer (customer_id, category, deleted_at),
    CONSTRAINT fk_customer_documents_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_customer_documents_request FOREIGN KEY (service_request_id) REFERENCES customer_service_requests(id) ON DELETE SET NULL,
    CONSTRAINT fk_customer_documents_record FOREIGN KEY (document_record_id) REFERENCES admin_records(id) ON DELETE SET NULL,
    CONSTRAINT fk_customer_documents_uploader FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    notification_record_id BIGINT UNSIGNED NULL,
    type ENUM('subscription','invoice','application','announcement','document_request','general') NOT NULL DEFAULT 'general',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    action_url VARCHAR(500) NULL,
    email_sent_at DATETIME NULL,
    read_at DATETIME NULL,
    archived_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer_notifications_customer (customer_id, read_at, archived_at, created_at),
    CONSTRAINT fk_customer_notifications_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_customer_notifications_record FOREIGN KEY (notification_record_id) REFERENCES admin_records(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    subscription_id BIGINT UNSIGNED NULL,
    service_request_id BIGINT UNSIGNED NULL,
    invoice_number VARCHAR(50) NOT NULL,
    invoice_type ENUM('subscription','service') NOT NULL,
    billing_cycle VARCHAR(50) NULL,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    taxable_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    cgst DECIMAL(12,2) NOT NULL DEFAULT 0,
    sgst DECIMAL(12,2) NOT NULL DEFAULT 0,
    igst DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('draft','issued','partial','paid','overdue','cancelled','refunded') NOT NULL DEFAULT 'draft',
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    paid_at DATETIME NULL,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_customer_invoices_number (invoice_number),
    INDEX idx_customer_invoices_customer (customer_id, status, due_date),
    CONSTRAINT fk_customer_invoices_customer FOREIGN KEY (customer_id) REFERENCES users(id),
    CONSTRAINT fk_customer_invoices_subscription FOREIGN KEY (subscription_id) REFERENCES customer_subscriptions(id) ON DELETE SET NULL,
    CONSTRAINT fk_customer_invoices_request FOREIGN KEY (service_request_id) REFERENCES customer_service_requests(id) ON DELETE SET NULL,
    CONSTRAINT fk_customer_invoices_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_invoice_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT UNSIGNED NOT NULL,
    description VARCHAR(500) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
    unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    gst_rate DECIMAL(5,2) NOT NULL DEFAULT 18,
    line_total DECIMAL(12,2) NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_customer_invoice_items_invoice FOREIGN KEY (invoice_id) REFERENCES customer_invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    invoice_id BIGINT UNSIGNED NOT NULL,
    transaction_id VARCHAR(150) NULL,
    method ENUM('cash','bank_transfer','upi','cheque','card','gateway','adjustment') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    status ENUM('pending','successful','failed','refunded','partially_refunded') NOT NULL DEFAULT 'successful',
    payment_date DATETIME NOT NULL,
    receipt_number VARCHAR(50) NULL,
    notes TEXT NULL,
    recorded_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_customer_payments_transaction (transaction_id),
    INDEX idx_customer_payments_customer (customer_id, payment_date),
    CONSTRAINT fk_customer_payments_customer FOREIGN KEY (customer_id) REFERENCES users(id),
    CONSTRAINT fk_customer_payments_invoice FOREIGN KEY (invoice_id) REFERENCES customer_invoices(id),
    CONSTRAINT fk_customer_payments_recorder FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    actor_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    subject_type VARCHAR(80) NULL,
    subject_id BIGINT UNSIGNED NULL,
    description VARCHAR(500) NULL,
    metadata JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer_activity_customer (customer_id, created_at),
    CONSTRAINT fk_customer_activity_customer FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_customer_activity_actor FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
