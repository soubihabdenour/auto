-- =============================================================
--  ADY Motors — Demo seed (5 vehicles + testimonials + pages)
--  Run AFTER 00_reference.sql. Idempotency: deletes prior demo rows
--  by VIN prefix "KAE-DEMO-" before re-inserting.
-- =============================================================

SET NAMES utf8mb4;

-- Clean prior demo data (safe if first run)
DELETE FROM vehicles WHERE vin LIKE 'KAE-DEMO-%';

-- =============================================================
--  Vehicle 1 — Hyundai Tucson 2022 (Diesel, AWD, featured)
-- =============================================================
INSERT INTO vehicles
    (slug, brand_id, model_id, body_type_id, year, vin, mileage_km,
     engine_cc, engine_power_hp, transmission, fuel_type, drivetrain,
     exterior_color, interior_color, doors, seats, location,
     price_usd, listing_type, status, is_featured, published_at, created_at)
VALUES
    ('2022-hyundai-tucson-diesel-7a3f',
     (SELECT id FROM brands WHERE slug='hyundai'),
     (SELECT id FROM models WHERE slug='tucson'),
     (SELECT id FROM body_types WHERE `key`='suv'),
     2022, 'KAE-DEMO-TUC22-7A3F', 35420,
     1995, 185, 'automatic', 'diesel', 'awd',
     'Phantom Black', 'Black leather', 5, 5, 'Busan, Korea',
     19500.00, 'sale', 'available', 1, NOW(), NOW());

SET @v1 = LAST_INSERT_ID();

INSERT INTO vehicle_translations (vehicle_id, locale, title, description, meta_title, meta_description) VALUES
(@v1, 'ar',
 'هيونداي توسان 2022 ديزل دفع رباعي',
 'سيارة دفع رباعي مدمجة فاخرة من فئة Premium. محرك ديزل 2.0 لتر بقوة 185 حصاناً، ناقل حركة أوتوماتيكي 8 سرعات، نظام دفع رباعي ذكي. تجهيزات كاملة: مقاعد جلدية مدفأة، شاشة لمس 10.25 بوصة، نظام أمان ADAS، فتحة سقف بانورامية. حالة ممتازة، مفحوصة من قبل فريقنا في بوسان.',
 'هيونداي توسان 2022 - استيراد من كوريا',
 'استورد هيونداي توسان 2022 ديزل من كوريا إلى الجزائر. حالة ممتازة، مفحوصة، تقرير شفاف، سعر تنافسي.'),
(@v1, 'fr',
 'Hyundai Tucson 2022 Diesel AWD',
 'SUV compact premium. Moteur diesel 2.0L de 185 ch, boîte automatique 8 rapports, transmission AWD intelligente. Pack complet : sièges cuir chauffants, écran tactile 10,25", ADAS, toit panoramique. État excellent, inspection complète par notre équipe à Busan.',
 'Hyundai Tucson 2022 — Importation depuis la Corée',
 'Importez ce Hyundai Tucson 2022 diesel depuis la Corée vers l\'Algérie. État excellent, inspection détaillée, prix transparent.'),
(@v1, 'en',
 'Hyundai Tucson 2022 Diesel AWD',
 'Premium compact SUV. 2.0L diesel engine, 185 hp, 8-speed automatic, intelligent AWD. Full package: heated leather seats, 10.25" touchscreen, ADAS, panoramic roof. Excellent condition, fully inspected by our Busan team.',
 'Hyundai Tucson 2022 — Imported from Korea',
 'Import this Hyundai Tucson 2022 diesel from Korea to Algeria. Excellent condition, full inspection, transparent pricing.');

INSERT INTO inspection_reports
    (vehicle_id, overall_score, engine_score, exterior_score, interior_score,
     tires_score, brakes_score, electrical_score, accident_history,
     inspector_name, inspected_at, notes_en)
