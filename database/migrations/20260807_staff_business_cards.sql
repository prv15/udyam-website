-- Users-backed staff identities, role-based admin access and digital cards.
-- Staff are users with user_type='staff'; there is no separate staff identity table.

ALTER TABLE users MODIFY COLUMN user_type ENUM('admin','staff','customer') NOT NULL DEFAULT 'customer';
ALTER TABLE users ADD COLUMN IF NOT EXISTS employee_code VARCHAR(60) NULL UNIQUE AFTER user_type;
ALTER TABLE users ADD COLUMN IF NOT EXISTS display_name VARCHAR(255) NULL AFTER last_name;
ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_photo_media_id BIGINT UNSIGNED NULL AFTER employee_code;
ALTER TABLE users ADD COLUMN IF NOT EXISTS designation VARCHAR(180) NULL AFTER profile_photo_media_id;
ALTER TABLE users ADD COLUMN IF NOT EXISTS department VARCHAR(180) NULL AFTER designation;
ALTER TABLE users ADD COLUMN IF NOT EXISTS mobile VARCHAR(40) NULL AFTER department;
ALTER TABLE users ADD COLUMN IF NOT EXISTS alternate_mobile VARCHAR(40) NULL AFTER mobile;
ALTER TABLE users ADD COLUMN IF NOT EXISTS office_extension VARCHAR(30) NULL AFTER alternate_mobile;
ALTER TABLE users ADD COLUMN IF NOT EXISTS date_of_joining DATE NULL AFTER office_extension;
ALTER TABLE users ADD COLUMN IF NOT EXISTS employment_type ENUM('full_time','part_time','contract','intern','consultant') NULL AFTER date_of_joining;
ALTER TABLE users ADD COLUMN IF NOT EXISTS reporting_manager_id BIGINT UNSIGNED NULL AFTER employment_type;
ALTER TABLE users ADD COLUMN IF NOT EXISTS office_location VARCHAR(255) NULL AFTER reporting_manager_id;
ALTER TABLE users ADD COLUMN IF NOT EXISTS address TEXT NULL AFTER office_location;
ALTER TABLE users ADD COLUMN IF NOT EXISTS employment_status ENUM('active','inactive','on_leave','resigned','suspended') NULL AFTER address;
ALTER TABLE users ADD COLUMN IF NOT EXISTS bio TEXT NULL AFTER employment_status;
ALTER TABLE users ADD COLUMN IF NOT EXISTS skills TEXT NULL AFTER bio;
ALTER TABLE users ADD COLUMN IF NOT EXISTS linkedin_url VARCHAR(500) NULL AFTER skills;
ALTER TABLE users ADD COLUMN IF NOT EXISTS website_url VARCHAR(500) NULL AFTER linkedin_url;
ALTER TABLE users ADD COLUMN IF NOT EXISTS whatsapp_number VARCHAR(40) NULL AFTER website_url;
ALTER TABLE users ADD COLUMN IF NOT EXISTS emergency_contact TEXT NULL AFTER whatsapp_number;
ALTER TABLE users ADD COLUMN IF NOT EXISTS internal_notes TEXT NULL AFTER emergency_contact;

-- Superseded first-draft identity tables. They are empty in the current installation.
DROP TABLE IF EXISTS staff_card_events;
DROP TABLE IF EXISTS staff_digital_cards;
DROP TABLE IF EXISTS staff_activity_logs;
DROP TABLE IF EXISTS staff;

CREATE TABLE IF NOT EXISTS roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    description VARCHAR(500) NULL,
    permissions JSON NOT NULL,
    is_system TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Upgrade the existing lightweight roles table when already present.
ALTER TABLE roles ADD COLUMN IF NOT EXISTS permissions JSON NULL AFTER description;
ALTER TABLE roles ADD COLUMN IF NOT EXISTS is_system TINYINT(1) NOT NULL DEFAULT 0 AFTER permissions;
ALTER TABLE roles ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;
UPDATE roles SET permissions=JSON_OBJECT() WHERE permissions IS NULL;
ALTER TABLE roles MODIFY COLUMN permissions JSON NOT NULL;
UPDATE roles SET is_system=1 WHERE slug IN ('super-admin','administrator');

CREATE TABLE IF NOT EXISTS user_roles (
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, role_id),
    CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO roles (name, slug, description, permissions, is_system) VALUES
('Website Editor', 'website-editor', 'Can maintain website content and media.', JSON_OBJECT('dashboard', JSON_ARRAY('view'), 'website', JSON_ARRAY('view','manage'), 'media', JSON_ARRAY('view','manage')), 1),
('Project Manager', 'project-manager', 'Can manage projects, tasks and view the staff directory.', JSON_OBJECT('dashboard', JSON_ARRAY('view'), 'staff', JSON_ARRAY('view'), 'projects', JSON_ARRAY('view','manage'), 'tasks', JSON_ARRAY('view','manage')), 1);

CREATE TABLE IF NOT EXISTS digital_business_cards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    public_slug VARCHAR(190) NOT NULL,
    theme VARCHAR(50) NOT NULL DEFAULT 'udyam-premium',
    status ENUM('enabled','disabled') NOT NULL DEFAULT 'enabled',
    show_mobile TINYINT(1) NOT NULL DEFAULT 1,
    show_whatsapp TINYINT(1) NOT NULL DEFAULT 1,
    show_email TINYINT(1) NOT NULL DEFAULT 1,
    show_address TINYINT(1) NOT NULL DEFAULT 1,
    show_linkedin TINYINT(1) NOT NULL DEFAULT 1,
    show_bio TINYINT(1) NOT NULL DEFAULT 1,
    qr_version INT UNSIGNED NOT NULL DEFAULT 1,
    qr_generated_at DATETIME NULL,
    total_views BIGINT UNSIGNED NOT NULL DEFAULT 0,
    unique_views BIGINT UNSIGNED NOT NULL DEFAULT 0,
    qr_scans BIGINT UNSIGNED NOT NULL DEFAULT 0,
    last_viewed_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_digital_card_user (user_id),
    UNIQUE KEY uq_digital_card_slug (public_slug),
    INDEX idx_digital_cards_status_created (status, created_at),
    CONSTRAINT fk_digital_card_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS digital_card_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    card_id BIGINT UNSIGNED NOT NULL,
    event_type ENUM('view','qr_scan','save_contact','whatsapp','call','email','website','linkedin','share','copy_link','wallet') NOT NULL,
    visitor_hash CHAR(64) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_card_events_card_type_created (card_id, event_type, created_at),
    INDEX idx_card_events_unique_view (card_id, visitor_hash, event_type),
    CONSTRAINT fk_digital_card_event_card FOREIGN KEY (card_id) REFERENCES digital_business_cards(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    staff_user_id BIGINT UNSIGNED NOT NULL,
    actor_user_id BIGINT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    description VARCHAR(500) NOT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_activity_staff_created (staff_user_id, created_at),
    CONSTRAINT fk_staff_activity_user FOREIGN KEY (staff_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_staff_activity_actor FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
