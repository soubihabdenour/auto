-- =============================================================
--  Korea Auto Export — Reference seed data (idempotent-friendly)
--  Run AFTER database/schema.sql
--  Does NOT create admin user — that is handled by bin/install.php
-- =============================================================

SET NAMES utf8mb4;

-- ---- Korean brands ----------------------------------------
INSERT INTO brands (slug, name, country, sort_order) VALUES
('hyundai',          'Hyundai',          'South Korea', 1),
('kia',              'Kia',              'South Korea', 2),
('genesis',          'Genesis',          'South Korea', 3),
('ssangyong',        'SsangYong',        'South Korea', 4),
('renault-samsung',  'Renault Samsung',  'South Korea', 5);

-- ---- Selected models --------------------------------------
INSERT INTO models (brand_id, slug, name) VALUES
(1, 'tucson',   'Tucson'),
(1, 'santa-fe', 'Santa Fe'),
(1, 'elantra',  'Elantra'),
(1, 'sonata',   'Sonata'),
(1, 'palisade', 'Palisade'),
(1, 'kona',     'Kona'),
(1, 'ioniq-5',  'Ioniq 5'),
(2, 'sportage', 'Sportage'),
(2, 'sorento',  'Sorento'),
(2, 'cerato',   'Cerato'),
(2, 'k5',       'K5'),
(2, 'carnival', 'Carnival'),
(2, 'ev6',      'EV6'),
(3, 'gv70',     'GV70'),
(3, 'gv80',     'GV80'),
(3, 'g80',      'G80');

-- ---- Body types -------------------------------------------
INSERT INTO body_types (`key`, name_ar, name_fr, name_en, sort_order) VALUES
('sedan',     'سيدان',     'Berline',  'Sedan',     1),
('suv',       'دفع رباعي', 'SUV',      'SUV',       2),
('hatchback', 'هاتشباك',  'Compacte', 'Hatchback', 3),
('coupe',     'كوبيه',     'Coupé',    'Coupe',     4),
('pickup',    'بيك أب',    'Pickup',   'Pickup',    5),
('van',       'فان',       'Van',      'Van',       6),
('wagon',     'ستيشن',     'Break',    'Wagon',     7);

-- ---- Pages ------------------------------------------------
INSERT INTO pages (`key`, template) VALUES
('why-korea',      'why-korea'),
('import-process', 'import-process'),
('testimonials',   'testimonials'),
('about',          'default'),
('contact',        'contact');

-- ---- Core settings ----------------------------------------
INSERT INTO settings (`key`, `value`, `type`, is_public, description) VALUES
('site_name',                       'Korea Auto Export',                                       'string', 1, 'Brand name'),
('site_tagline_ar',                 'استورد سيارتك الكورية مباشرة إلى الجزائر',                'string', 1, NULL),
('site_tagline_fr',                 'Importez votre voiture coréenne directement en Algérie',  'string', 1, NULL),
('site_tagline_en',                 'Import your Korean car directly to Algeria',              'string', 1, NULL),
('contact_email',                   'contact@koreaautoexport.dz',                              'string', 1, NULL),
('contact_phone',                   '+213 000 000 000',                                        'string', 1, NULL),
('whatsapp_number',                 '+213000000000',                                           'string', 1, 'No spaces, no dashes'),
('whatsapp_default_message_ar',     'مرحبا، أنا مهتم بالسيارة:',                               'string', 1, NULL),
('whatsapp_default_message_fr',     'Bonjour, je suis intéressé par le véhicule:',             'string', 1, NULL),
('whatsapp_default_message_en',     'Hello, I am interested in the vehicle:',                  'string', 1, NULL),
('lead_notification_email',         'leads@koreaautoexport.dz',                                'string', 0, 'Where new leads are mailed'),
('default_locale',                  'ar',                                                       'string', 1, NULL),
('available_locales',               '["ar","fr","en"]',                                         'json',   1, NULL),
('estimator_shipping_base_usd',     '1500',                                                     'float',  0, NULL),
('estimator_customs_rate',          '0.30',                                                     'float',  0, NULL),
('estimator_tva_rate',              '0.19',                                                     'float',  0, NULL),
('estimator_service_fee_flat_usd',  '500',                                                      'float',  0, NULL),
('estimator_service_fee_percent',   '0.02',                                                     'float',  0, NULL),
('fx_usd_to_dzd',                   '135.00',                                                   'float',  1, 'Updated weekly'),
('fx_usd_to_krw',                   '1380.00',                                                  'float',  1, 'Updated weekly — 1 USD = X KRW'),
('social_facebook',                 '',                                                         'string', 1, NULL),
('social_instagram',                '',                                                         'string', 1, NULL),
('social_tiktok',                   '',                                                         'string', 1, NULL),
('analytics_plausible_domain',      '',                                                         'string', 0, 'e.g. koreaautoexport.dz'),
('analytics_ga4_id',                '',                                                         'string', 0, 'e.g. G-XXXXXXXXXX'),
('search_console_verification',     '',                                                         'string', 0, 'Google Search Console meta verification token');
