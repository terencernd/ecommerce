<?php
/**
 * generer_facture.php
 * Génère une facture PDF en PHP pur, SANS librairie externe.
 * À appeler après la validation de commande.
 *
 * Usage : genererFacturePDF($panier, $adresse, $total, $client, $methode, $commande_id)
 * Retourne le chemin du fichier PDF généré.
 */

function genererFacturePDF(array $panier, $adresse, float $total, string $client, string $methode, int $commande_id): string
{
    $invoice_number = 'CMD-' . date('Y') . '-' . str_pad($commande_id, 5, '0', STR_PAD_LEFT);
    $date           = date('d/m/Y');
    $tva_rate       = 20;
    $subtotal       = 0;

    foreach ($panier as $item) {
        $subtotal += $item['prix'] * $item['quantite'];
    }
    $tva   = $subtotal * ($tva_rate / 100);
    $total_ttc = $subtotal + $tva;

    // Adresse livraison
    $adresse_html = 'Non renseignee';
    if ($adresse) {
        $adresse_html = htmlspecialchars($adresse['prenom'] ?? '') . ' ' . htmlspecialchars($adresse['nom'] ?? '') . '<br>'
            . htmlspecialchars($adresse['rue'] ?? '') . '<br>'
            . htmlspecialchars($adresse['code_postal'] ?? '') . ' ' . htmlspecialchars($adresse['ville'] ?? '') . '<br>'
            . htmlspecialchars($adresse['pays'] ?? '');
    }

    // Lignes articles
    $lignes_html = '';
    $bg = false;
    foreach ($panier as $item) {
        $line_total   = $item['prix'] * $item['quantite'];
        $bg_color     = $bg ? '#f9f9f9' : '#ffffff';
        $lignes_html .= "
        <tr style='background:{$bg_color};'>
            <td style='padding:9px 12px; border-bottom:1px solid #e0e0e0;'>" . htmlspecialchars($item['nom']) . "</td>
            <td style='padding:9px 12px; border-bottom:1px solid #e0e0e0; text-align:center;'>" . intval($item['quantite']) . "</td>
            <td style='padding:9px 12px; border-bottom:1px solid #e0e0e0; text-align:right;'>" . number_format($item['prix'], 2) . " &euro;</td>
            <td style='padding:9px 12px; border-bottom:1px solid #e0e0e0; text-align:right;'>" . number_format($line_total, 2) . " &euro;</td>
        </tr>";
        $bg = !$bg;
    }

    // HTML complet de la facture
    $html = '<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Facture ' . $invoice_number . '</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: Arial, sans-serif; font-size: 13px; color: #333; padding: 40px; background: #fff; }
    .header { width:100%; margin-bottom:30px; overflow:hidden; }
    .header-left { float:left; }
    .header-right { float:right; text-align:right; }
    .company-name { font-size:22px; font-weight:bold; color:#1a3a5c; }
    .company-sub { color:#777; font-size:12px; margin-top:4px; }
    .badge { display:inline-block; background:#1a3a5c; color:white; padding:5px 16px; border-radius:4px; font-size:16px; font-weight:bold; letter-spacing:1px; }
    .invoice-meta { color:#555; margin-top:8px; font-size:13px; line-height:1.7; }
    .clearfix { clear:both; }
    .blocks { width:100%; margin:24px 0; overflow:hidden; }
    .block { float:left; width:48%; background:#f4f6f8; border-left:4px solid #1a3a5c; padding:12px 16px; border-radius:0 6px 6px 0; }
    .block-right { float:right; }
    .block-title { font-weight:bold; color:#1a3a5c; margin-bottom:6px; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; }
    table.items { width:100%; border-collapse:collapse; margin-top:10px; }
    table.items th { background:#1a3a5c; color:white; padding:10px 12px; text-align:left; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; }
    .totals { width:280px; margin-left:auto; margin-top:20px; border-collapse:collapse; }
    .totals td { padding:6px 12px; border:none; font-size:13px; }
    .totals tr.sep td { border-top:1px solid #ddd; padding-top:10px; }
    .totals tr.final td { background:#1a3a5c; color:white; font-weight:bold; font-size:15px; padding:10px 12px; }
    .footer { margin-top:50px; font-size:11px; color:#999; text-align:center; border-top:1px solid #eee; padding-top:12px; }
    .print-btn { display:block; margin:0 auto 30px auto; background:#1a3a5c; color:white; border:none; padding:12px 30px; font-size:15px; border-radius:6px; cursor:pointer; }
    @media print { .print-btn { display:none; } }
</style>
</head>
<body>

<button class="print-btn" onclick="window.print()">Imprimer / Sauvegarder en PDF</button>

<div class="header">
    <div class="header-left">
        <div class="company-name">Volley Club Boutique</div>
        <div class="company-sub">contact@volleyclub.fr</div>
    </div>
    <div class="header-right">
        <div class="badge">FACTURE</div>
        <div class="invoice-meta">
            <strong>N&deg; ' . $invoice_number . '</strong><br>
            Date : ' . $date . '<br>
            Paiement : ' . htmlspecialchars(ucfirst($methode)) . '
        </div>
    </div>
    <div class="clearfix"></div>
</div>

<div class="blocks">
    <div class="block">
        <div class="block-title">Client</div>
        ' . htmlspecialchars($client) . '
    </div>
    <div class="block block-right">
        <div class="block-title">Adresse de livraison</div>
        ' . $adresse_html . '
    </div>
    <div class="clearfix"></div>
</div>

<table class="items">
    <thead>
        <tr>
            <th>Article</th>
            <th style="text-align:center">Qte</th>
            <th style="text-align:right">Prix unitaire</th>
            <th style="text-align:right">Total HT</th>
        </tr>
    </thead>
    <tbody>
        ' . $lignes_html . '
    </tbody>
</table>

<table class="totals">
    <tr>
        <td>Sous-total HT</td>
        <td style="text-align:right">' . number_format($subtotal, 2) . ' &euro;</td>
    </tr>
    <tr class="sep">
        <td>TVA (' . $tva_rate . '%)</td>
        <td style="text-align:right">' . number_format($tva, 2) . ' &euro;</td>
    </tr>
    <tr class="final">
        <td>Total TTC</td>
        <td style="text-align:right">' . number_format($total_ttc, 2) . ' &euro;</td>
    </tr>
</table>

<div class="footer">
    Volley Club Boutique &mdash; contact@volleyclub.fr &mdash; Merci pour votre commande !
</div>

</body>
</html>';

    // Sauvegarde dans /factures/
    $dir = __DIR__ . '/factures/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $filename = $dir . $invoice_number . '.html';
    file_put_contents($filename, $html);

    return 'factures/' . $invoice_number . '.html';
}