VALUES
    (@v1, 92, 98, 90, 85, 92, 95, 88, 'none',
     'KAE Busan inspection team', '2025-11-12',
     'No mechanical or structural issues found. Minor stone chip on bumper (cosmetic only). Service records present.');

-- =============================================================
--  Vehicle 2 — Kia K5 GT-Line 2021 (Petrol, FWD, featured)
-- =============================================================
INSERT INTO vehicles
    (slug, brand_id, model_id, body_type_id, year, vin, mileage_km,
     engine_cc, engine_power_hp, transmission, fuel_type, drivetrain,
     exterior_color, interior_color, doors, seats, location,
     price_usd, listing_type, status, is_featured, published_at, created_at)
VALUES
    ('2021-kia-k5-gt-line-petrol-9b2c',
     (SELECT id FROM brands WHERE slug='kia'),
     (SELECT id FROM models WHERE slug='k5'),
     (SELECT id FROM body_types WHERE `key`='sedan'),
     2021, 'KAE-DEMO-K521-9B2C', 47800,
     1598, 180, 'automatic', 'petrol', 'fwd',
     'Snow White Pearl', 'Black sport leather', 4, 5, 'Incheon, Korea',
     17200.00, 'sale', 'available', 1, NOW(), NOW());

SET @v2 = LAST_INSERT_ID();

INSERT INTO vehicle_translations (vehicle_id, locale, title, description, meta_title, meta_description) VALUES
(@v2, 'ar',
 'كيا K5 GT-Line 2021 بنزين',
 'سيدان رياضية أنيقة بمحرك تيربو 1.6 لتر بقوة 180 حصاناً، ناقل حركة أوتوماتيكي 8 سرعات. تصميم خارجي رياضي مع جنوط 19 بوصة، مقاعد رياضية، شاشة 12.3 بوصة، نظام صوت Bose. حالة ممتازة، تاريخ صيانة موثق.',
 'كيا K5 GT-Line 2021 - استيراد من كوريا',
 'كيا K5 GT-Line 2021 بنزين تيربو، استيراد مباشر من كوريا إلى الجزائر.'),
(@v2, 'fr',
 'Kia K5 GT-Line 2021 Essence',
 'Berline sportive élégante avec turbo 1,6L de 180 ch et boîte auto 8 rapports. Design extérieur sportif, jantes 19", sièges sport, écran 12,3", système audio Bose. État excellent, historique d\'entretien documenté.',
 'Kia K5 GT-Line 2021 — Importation depuis la Corée',
 'Kia K5 GT-Line 2021 turbo essence, importation directe depuis la Corée vers l\'Algérie.'),
(@v2, 'en',
 'Kia K5 GT-Line 2021 Petrol',
 'Elegant sport sedan with 1.6L turbo (180 hp) and 8-speed automatic. Sporty exterior, 19" alloys, sport seats, 12.3" screen, Bose audio. Excellent condition, full maintenance history.',
 'Kia K5 GT-Line 2021 — Imported from Korea',
 'Kia K5 GT-Line 2021 turbo petrol, direct import from Korea to Algeria.');

INSERT INTO inspection_reports
    (vehicle_id, overall_score, engine_score, exterior_score, interior_score,
     tires_score, brakes_score, electrical_score, accident_history,
     inspector_name, inspected_at, notes_en)
VALUES
    (@v2, 88, 92, 86, 88, 80, 90, 90, 'none',
     'KAE Busan inspection team', '2025-10-28',
     'Tires at 60% remaining tread — recommend rotation after import. All else excellent.');

-- =============================================================
--  Vehicle 3 — Genesis GV70 2023 (Petrol, AWD, featured, premium)
-- =============================================================
INSERT INTO vehicles
    (slug, brand_id, model_id, body_type_id, year, vin, mileage_km,
     engine_cc, engine_power_hp, transmission, fuel_type, drivetrain,
     exterior_color, interior_color, doors, seats, location,
     price_usd, listing_type, status, is_featured, published_at, created_at)
