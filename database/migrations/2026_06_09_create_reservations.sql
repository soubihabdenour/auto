-- =============================================================
-- Migration: create reservations table + extend vehicle status enum
-- Date: 2026-06-09
-- Feature: off-platform reservation/deposit flow (no payment gateway)
-- =============================================================

-- 1. Add the new public-facing status to the vehicle enum.
--    Order matters: 'pending_reservation' must sit between 'available'
--    and 'reserved' so the state transitions read top-to-bottom.
ALTER TABLE vehicles
    MODIFY status ENUM('draft','available','pending_reservation','reserved','sold','archived')
                  NOT NULL DEFAULT 'draft';

-- 2. Reservations table.
CREATE TABLE reservations (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    reference           VARCHAR(20) NOT NULL,
    vehicle_id          BIGINT UNSIGNED NOT NULL,
    lead_id             BIGINT UNSIGNED NULL,

    -- denormalized contact so we don't depend on the lead row
    name                VARCHAR(150) NOT NULL,
    phone               VARCHAR(40)  NOT NULL,
    whatsapp            VARCHAR(40)  NULL,
    email               VARCHAR(190) NULL,
    city                VARCHAR(120) NULL,

    deposit_amount_usd  DECIMAL(10,2) NOT NULL,
    currency            CHAR(3) NOT NULL DEFAULT 'USD',

    status              ENUM('pending_deposit','confirmed','expired','cancelled','converted')
                        NOT NULL DEFAULT 'pending_deposit',
    expires_at          DATETIME NOT NULL,
    confirmed_at        DATETIME NULL,
    confirmed_by        BIGINT UNSIGNED NULL,
    cancelled_at        DATETIME NULL,
    cancelled_by        BIGINT UNSIGNED NULL,
    cancellation_reason VARCHAR(255) NULL,

    locale              CHAR(2) NOT NULL DEFAULT 'ar',
    ip_hash             CHAR(64) NULL,
    user_agent          VARCHAR(255) NULL,
    admin_note          TEXT NULL,

    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uniq_reservations_reference (reference),
    KEY idx_reservations_status_expires (status, expires_at),
    KEY idx_reservations_vehicle (vehicle_id, status),

    CONSTRAINT fk_reservations_vehicle
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_reservations_lead
        FOREIGN KEY (lead_id) REFERENCES leads(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_reservations_confirmed_by
        FOREIGN KEY (confirmed_by) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_reservations_cancelled_by
        FOREIGN KEY (cancelled_by) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Default settings rows (NULL keys are upserted by SettingService).
--    Adjust amounts/instructions in /admin/settings after install.
INSERT INTO settings (`key`, `value`) VALUES
    ('reservation_default_deposit_usd',        '500'),
    ('reservation_expiry_hours',               '48'),
    ('reservation_admin_notification_email',   ''),
    ('reservation_bank_instructions_ar',       ''),
    ('reservation_bank_instructions_fr',       ''),
    ('reservation_bank_instructions_en',       '')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
