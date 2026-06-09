<?php
/**
 * @var \App\Core\View $this
 * @var array  $vehicle
 * @var string $title_en
 * @var array|null $cover
 * @var array  $images
 * @var array|null $inspection
 * @var array  $estimate
 * @var array|null $lead
 * @var array  $business
 * @var string $reference
 * @var string $generated
 * @var bool   $is_public
 * @var string $public_url
 */
$total_dzd_settings_rate = (float) ($estimate['fx_rate'] ?? 240);

// Build WhatsApp click-to-chat link if we have a recipient phone.
$waLink = null;
if (! $is_public && $lead) {
    $rawWa = ! empty($lead['whatsapp']) ? (string) $lead['whatsapp'] : (string) ($lead['phone'] ?? '');
    if ($rawWa !== '') {
        $phoneForWa = \App\Services\Phone::forWhatsapp($rawWa);
        $msg = "Hello " . trim((string) ($lead['name'] ?? '')) . ",\n\n"
             . "Here's our proposal for the " . $title_en . ":\n"
             . $public_url . "\n\n"
             . "Best regards,\n" . $business['name'];
        $waLink = 'https://wa.me/' . $phoneForWa . '?text=' . rawurlencode($msg);
    }
}
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title_en) ?> — Proposal</title>
    <style>
        /* ---------- Screen + print base ---------- */
        :root {
            --ink: #1e1e1e;
            --muted: #6b7280;
            --line: #e5e7eb;
            --brand: #1d4ed8;
            --warn: #92400e;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font: 13px/1.5 -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--ink);
            background: #f3f4f6;
        }
        .kae-prop {
            max-width: 820px;
            margin: 24px auto;
            background: #fff;
            padding: 32px 40px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .kae-prop h1 { font-size: 22px; margin: 0 0 4px; }
        .kae-prop h2 { font-size: 14px; margin: 24px 0 8px; padding-bottom: 4px; border-bottom: 1px solid var(--line); color: var(--muted); text-transform: uppercase; letter-spacing: 0.04em; }
        .kae-prop p, .kae-prop dd { margin: 0; }
        .kae-prop .muted { color: var(--muted); }
        .kae-prop .row { display: flex; gap: 24px; }
        .kae-prop .col { flex: 1; }
        .kae-prop .head {
            display: flex; align-items: flex-start; justify-content: space-between;
            padding-bottom: 16px; border-bottom: 2px solid var(--ink);
        }
        .kae-prop .brand-block .brand-name { font-weight: 700; font-size: 16px; }
        .kae-prop .ref-block { text-align: right; font-size: 11px; color: var(--muted); }
        .kae-prop .ref-block .ref { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; color: var(--ink); font-weight: 600; }

        .kae-prop dl.kv { display: grid; grid-template-columns: 40% 60%; row-gap: 4px; margin: 0; }
        .kae-prop dl.kv dt { color: var(--muted); }
        .kae-prop dl.kv dd { font-weight: 500; }

        .kae-prop .cost-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .kae-prop .cost-table td { padding: 6px 0; border-bottom: 1px solid var(--line); }
        .kae-prop .cost-table td:last-child { text-align: right; font-variant-numeric: tabular-nums; }
        .kae-prop .cost-table tr.total td {
            border-top: 2px solid var(--ink); border-bottom: none;
            padding-top: 10px; font-weight: 700; font-size: 15px;
        }
        .kae-prop .cost-table tr.subtotal td {
            border-top: 1px solid var(--ink); padding-top: 8px; font-weight: 600;
        }

        .kae-prop .inspection { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .kae-prop .inspection .score { padding: 8px 10px; background: #f9fafb; border: 1px solid var(--line); border-radius: 4px; }
        .kae-prop .inspection .score .label { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.04em; }
        .kae-prop .inspection .score .val   { font-size: 18px; font-weight: 700; }
        .kae-prop .inspection .score .val .pct { font-size: 11px; color: var(--muted); font-weight: 500; }

        .kae-prop .images { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-top: 8px; }
        .kae-prop .images img { width: 100%; height: 180px; object-fit: cover; border-radius: 4px; border: 1px solid var(--line); }

        .kae-prop .footer-note { font-size: 11px; color: var(--muted); margin-top: 24px; padding-top: 12px; border-top: 1px solid var(--line); }

        /* ---------- On-screen action bar (hidden when printing) ---------- */
        .kae-actions {
            position: sticky; top: 0; z-index: 10;
            background: #1f2937; color: #fff;
            padding: 10px 16px;
            display: flex; gap: 8px; align-items: center; justify-content: space-between;
        }
        .kae-actions .left { display: flex; gap: 12px; align-items: center; }
        .kae-actions a, .kae-actions button {
            background: transparent; color: #fff; border: 1px solid rgba(255,255,255,0.4);
            padding: 6px 12px; border-radius: 4px; font-size: 13px;
            text-decoration: none; cursor: pointer;
        }
        .kae-actions button.primary { background: #2563eb; border-color: #2563eb; }
        .kae-actions .hint { color: #d1d5db; font-size: 12px; }

        /* ---------- Print rules ---------- */
        @media print {
            body { background: #fff; }
            .kae-actions { display: none !important; }
            .kae-prop {
                margin: 0; max-width: none; box-shadow: none; padding: 0;
            }
            @page { margin: 14mm 14mm 18mm; size: A4; }
            h2 { page-break-after: avoid; }
            .images, .cost-table, .inspection { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

<?php if ($is_public): ?>
    <!-- Public viewer (recipient) — simpler bar -->
    <div class="kae-actions">
        <div class="left">
            <span class="hint">
                Proposal from <strong><?= e($business['name']) ?></strong>.
                Use your browser's <strong>Print → Save as PDF</strong> to download.
            </span>
        </div>
        <div>
            <button type="button" class="primary" onclick="window.print()">⤓ Save as PDF</button>
        </div>
    </div>
<?php else: ?>
    <!-- Admin bar — Email + WhatsApp + Print -->
    <div class="kae-actions">
        <div class="left">
            <a href="javascript:history.back()">← Back</a>
            <span class="hint">
                Public link: <code style="background: rgba(255,255,255,0.15); padding: 1px 4px; border-radius: 3px;"><?= e($public_url) ?></code>
            </span>
        </div>
        <div>
            <?php if (! empty($lead['email'])): ?>
                <?php
                $subject = rawurlencode('Proposal — ' . $title_en);
                $body    = rawurlencode("Hello " . ($lead['name'] ?? '') . ",\n\nHere's our proposal for the " . $title_en . ":\n" . $public_url . "\n\nBest regards,\n" . $business['name']);
                ?>
                <a href="mailto:<?= e((string) $lead['email']) ?>?subject=<?= $subject ?>&body=<?= $body ?>">📧 Email draft</a>
            <?php endif; ?>
            <?php if ($waLink !== null): ?>
                <a href="<?= e($waLink) ?>" target="_blank" rel="noopener" style="background: #25D366; border-color: #25D366; color: #fff;">💬 WhatsApp</a>
            <?php endif; ?>
            <button type="button" class="primary" onclick="window.print()">⤓ Print / Save PDF</button>
        </div>
    </div>
<?php endif; ?>

<div class="kae-prop">
    <!-- HEAD -->
    <div class="head">
        <div class="brand-block">
            <div class="brand-name"><?= e($business['name']) ?></div>
            <div class="muted" style="font-size: 11px; margin-top: 2px;"><?= e($business['site_url']) ?></div>
        </div>
        <div class="ref-block">
            <div>Proposal</div>
            <div class="ref"><?= e($reference) ?></div>
            <div>Date: <?= e($generated) ?></div>
        </div>
    </div>

    <!-- CUSTOMER -->
    <?php if ($lead): ?>
        <h2>Prepared for</h2>
        <div class="row">
            <div class="col">
                <p><strong><?= e((string) $lead['name']) ?></strong></p>
                <p class="muted">
                    <?= e((string) $lead['phone']) ?>
                    <?= ! empty($lead['email']) ? ' · ' . e((string) $lead['email']) : '' ?>
                    <?= ! empty($lead['city']) ? ' · ' . e((string) $lead['city']) : '' ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- VEHICLE -->
    <h2>Vehicle</h2>
    <div class="row">
        <div class="col">
            <h1><?= e($title_en) ?></h1>
            <p class="muted">
                VIN: <?= e((string) ($vehicle['vin'] ?? '—')) ?>
                · Status: <?= e((string) $vehicle['status']) ?>
            </p>
            <dl class="kv" style="margin-top: 10px;">
                <dt>Year</dt><dd><?= (int) $vehicle['year'] ?></dd>
                <dt>Mileage</dt><dd><?= e(format_mileage((int) $vehicle['mileage_km'])) ?></dd>
                <dt>Engine</dt><dd>
                    <?= ! empty($vehicle['engine_cc']) ? (int) $vehicle['engine_cc'] . ' cc' : '—' ?>
                    <?= ! empty($vehicle['engine_power_hp']) ? ' / ' . (int) $vehicle['engine_power_hp'] . ' hp' : '' ?>
                </dd>
                <dt>Transmission</dt><dd><?= e((string) $vehicle['transmission']) ?></dd>
                <dt>Fuel</dt><dd><?= e((string) $vehicle['fuel_type']) ?></dd>
                <dt>Drivetrain</dt><dd><?= e((string) ($vehicle['drivetrain'] ?? '—')) ?></dd>
                <?php if (! empty($vehicle['doors'])): ?>
                    <dt>Doors / Seats</dt>
                    <dd><?= (int) $vehicle['doors'] ?> doors · <?= (int) ($vehicle['seats'] ?? 0) ?: '—' ?> seats</dd>
                <?php endif; ?>
                <?php if (! empty($vehicle['exterior_color'])): ?>
                    <dt>Exterior</dt><dd><?= e((string) $vehicle['exterior_color']) ?></dd>
                <?php endif; ?>
                <?php if (! empty($vehicle['interior_color'])): ?>
                    <dt>Interior</dt><dd><?= e((string) $vehicle['interior_color']) ?></dd>
                <?php endif; ?>
                <dt>Sourcing</dt>
                <dd><?= e((string) ($vehicle['location'] ?? 'South Korea')) ?></dd>
            </dl>
        </div>
    </div>

    <!-- PHOTOS -->
    <?php if (! empty($images)): ?>
        <h2>Photos</h2>
        <div class="images">
            <?php foreach ($images as $img): ?>
                <img src="<?= e(url(image_url((string) $img['path']))) ?>" alt="">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- COST BREAKDOWN -->
    <h2>Cost breakdown (USD)</h2>
    <table class="cost-table">
        <tr>
            <td>Vehicle price</td>
            <td><?= e(format_price((float) $estimate['vehicle_usd'])) ?></td>
        </tr>
        <tr>
            <td>Shipping to Algeria</td>
            <td><?= e(format_price((float) $estimate['shipping_usd'])) ?></td>
        </tr>
        <tr>
            <td>Customs + TVA <span class="muted" style="font-weight: normal;">(estimated)</span></td>
            <td><?= e(format_price((float) $estimate['customs_usd'])) ?></td>
        </tr>
        <tr>
            <td>Service fee</td>
            <td><?= e(format_price((float) $estimate['service_fee_usd'])) ?></td>
        </tr>
        <tr class="total">
            <td>Total landed cost</td>
            <td><?= e(format_price((float) $estimate['total_usd'])) ?></td>
        </tr>
        <tr>
            <td class="muted">Equivalent in DZD</td>
            <td><?= e(format_price((float) $estimate['total_dzd'], 'DZD')) ?></td>
        </tr>
    </table>

    <!-- INSPECTION -->
    <?php if ($inspection): ?>
        <h2>Inspection scores</h2>
        <div class="inspection">
            <?php
            $rows = [
                'Overall'   => $inspection['overall_score'] ?? null,
                'Engine'    => $inspection['engine_score'] ?? null,
                'Exterior'  => $inspection['exterior_score'] ?? null,
                'Interior'  => $inspection['interior_score'] ?? null,
                'Tires'     => $inspection['tires_score'] ?? null,
                'Brakes'    => $inspection['brakes_score'] ?? null,
                'Electrical'=> $inspection['electrical_score'] ?? null,
            ];
            foreach ($rows as $label => $score):
                if ($score === null) continue; ?>
                <div class="score">
                    <div class="label"><?= e($label) ?></div>
                    <div class="val"><?= (int) $score ?><span class="pct">/100</span></div>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="muted" style="margin-top: 10px; font-size: 12px;">
            Accident history: <strong><?= e((string) ($inspection['accident_history'] ?? 'unknown')) ?></strong>
            <?php if (! empty($inspection['inspector_name'])): ?>
                · Inspector: <?= e((string) $inspection['inspector_name']) ?>
            <?php endif; ?>
            <?php if (! empty($inspection['inspected_at'])): ?>
                · Date: <?= e((string) $inspection['inspected_at']) ?>
            <?php endif; ?>
        </p>
    <?php endif; ?>

    <!-- CONTACT -->
    <h2>Next steps</h2>
    <p>
        To reserve this vehicle or ask a question, contact us:
    </p>
    <dl class="kv" style="grid-template-columns: 25% 75%;">
        <?php if (! empty($business['phone'])): ?>
            <dt>Phone</dt><dd><?= e($business['phone']) ?></dd>
        <?php endif; ?>
        <?php if (! empty($business['whatsapp'])): ?>
            <dt>WhatsApp</dt><dd><?= e($business['whatsapp']) ?></dd>
        <?php endif; ?>
        <?php if (! empty($business['email'])): ?>
            <dt>Email</dt><dd><?= e($business['email']) ?></dd>
        <?php endif; ?>
        <?php if (! empty($business['site_url'])): ?>
            <dt>Website</dt><dd><?= e($business['site_url']) ?></dd>
        <?php endif; ?>
    </dl>

    <p class="footer-note">
        Pricing is an estimate using current Algerian customs/TVA rates and FX. Final landed
        cost depends on the customs assessment at the port of entry and the FX rate on the
        day the deposit is settled. This proposal is valid for 14 days from <?= e($generated) ?>.
        Document reference <strong><?= e($reference) ?></strong>.
    </p>
</div>

</body>
</html>