VALUES
    ('2023-genesis-gv70-3-5t-awd-prestige',
     (SELECT id FROM brands WHERE slug='genesis'),
     (SELECT id FROM models WHERE slug='gv70'),
     (SELECT id FROM body_types WHERE `key`='suv'),
     2023, 'KAE-DEMO-GV723-PRES', 18200,
     3470, 380, 'automatic', 'petrol', 'awd',
     'Vik Black', 'Obsidian Black Nappa', 5, 5, 'Seoul, Korea',
     42900.00, 'sale', 'available', 1, NOW(), NOW());

SET @v3 = LAST_INSERT_ID();

INSERT INTO vehicle_translations (vehicle_id, locale, title, description, meta_title, meta_description) VALUES
(@v3, 'ar',
 'جينيسيس GV70 3.5T AWD 2023 Prestige',
 'SUV فاخر من جينيسيس بمحرك V6 توين تيربو 3.5 لتر بقوة 380 حصاناً. تجهيزات كاملة من فئة Prestige: مقاعد Nappa جلدية، شاشة منحنية 14.5 بوصة، نظام أمان شامل، نظام صوت Lexicon. سيارة قليلة الاستخدام بحالة الوكالة.',
 'جينيسيس GV70 2023 - فاخرة من كوريا',
 'جينيسيس GV70 3.5T AWD 2023 فئة Prestige، استيراد مباشر من كوريا إلى الجزائر.'),
(@v3, 'fr',
 'Genesis GV70 3.5T AWD 2023 Prestige',
 'SUV de luxe Genesis avec V6 bi-turbo 3,5L de 380 ch. Pack Prestige complet : sièges Nappa, écran incurvé 14,5", suite de sécurité complète, audio Lexicon. Véhicule quasi-neuf, état concession.',
 'Genesis GV70 2023 — Luxe coréen importé',
 'Genesis GV70 3.5T AWD 2023 Prestige, importation directe depuis la Corée vers l\'Algérie.'),
(@v3, 'en',
 'Genesis GV70 3.5T AWD 2023 Prestige',
 'Genesis luxury SUV with 3.5L twin-turbo V6 producing 380 hp. Full Prestige spec: Nappa leather, 14.5" curved display, full safety suite, Lexicon audio. Nearly new, dealer-condition.',
 'Genesis GV70 2023 — Korean luxury imported',
 'Genesis GV70 3.5T AWD 2023 Prestige, direct import from Korea to Algeria.');

INSERT INTO inspection_reports
    (vehicle_id, overall_score, engine_score, exterior_score, interior_score,
     tires_score, brakes_score, electrical_score, accident_history,
     inspector_name, inspected_at, notes_en)
VALUES
    (@v3, 97, 100, 96, 96, 98, 97, 98, 'none',
     'KAE Seoul inspection team', '2025-11-05',
     'Demonstrator vehicle, dealer-maintained. All electronics functional. Warranty paperwork available.');

-- =============================================================
--  Vehicle 4 — Hyundai Palisade 2020 (Family, 7-seat)
-- =============================================================
INSERT INTO vehicles
    (slug, brand_id, model_id, body_type_id, year, vin, mileage_km,
     engine_cc, engine_power_hp, transmission, fuel_type, drivetrain,
     exterior_color, interior_color, doors, seats, location,
     price_usd, listing_type, status, is_featured, published_at, created_at)
VALUES
    ('2020-hyundai-palisade-family-8-seat',
     (SELECT id FROM brands WHERE slug='hyundai'),
     (SELECT id FROM models WHERE slug='palisade'),
     (SELECT id FROM body_types WHERE `key`='suv'),
     2020, 'KAE-DEMO-PAL20-FAM8', 61500,
     3778, 295, 'automatic', 'petrol', 'awd',
     'Lagoon Silver', 'Beige leather', 5, 8, 'Daegu, Korea',
     24600.00, 'sale', 'available', 0, NOW(), NOW());

SET @v4 = LAST_INSERT_ID();

