<?php
/** @var \App\Core\View $this */
$this->extends('layouts/admin');
$today = date('M j, Y');
?>
<?php $this->section('content'); ?>
<div class="container-fluid">
    <div class="kae-page-head">
        <div>
            <h1>Regulations &amp; operational reference</h1>
            <p class="text-muted mb-0 small">
                Internal reference for Korea → Algeria car export. Static page; edit
                <code>resources/views/admin/regulations.php</code> when something changes.
            </p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-dark btn-sm">Print</button>
        </div>
    </div>

    <!-- ============ DISCLAIMER ============ -->
    <div class="alert alert-warning">
        <strong>Read before relying on anything here.</strong>
        <p class="mb-2 mt-2 small">
            Algerian vehicle-import rules and Korean export procedures change frequently
            (age limits, fuel restrictions, duty rates, conformity requirements). Items
            tagged <span class="badge bg-danger">VERIFY</span> are the most volatile —
            confirm the current value with your customs broker, freight forwarder, or the
            Algerian Embassy in Seoul before acting on them.
        </p>
        <p class="mb-0 small">
            Last compiled: <strong><?= e($today) ?></strong>. Review quarterly.
        </p>
    </div>

    <!-- ============ JUMP NAV ============ -->
    <div class="kae-card p-3 mb-3">
        <strong class="me-2">Jump to:</strong>
        <a href="#korea"    class="me-3">Korea (export)</a>
        <a href="#algeria"  class="me-3">Algeria (import)</a>
        <a href="#shipping" class="me-3">Shipping</a>
        <a href="#docs"     class="me-3">Documents checklist</a>
        <a href="#pitfalls" class="me-3">Pitfalls</a>
        <a href="#contacts" class="me-3">Contacts</a>
        <a href="#sources"  class="me-3">Where to verify</a>
    </div>

    <!-- ============ KOREA ============ -->
    <section id="korea" class="kae-card p-3 p-md-4 mb-3">
        <h2 class="h4">🇰🇷 Korea — export side</h2>

        <h3 class="h6 mt-3">Vehicle de-registration (말소등록 / Malso Deungrok)</h3>
        <p class="small">
            Before a vehicle can leave Korea, its registration must be cancelled at the
            local 차량등록사업소 (vehicle registration office). The output is a
            <em>Certificate of Vehicle Cancellation for Export</em>
            (수출용 자동차 말소사실증명서) — the customs office at the port asks for this.
        </p>

        <h3 class="h6 mt-3">Key documents you'll need on the Korean side</h3>
        <ul class="small">
            <li>Sales contract / Bill of Sale (매매 계약서)</li>
            <li>Vehicle registration cancellation certificate (말소사실증명서)</li>
            <li>Commercial Invoice (exporter → importer)</li>
            <li>Packing List</li>
            <li>Bill of Lading (issued by carrier / forwarder)</li>
            <li>Export declaration (수출신고필증) — filed by you or your forwarder</li>
        </ul>

        <h3 class="h6 mt-3">VAT (부가가치세) treatment</h3>
        <p class="small">
            Korea's 10% VAT is <strong>zero-rated on exports</strong>. If you're a
            registered exporter (사업자등록 + 수출), you can claim VAT back on the
            purchase. <span class="badge bg-danger">VERIFY</span> with your accountant —
            requires correct documentation and a registered business.
        </p>

        <h3 class="h6 mt-3">Auction access (Glovis / Pyeongtaek / Lotte / Bay)</h3>
        <p class="small">
            Korean auto auctions require a dealer license (자동차매매업 허가). Foreigners
            typically work through a Korean dealer partner who bids on your behalf for a
            small commission. Direct foreign access is rare.
        </p>

        <h3 class="h6 mt-3">Cross-border payments (외환거래법)</h3>
        <p class="small">
            FX outflows above declaration thresholds need to be reported to your bank
            under the Foreign Exchange Transactions Act. <span class="badge bg-danger">VERIFY</span>
            current threshold with your bank — has been around USD 5,000 for individuals
            but commercial flows have different rules.
        </p>

        <h3 class="h6 mt-3">Major export ports</h3>
        <ul class="small">
            <li><strong>Busan</strong> (부산) — largest, most carriers serve it</li>
            <li><strong>Incheon</strong> (인천) — close to Seoul, good for sourcing in the capital region</li>
            <li><strong>Pyeongtaek</strong> (평택) — major car-export hub, lots of auctions nearby</li>
            <li><strong>Mokpo</strong> (목포) — south-west, used for some Africa-bound RoRo</li>
        </ul>
    </section>

    <!-- ============ ALGERIA ============ -->
    <section id="algeria" class="kae-card p-3 p-md-4 mb-3">
        <h2 class="h4">🇩🇿 Algeria — import side</h2>

        <div class="alert alert-danger small mb-3">
            <strong>High-volatility area.</strong> Algerian car-import rules have changed
            multiple times in recent years (used-import suspensions, age caps,
            diesel restrictions, dealer-vs-individual regimes). Every line here should be
            re-checked against the current Code des douanes and the JORA (Journal Officiel)
            before relying on it.
        </div>

        <h3 class="h6 mt-3">Vehicle age cap <span class="badge bg-danger">VERIFY</span></h3>
        <p class="small">
            Most recent published rule: used imports are permitted only if the vehicle is
            <strong>under 3 years old</strong> (counted from year of first registration).
            <em>This has shifted multiple times since 2017</em> — confirm current cap with
            your transitaire before quoting a buyer.
        </p>

        <h3 class="h6 mt-3">Fuel type restrictions <span class="badge bg-danger">VERIFY</span></h3>
        <p class="small">
            Diesel imports have been subject to additional conditions / partial bans.
            Petrol, hybrid and electric have generally been less restricted. Confirm
            which fuel categories are currently importable.
        </p>

        <h3 class="h6 mt-3">Personal vs commercial import</h3>
        <p class="small">
            Two distinct regimes — different paperwork, different limits.
        </p>
        <ul class="small">
            <li><strong>Personal (individual)</strong> — typically 1 vehicle per year per
                person, requires proof of residence abroad or specific conditions.
                <span class="badge bg-danger">VERIFY</span></li>
            <li><strong>Commercial (concessionnaire / dealer)</strong> — requires importer
                registration, dealer status (agrément), bank domiciliation, and full
                customs procedures.</li>
        </ul>

        <h3 class="h6 mt-3">Documents required at customs</h3>
        <ul class="small">
            <li>Certificate of origin (Certificat d'origine)</li>
            <li>Original carte grise from country of export (Korean vehicle reg before cancellation)</li>
            <li>Bill of Lading — original</li>
            <li>Commercial Invoice — sworn / certified</li>
            <li>Certificate of Conformity (Certificat de conformité)</li>
            <li>Marine insurance certificate</li>
            <li>Importer's national ID + (for commercial) trade register + agrément</li>
            <li>Bank domiciliation document (Domiciliation bancaire)</li>
            <li>Mise à la consommation declaration (D10 / form depending on regime)</li>
        </ul>

        <h3 class="h6 mt-3">Customs duty + TVA <span class="badge bg-danger">VERIFY</span></h3>
        <p class="small">
            The estimator currently uses <strong>customs 30%</strong> and <strong>TVA 19%</strong>
            (configurable in <a href="/admin/settings">Settings</a> → Cost estimator).
            Actual rates vary by HS code / vehicle category / fuel type / engine size.
            Confirm against the current Tarif Douanier Algérien before quoting.
        </p>

        <h3 class="h6 mt-3">FX / payment</h3>
        <p class="small">
            Algerian importers need a <strong>domiciliation bancaire</strong> at a bank
            before they can transfer foreign currency to pay the exporter. Without it,
            no FX outflow is authorised. Personal imports use a different mechanism
            (often pre-existing foreign earnings).
        </p>

        <h3 class="h6 mt-3">Ports of entry</h3>
        <ul class="small">
            <li><strong>Algiers</strong> — largest, most carrier coverage</li>
            <li><strong>Oran</strong> — west, often cheaper to Algiers for western customers</li>
            <li><strong>Annaba</strong> — east</li>
            <li><strong>Mostaganem</strong> — supplementary, smaller car volume</li>
        </ul>

        <h3 class="h6 mt-3">Post-clearance</h3>
        <p class="small">
            After customs release: technical inspection (contrôle technique),
            registration (carte grise nationale), license plates, insurance.
        </p>
    </section>

    <!-- ============ SHIPPING ============ -->
    <section id="shipping" class="kae-card p-3 p-md-4 mb-3">
        <h2 class="h4">🚢 Shipping &amp; logistics</h2>

        <h3 class="h6 mt-3">Modes</h3>
        <ul class="small">
            <li><strong>RoRo (Roll-on / Roll-off)</strong> — cheapest for cars; the
                vehicle is driven onto the ship. Standard for used-car exports. Need
                operational vehicle (running, with battery, etc.).</li>
            <li><strong>Container (FCL 1×40' = 2-3 cars, 1×20' = 1 car)</strong> — more
                secure, useful for high-value or non-operational vehicles. More expensive.</li>
        </ul>

        <h3 class="h6 mt-3">Major car-carrier lines</h3>
        <ul class="small">
            <li>Hyundai Glovis</li>
            <li>EUKOR Car Carriers</li>
            <li>Wallenius Wilhelmsen</li>
            <li>K Line / MOL / NYK (Japanese majors with Korean services)</li>
            <li>CMA CGM (mixed cargo + RoRo)</li>
        </ul>

        <h3 class="h6 mt-3">Typical transit (Korea → Algeria) <span class="badge bg-danger">VERIFY</span></h3>
        <p class="small">
            Busan → Algiers via Mediterranean routing: roughly <strong>30–45 days</strong>
            door-to-port depending on transhipment and carrier schedule. Confirm with
            your booked carrier; transit shifts with route changes.
        </p>

        <h3 class="h6 mt-3">Marine insurance</h3>
        <p class="small">
            Always insure for at least 110% of the CIF value (vehicle + freight + insurance
            + 10% buffer). Carrier liability alone won't cover total loss.
        </p>
    </section>

    <!-- ============ DOCS CHECKLIST ============ -->
    <section id="docs" class="kae-card p-3 p-md-4 mb-3">
        <h2 class="h4">📋 End-to-end documents checklist</h2>
        <p class="small text-muted">
            Use this as a per-shipment checklist. Print one per car.
        </p>

        <h3 class="h6 mt-3">A. Pre-purchase (Korea)</h3>
        <ul class="small">
            <li>☐ Auction history report (encar / dealer partner)</li>
            <li>☐ Visual inspection report (your inspector in Korea)</li>
            <li>☐ VIN-verified specifications (use the Decode button on /admin/vehicles/create)</li>
        </ul>

        <h3 class="h6 mt-3">B. Purchase</h3>
        <ul class="small">
            <li>☐ Bill of Sale (매매계약서)</li>
            <li>☐ Korean registration certificate (자동차등록증) — copy + later cancelled original</li>
            <li>☐ Proof of payment to seller</li>
        </ul>

        <h3 class="h6 mt-3">C. Pre-export (Korea)</h3>
        <ul class="small">
            <li>☐ De-registration certificate (말소사실증명서)</li>
            <li>☐ Export declaration (수출신고필증)</li>
            <li>☐ Commercial Invoice (you → buyer)</li>
            <li>☐ Packing List</li>
            <li>☐ Korea-side VAT records (for your own bookkeeping)</li>
        </ul>

        <h3 class="h6 mt-3">D. Shipping</h3>
        <ul class="small">
            <li>☐ Booking confirmation (carrier or forwarder)</li>
            <li>☐ Bill of Lading (original)</li>
            <li>☐ Marine insurance certificate (110% CIF)</li>
            <li>☐ Vessel name + ETA Algeria — track this on the reservation/order page</li>
        </ul>

        <h3 class="h6 mt-3">E. Pre-import (Algeria)</h3>
        <ul class="small">
            <li>☐ Bank domiciliation (commercial) or personal regime proof</li>
            <li>☐ Importer registration / agrément (commercial)</li>
            <li>☐ Buyer's national ID / passport</li>
            <li>☐ Pre-cleared HS code with the transitaire</li>
        </ul>

        <h3 class="h6 mt-3">F. Customs clearance (Algeria)</h3>
        <ul class="small">
            <li>☐ All Korea-side originals</li>
            <li>☐ Certificate of conformity</li>
            <li>☐ Mise à la consommation declaration filed</li>
            <li>☐ Customs duty + TVA paid</li>
        </ul>

        <h3 class="h6 mt-3">G. Post-clearance (Algeria)</h3>
        <ul class="small">
            <li>☐ Technical inspection (contrôle technique)</li>
            <li>☐ Carte grise nationale</li>
            <li>☐ License plates</li>
            <li>☐ Insurance (assurance auto)</li>
        </ul>
    </section>

    <!-- ============ PITFALLS ============ -->
    <section id="pitfalls" class="kae-card p-3 p-md-4 mb-3">
        <h2 class="h4">⚠️ Common pitfalls</h2>
        <ul class="small">
            <li><strong>Wrong HS code</strong> → customs re-assesses + back-duty + fines.
                Have the transitaire confirm HS before shipping.</li>
            <li><strong>Age miscalculation</strong> — Algerian customs counts age from
                year of <em>first registration</em>, not model year. A 2024 model registered
                in 2024 cuts it close to the 3-year cap by 2027.</li>
            <li><strong>VIN / odometer fraud at Korean auctions</strong> — rare but happens.
                Use Encar history + a trusted inspector.</li>
            <li><strong>Missing certificate of conformity</strong> → vehicle stuck at the
                port, demurrage costs add up daily.</li>
            <li><strong>Currency declaration omissions</strong> on the Korean side → bank
                may freeze the transfer or flag for AML review.</li>
            <li><strong>RoRo vs container</strong> — RoRo requires running vehicle.
                If the car is non-op, container only.</li>
            <li><strong>Demurrage at Algerian port</strong> — if clearance drags past free
                days (~7), per-day storage charges escalate fast.</li>
            <li><strong>Diesel restrictions</strong> — confirm before sourcing a diesel
                vehicle; a sourced diesel that can't clear is a total loss.</li>
            <li><strong>FX rate movement</strong> — between auction win and final invoice,
                USD/DZD can shift; price in USD, set FX margin in settings.</li>
        </ul>
    </section>

    <!-- ============ CONTACTS ============ -->
    <section id="contacts" class="kae-card p-3 p-md-4 mb-3">
        <h2 class="h4">📞 Key contacts (fill in your own)</h2>
        <p class="small text-muted">Edit this list in the view file as you build the network.</p>
        <ul class="small">
            <li><strong>Customs broker / transitaire (Algeria):</strong> ____________________</li>
            <li><strong>Freight forwarder (Korea):</strong> ____________________</li>
            <li><strong>Korean dealer partner (auction access):</strong> ____________________</li>
            <li><strong>Pre-shipment inspector (Korea):</strong> ____________________</li>
            <li><strong>Bank — Korea side (FX outflow):</strong> ____________________</li>
            <li><strong>Bank — Algeria side (domiciliation):</strong> ____________________</li>
            <li><strong>Algerian Embassy in Seoul:</strong> verify number + address</li>
            <li><strong>KOTRA Algeria desk:</strong> verify contact</li>
        </ul>
    </section>

    <!-- ============ SOURCES ============ -->
    <section id="sources" class="kae-card p-3 p-md-4 mb-3">
        <h2 class="h4">🔍 Where to verify (authoritative sources)</h2>
        <ul class="small">
            <li><strong>Algerian Customs (Direction Générale des Douanes):</strong>
                <a href="https://www.douane.gov.dz" target="_blank" rel="noopener">douane.gov.dz</a>
                — Tariff search, regulatory updates.</li>
            <li><strong>JORA (Journal Officiel):</strong>
                <a href="https://www.joradp.dz" target="_blank" rel="noopener">joradp.dz</a>
                — All published laws and decrees. Search "véhicules" / "importation".</li>
            <li><strong>Korean Customs Service:</strong>
                <a href="https://www.customs.go.kr" target="_blank" rel="noopener">customs.go.kr</a>
                — Export procedures, HS codes, statistics.</li>
            <li><strong>data.go.kr (Open data):</strong>
                <a href="https://www.data.go.kr" target="_blank" rel="noopener">data.go.kr</a>
                — Public datasets including HS code APIs.</li>
            <li><strong>Algerian Embassy in Seoul:</strong> search for current contact;
                they confirm document legalisation requirements.</li>
            <li><strong>Encar:</strong>
                <a href="https://www.encar.com" target="_blank" rel="noopener">encar.com</a>
                — Korean used-car market reference.</li>
        </ul>
    </section>

    <p class="text-muted small">
        End of reference. When you learn something new, edit
        <code>resources/views/admin/regulations.php</code> and bump the
        "Last compiled" date at the top.
    </p>
</div>
<?php $this->endSection(); ?>
