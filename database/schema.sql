-- =============================================================
--  Korea Auto Export — MySQL 8.0 Schema (v1)
--  Charset: utf8mb4   Collation: utf8mb4_unicode_ci   Engine: InnoDB
-- =============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- -------------------------------------------------------------
--  USERS  (admins/staff in v1; customer role reserved for v2)
-- -------------------------------------------------------------
CREATE TABLE users (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email           VARCHAR(190) NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    name            VARCHAR(150) NOT NULL,
    phone           VARCHAR(40)  NULL,
    role            ENUM('admin','staff','customer') NOT NULL DEFAULT 'staff',
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at   DATETIME NULL,
    remember_token  VARCHAR(100) NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_users_email (email),
    KEY idx_users_role_active (role, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  BRANDS
-- -------------------------------------------------------------
CREATE TABLE brands (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug        VARCHAR(80) NOT NULL,
    name        VARCHAR(120) NOT NULL,
    country     VARCHAR(60) NULL,
    logo_path   VARCHAR(255) NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_brands_slug (slug),
    KEY idx_brands_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  MODELS
-- -------------------------------------------------------------
CREATE TABLE models (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    brand_id    BIGINT UNSIGNED NOT NULL,
    slug        VARCHAR(120) NOT NULL,
    name        VARCHAR(150) NOT NULL,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    sort_order  INT NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_models_brand_slug (brand_id, slug),
    KEY idx_models_brand (brand_id),
    CONSTRAINT fk_models_brand
        FOREIGN KEY (brand_id) REFERENCES brands(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  BODY TYPES  (sedan, suv, hatchback, pickup, van, coupe...)
-- -------------------------------------------------------------
CREATE TABLE body_types (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key`       VARCHAR(40) NOT NULL,
    name_ar     VARCHAR(120) NOT NULL,
    name_fr     VARCHAR(120) NOT NULL,
    name_en     VARCHAR(120) NOT NULL,
    icon_path   VARCHAR(255) NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_body_types_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  VEHICLES
-- -------------------------------------------------------------
CREATE TABLE vehicles (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug                VARCHAR(190) NOT NULL,
    brand_id            BIGINT UNSIGNED NOT NULL,
    model_id            BIGINT UNSIGNED NOT NULL,
    body_type_id        BIGINT UNSIGNED NULL,
    year                SMALLINT UNSIGNED NOT NULL,
    vin                 VARCHAR(40) NULL,
    mileage_km          INT UNSIGNED NOT NULL DEFAULT 0,
    engine_cc           INT UNSIGNED NULL,
    engine_power_hp     INT UNSIGNED NULL,
    transmission        ENUM('manual','automatic','dct','cvt') NOT NULL DEFAULT 'automatic',
    fuel_type           ENUM('petrol','diesel','hybrid','phev','electric','lpg') NOT NULL,
    drivetrain          ENUM('fwd','rwd','awd','4wd') NOT NULL DEFAULT 'fwd',
    exterior_color      VARCHAR(60) NULL,
    interior_color      VARCHAR(60) NULL,
    doors               TINYINT UNSIGNED NULL,
    seats               TINYINT UNSIGNED NULL,
    origin_country      VARCHAR(60) NOT NULL DEFAULT 'South Korea',
    location            VARCHAR(120) NULL,
    price_usd           DECIMAL(12,2) NOT NULL,
    price_currency      CHAR(3) NOT NULL DEFAULT 'USD',
    listing_type        ENUM('sale','auction') NOT NULL DEFAULT 'sale',
    status              ENUM('draft','available','reserved','sold','archived') NOT NULL DEFAULT 'draft',
    is_featured         TINYINT(1) NOT NULL DEFAULT 0,
    cover_image_id      BIGINT UNSIGNED NULL,
    views_count         INT UNSIGNED NOT NULL DEFAULT 0,
    sold_at             DATETIME NULL,
    published_at        DATETIME NULL,
    created_by          BIGINT UNSIGNED NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_vehicles_slug (slug),
    UNIQUE KEY uniq_vehicles_vin (vin),
    KEY idx_vehicles_status_featured (status, is_featured),
    KEY idx_vehicles_status_published (status, published_at DESC),
    KEY idx_vehicles_brand_model (brand_id, model_id),
    KEY idx_vehicles_year (year),
    KEY idx_vehicles_price (price_usd),
    KEY idx_vehicles_mileage (mileage_km),
    KEY idx_vehicles_fuel (fuel_type),
    KEY idx_vehicles_trans (transmission),
    KEY idx_vehicles_body (body_type_id),
    KEY idx_vehicles_listing_type (listing_type),
    CONSTRAINT fk_vehicles_brand
        FOREIGN KEY (brand_id) REFERENCES brands(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_vehicles_model
        FOREIGN KEY (model_id) REFERENCES models(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_vehicles_body_type
        FOREIGN KEY (body_type_id) REFERENCES body_types(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_vehicles_created_by
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  VEHICLE TRANSLATIONS  (title, description, meta per locale)
-- -------------------------------------------------------------
CREATE TABLE vehicle_translations (
    id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    vehicle_id        BIGINT UNSIGNED NOT NULL,
    locale            CHAR(2) NOT NULL,
    title             VARCHAR(220) NOT NULL,
    description       MEDIUMTEXT NULL,
    meta_title        VARCHAR(220) NULL,
    meta_description  VARCHAR(320) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_vehtrans_vehicle_locale (vehicle_id, locale),
    FULLTEXT KEY ft_vehtrans_title_desc (title, description),
    CONSTRAINT fk_vehtrans_vehicle
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  VEHICLE IMAGES
-- -------------------------------------------------------------
CREATE TABLE vehicle_images (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    vehicle_id      BIGINT UNSIGNED NOT NULL,
    path            VARCHAR(255) NOT NULL,
    alt_ar          VARCHAR(220) NULL,
    alt_fr          VARCHAR(220) NULL,
    alt_en          VARCHAR(220) NULL,
    width           INT UNSIGNED NULL,
    height          INT UNSIGNED NULL,
    size_bytes      INT UNSIGNED NULL,
    is_cover        TINYINT(1) NOT NULL DEFAULT 0,
    sort_order      INT NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_vehimg_vehicle_sort (vehicle_id, sort_order),
    KEY idx_vehimg_cover (vehicle_id, is_cover),
    CONSTRAINT fk_vehimg_vehicle
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE vehicles
    ADD CONSTRAINT fk_vehicles_cover_image
    FOREIGN KEY (cover_image_id) REFERENCES vehicle_images(id)
    ON UPDATE CASCADE ON DELETE SET NULL;

-- -------------------------------------------------------------
--  VEHICLE VIDEOS
-- -------------------------------------------------------------
CREATE TABLE vehicle_videos (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    vehicle_id      BIGINT UNSIGNED NOT NULL,
    provider        ENUM('local','youtube','vimeo') NOT NULL DEFAULT 'local',
    path            VARCHAR(255) NULL,
    external_url    VARCHAR(500) NULL,
    poster_path     VARCHAR(255) NULL,
    duration_sec    INT UNSIGNED NULL,
    sort_order      INT NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_vehvid_vehicle (vehicle_id, sort_order),
    CONSTRAINT fk_vehvid_vehicle
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  INSPECTION REPORTS  (1-to-1 with vehicle)
-- -------------------------------------------------------------
CREATE TABLE inspection_reports (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    vehicle_id          BIGINT UNSIGNED NOT NULL,
    overall_score       TINYINT UNSIGNED NULL,
    engine_score        TINYINT UNSIGNED NULL,
    exterior_score      TINYINT UNSIGNED NULL,
    interior_score      TINYINT UNSIGNED NULL,
    tires_score         TINYINT UNSIGNED NULL,
    brakes_score        TINYINT UNSIGNED NULL,
    electrical_score    TINYINT UNSIGNED NULL,
    accident_history    ENUM('none','minor','major','unknown') NOT NULL DEFAULT 'unknown',
    inspector_name      VARCHAR(150) NULL,
    inspected_at        DATE NULL,
    report_pdf_path     VARCHAR(255) NULL,
    notes_ar            TEXT NULL,
    notes_fr            TEXT NULL,
    notes_en            TEXT NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_inspect_vehicle (vehicle_id),
    CONSTRAINT fk_inspect_vehicle
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  LEADS
-- -------------------------------------------------------------
CREATE TABLE leads (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    vehicle_id      BIGINT UNSIGNED NULL,
    name            VARCHAR(150) NOT NULL,
    phone           VARCHAR(40) NOT NULL,
    whatsapp        VARCHAR(40) NULL,
    email           VARCHAR(190) NULL,
    country         VARCHAR(60) NOT NULL DEFAULT 'Algeria',
    city            VARCHAR(120) NULL,
    message         TEXT NULL,
    lead_type       ENUM('inquiry','quotation','reservation','whatsapp') NOT NULL DEFAULT 'inquiry',
    status          ENUM('new','contacted','qualified','negotiating','won','lost') NOT NULL DEFAULT 'new',
    source          ENUM('vehicle_page','listing','homepage','contact','direct') NOT NULL DEFAULT 'vehicle_page',
    locale          CHAR(2) NOT NULL DEFAULT 'ar',
    assigned_to     BIGINT UNSIGNED NULL,
    ip_hash         CHAR(64) NULL,
    user_agent      VARCHAR(255) NULL,
    referrer        VARCHAR(500) NULL,
    utm_source      VARCHAR(120) NULL,
    utm_medium      VARCHAR(120) NULL,
    utm_campaign    VARCHAR(120) NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_leads_status_created (status, created_at DESC),
    KEY idx_leads_vehicle_created (vehicle_id, created_at DESC),
    KEY idx_leads_type (lead_type),
    KEY idx_leads_assigned (assigned_to),
    CONSTRAINT fk_leads_vehicle
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_leads_assigned
        FOREIGN KEY (assigned_to) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  LEAD NOTES  (internal CRM-style timeline)
-- -------------------------------------------------------------
CREATE TABLE lead_notes (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    lead_id     BIGINT UNSIGNED NOT NULL,
    user_id     BIGINT UNSIGNED NULL,
    body        TEXT NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_leadnotes_lead (lead_id, created_at DESC),
    CONSTRAINT fk_leadnotes_lead
        FOREIGN KEY (lead_id) REFERENCES leads(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_leadnotes_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  TESTIMONIALS
-- -------------------------------------------------------------
CREATE TABLE testimonials (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    customer_name       VARCHAR(150) NOT NULL,
    customer_city       VARCHAR(120) NULL,
    avatar_path         VARCHAR(255) NULL,
    rating              TINYINT UNSIGNED NOT NULL DEFAULT 5,
    vehicle_purchased   VARCHAR(200) NULL,
    is_published        TINYINT(1) NOT NULL DEFAULT 0,
    sort_order          INT NOT NULL DEFAULT 0,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_testimonials_published_sort (is_published, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE testimonial_translations (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    testimonial_id  BIGINT UNSIGNED NOT NULL,
    locale          CHAR(2) NOT NULL,
    body            TEXT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_testtrans_testimonial_locale (testimonial_id, locale),
    CONSTRAINT fk_testtrans_testimonial
        FOREIGN KEY (testimonial_id) REFERENCES testimonials(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  PAGES  (Why Korea, Import Process, About, etc.)
-- -------------------------------------------------------------
CREATE TABLE pages (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key`           VARCHAR(80) NOT NULL,
    template        VARCHAR(80) NOT NULL DEFAULT 'default',
    is_published    TINYINT(1) NOT NULL DEFAULT 1,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_pages_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE page_translations (
    id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    page_id           BIGINT UNSIGNED NOT NULL,
    locale            CHAR(2) NOT NULL,
    title             VARCHAR(220) NOT NULL,
    body              MEDIUMTEXT NULL,
    meta_title        VARCHAR(220) NULL,
    meta_description  VARCHAR(320) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_pagetrans_page_locale (page_id, locale),
    CONSTRAINT fk_pagetrans_page
        FOREIGN KEY (page_id) REFERENCES pages(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  SETTINGS  (key-value, admin-editable)
-- -------------------------------------------------------------
CREATE TABLE settings (
    `key`       VARCHAR(120) NOT NULL,
    `value`     LONGTEXT NULL,
    `type`      ENUM('string','int','float','bool','json') NOT NULL DEFAULT 'string',
    is_public   TINYINT(1) NOT NULL DEFAULT 0,
    description VARCHAR(255) NULL,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  TRANSLATIONS  (admin-editable UI strings; overrides file-based)
-- -------------------------------------------------------------
CREATE TABLE translations (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    namespace   VARCHAR(80) NOT NULL,
    `key`       VARCHAR(190) NOT NULL,
    locale      CHAR(2) NOT NULL,
    `value`     TEXT NOT NULL,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_translations_ns_key_locale (namespace, `key`, locale)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  AUDIT LOGS
-- -------------------------------------------------------------
CREATE TABLE audit_logs (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED NULL,
    action      VARCHAR(80) NOT NULL,
    entity      VARCHAR(80) NULL,
    entity_id   BIGINT UNSIGNED NULL,
    payload     JSON NULL,
    ip          VARCHAR(45) NULL,
    user_agent  VARCHAR(255) NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_user (user_id, created_at DESC),
    KEY idx_audit_entity (entity, entity_id),
    CONSTRAINT fk_audit_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  WHATSAPP CLICK EVENTS  (lightweight analytics)
-- -------------------------------------------------------------
CREATE TABLE whatsapp_click_events (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    vehicle_id      BIGINT UNSIGNED NULL,
    locale          CHAR(2) NULL,
    ip_hash         CHAR(64) NULL,
    user_agent_hash CHAR(64) NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_waclicks_vehicle_created (vehicle_id, created_at DESC),
    CONSTRAINT fk_waclicks_vehicle
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
--  LOGIN THROTTLE
-- -------------------------------------------------------------
CREATE TABLE login_throttle (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key`           CHAR(64) NOT NULL,
    attempts        INT UNSIGNED NOT NULL DEFAULT 0,
    locked_until    DATETIME NULL,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_throttle_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;