INSERT INTO vehicle_translations (vehicle_id, locale, title, description, meta_title, meta_description) VALUES
(@v4, 'ar',
 'هيونداي باليساد 2020 - 8 مقاعد للعائلة',
 'سيارة عائلية فاخرة بـ 8 مقاعد، محرك V6 3.8 لتر بقوة 295 حصاناً، دفع رباعي، فتحة سقف، تكييف ثلاث مناطق، شاشة 10.25 بوصة. مثالية للعائلات الكبيرة. صيانة منتظمة موثقة.',
 'هيونداي باليساد 2020 - SUV عائلية',
 'هيونداي باليساد 2020 8 مقاعد، استيراد من كوريا إلى الجزائر.'),
(@v4, 'fr',
 'Hyundai Palisade 2020 — 8 places familial',
 'Grand SUV familial 8 places avec V6 3,8L de 295 ch, AWD, toit ouvrant, climatisation tri-zone, écran 10,25". Idéal pour les grandes familles. Entretien régulier documenté.',
 'Hyundai Palisade 2020 — SUV familial',
 'Hyundai Palisade 2020 8 places, importation depuis la Corée vers l\'Algérie.'),
(@v4, 'en',
 'Hyundai Palisade 2020 — 8-seat family SUV',
 'Large 8-seat family SUV with 3.8L V6 (295 hp), AWD, sunroof, tri-zone climate, 10.25" display. Ideal for large families. Regular documented maintenance.',
 'Hyundai Palisade 2020 — Family SUV',
 'Hyundai Palisade 2020 8-seat, imported from Korea to Algeria.');

INSERT INTO inspection_reports
    (vehicle_id, overall_score, engine_score, exterior_score, interior_score,
     tires_score, brakes_score, electrical_score, accident_history,
     inspector_name, inspected_at, notes_en)
VALUES
    (@v4, 84, 90, 80, 85, 78, 85, 88, 'minor',
     'KAE Daegu inspection team', '2025-09-15',
     'Minor rear bumper repair (cosmetic, well-executed). Tires due for replacement within 10,000 km.');

-- =============================================================
--  Vehicle 5 — Kia EV6 2022 (Electric, RWD)
-- =============================================================
INSERT INTO vehicles
    (slug, brand_id, model_id, body_type_id, year, vin, mileage_km,
     engine_cc, engine_power_hp, transmission, fuel_type, drivetrain,
     exterior_color, interior_color, doors, seats, location,
     price_usd, listing_type, status, is_featured, published_at, created_at)
VALUES
    ('2022-kia-ev6-long-range-rwd',
     (SELECT id FROM brands WHERE slug='kia'),
     (SELECT id FROM models WHERE slug='ev6'),
     (SELECT id FROM body_types WHERE `key`='suv'),
     2022, 'KAE-DEMO-EV622-LR', 28900,
     NULL, 229, 'automatic', 'electric', 'rwd',
     'Aurora Black Pearl', 'Mid-grey', 5, 5, 'Busan, Korea',
     29800.00, 'sale', 'available', 1, NOW(), NOW());

SET @v5 = LAST_INSERT_ID();

INSERT INTO vehicle_translations (vehicle_id, locale, title, description, meta_title, meta_description) VALUES
(@v5, 'ar',
 'كيا EV6 Long Range 2022 كهربائية',
 'سيارة كهربائية بالكامل من كيا، مدى 528 كم WLTP، شحن سريع 800 فولت من 10% إلى 80% في 18 دقيقة. تصميم مستقبلي، شاشة منحنية مزدوجة 12.3 بوصة، نظام Highway Drive Assist. خيار ممتاز للمستقبل.',
 'كيا EV6 2022 - كهربائية بالكامل',
 'كيا EV6 Long Range 2022 كهربائية، استيراد من كوريا إلى الجزائر.'),
(@v5, 'fr',
 'Kia EV6 Long Range 2022 Électrique',
 'Véhicule 100% électrique Kia, autonomie WLTP 528 km, charge rapide 800V de 10% à 80% en 18 min. Design futuriste, double écran incurvé 12,3", Highway Drive Assist. Choix d\'avenir.',
 'Kia EV6 2022 — 100% électrique',
 'Kia EV6 Long Range 2022 électrique, importation depuis la Corée vers l\'Algérie.'),
(@v5, 'en',
 'Kia EV6 Long Range 2022 Electric',
 'Fully electric Kia with 528 km WLTP range, 800V fast charging 10–80% in 18 min. Futuristic design, dual 12.3" curved displays, Highway Drive Assist. Future-proof choice.',
 'Kia EV6 2022 — Fully electric',
 'Kia EV6 Long Range 2022 electric, imported from Korea to Algeria.');

INSERT INTO inspection_reports
    (vehicle_id, overall_score, engine_score, exterior_score, interior_score,
     tires_score, brakes_score, electrical_score, accident_history,
     inspector_name, inspected_at, notes_en)
VALUES
    (@v5, 95, 100, 94, 95, 92, 96, 99, 'none',
     'KAE Busan EV team', '2025-11-18',
     'Battery health 97% (verified via official diagnostic). All charging modes tested. Tires 85% remaining.');

-- =============================================================
--  Testimonials
-- =============================================================
DELETE FROM testimonials WHERE customer_name IN ('Karim B.', 'Sara N.', 'Mehdi L.');

INSERT INTO testimonials (customer_name, customer_city, rating, vehicle_purchased, is_published, sort_order)
VALUES
    ('Karim B.', 'Oran', 5, 'Hyundai Tucson 2022',           1, 1),
    ('Sara N.',  'Algiers', 5, 'Kia Sorento 2021',           1, 2),
    ('Mehdi L.', 'Constantine', 5, 'Genesis G80 2023',       1, 3);

SET @t1 = (SELECT id FROM testimonials WHERE customer_name='Karim B.' AND customer_city='Oran' ORDER BY id DESC LIMIT 1);
SET @t2 = (SELECT id FROM testimonials WHERE customer_name='Sara N.'  AND customer_city='Algiers' ORDER BY id DESC LIMIT 1);
SET @t3 = (SELECT id FROM testimonials WHERE customer_name='Mehdi L.' AND customer_city='Constantine' ORDER BY id DESC LIMIT 1);

INSERT INTO testimonial_translations (testimonial_id, locale, body) VALUES
(@t1, 'ar', 'تجربة ممتازة من البداية للنهاية. التقرير كان مفصلاً جداً، والسعر شامل. وصلت السيارة في الوقت المتفق عليه وبحالة ممتازة.'),
(@t1, 'fr', 'Une expérience excellente de bout en bout. Le rapport était très détaillé, le prix tout compris. Livraison à la date prévue, état impeccable.'),
(@t1, 'en', 'Excellent experience from start to finish. The inspection report was very detailed, the price all-in. Delivered on time in perfect condition.'),

(@t2, 'ar', 'تواصل سريع وصريح، وأسعار أفضل بكثير من السوق المحلي. أنصح بشدة لمن يبحث عن سيارة كورية موثوقة.'),
(@t2, 'fr', 'Communication rapide et honnête, prix bien meilleurs que le marché local. Je recommande vivement pour qui cherche une coréenne fiable.'),
(@t2, 'en', 'Quick and honest communication, prices much better than the local market. Strongly recommended for anyone looking for a reliable Korean car.'),

(@t3, 'ar', 'فريق محترف. ساعدوني في كل خطوة من الفحص إلى التخليص الجمركي. السيارة وصلت كما هي في الصور تماماً.'),
(@t3, 'fr', 'Équipe professionnelle. Ils m\'ont accompagné à chaque étape, de l\'inspection au dédouanement. La voiture est arrivée exactement comme sur les photos.'),
(@t3, 'en', 'Professional team. They helped me at every step from inspection to customs clearance. The car arrived exactly as in the photos.');
