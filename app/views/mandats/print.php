<?php
/* app/views/mandats/print.php — Mandat officiel TGI Niamey (1 page A4) */

// ─── Libellés selon le type de mandat ─────────────────────────────────
$typeLabels = [
    'arret'        => 'MANDAT D\'ARRÊT',
    'depot'        => 'MANDAT DE DÉPÔT',
    'amener'       => 'MANDAT D\'AMENER',
    'comparution'  => 'MANDAT DE COMPARUTION',
    'perquisition' => 'MANDAT DE PERQUISITION',
    'liberation'   => 'MANDAT DE LIBÉRATION',
];
$typeArticles = [
    'arret'        => "l'article 122 du Code de Procédure Pénale",
    'depot'        => "l'article 65 du Code de Procédure Pénal",
    'amener'       => "l'article 121 du Code de Procédure Pénale",
    'comparution'  => "l'article 120 du Code de Procédure Pénale",
    'perquisition' => "les articles 56 et suivants du Code de Procédure Pénale",
    'liberation'   => "les dispositions du Code de Procédure Pénale",
];
$tl         = $typeLabels[$mandat['type_mandat']]   ?? strtoupper($mandat['type_mandat']);
$article    = $typeArticles[$mandat['type_mandat']] ?? "l'article 65 du Code de Procédure Pénal";
$isDepot    = ($mandat['type_mandat'] === 'depot');
$isFlagrant = !empty($mandat['flagrant_delit']) || $isDepot;

// ─── Cible ────────────────────────────────────────────────────────────
if (!empty($mandat['detenu_label']))      $cible = $mandat['detenu_label'];
elseif (!empty($mandat['partie_label']))  $cible = $mandat['partie_label'];
elseif (!empty($mandat['nouveau_nom']))   $cible = trim(($mandat['nouveau_prenom'] ?? '') . ' ' . $mandat['nouveau_nom']);
else                                      $cible = '';

$ddn         = $mandat['nouveau_ddn']            ?? '';
$lieuNais    = $mandat['nouveau_lieu_naissance'] ?? '';
$nationalite = $mandat['nouveau_nationalite']    ?? 'Nigérienne';
$profession  = $mandat['nouveau_profession']     ?? '';
$domicile    = $mandat['nouveau_adresse']        ?? '';
$sitFamille  = $mandat['situation_famille']      ?? '';
$condamn     = $mandat['condamnations']          ?? '';
$serviceMil  = $mandat['service_militaire']      ?? '';
$infraction  = $mandat['infraction_libelle']     ?? '';
$motif       = $mandat['motif']                  ?? '';
$lieuExec    = $mandat['lieu_execution']         ?? "la Maison d'Arrêt de Niamey";
$numeroRP    = $mandat['numero']                 ?? '';
$numeroRG    = $mandat['numero_rg']              ?? '';
$sexeF       = (($mandat['sexe'] ?? '') === 'F');

// ─── Paramètres tribunal ──────────────────────────────────────────────
try {
    $db = Database::getInstance()->getPDO();
    $prows = $db->query("SELECT cle, valeur FROM parametres_tribunal")->fetchAll(\PDO::FETCH_KEY_PAIR);
} catch (\Exception $e) { $prows = []; }
$p = function (string $cle, string $def = '') use ($prows): string { return $prows[$cle] ?? $def; };

$entete1  = $p('doc_entete_ligne1', 'REPUBLIQUE DU NIGER');
$entete2  = $p('doc_entete_ligne2', "COUR D'APPEL DE NIAMEY");
$entete3  = $p('doc_entete_ligne3', 'TRIBUNAL DE GRANDE INSTANCE HORS CLASSE DE NIAMEY');
$entete4  = $p('doc_entete_ligne4', 'CABINET DU PROCUREUR DE LA REPUBLIQUE');
$piedPage = $p('doc_pied_page', 'Document officiel — TGI-NY — Niamey');
$qrActif  = $p('doc_qr_code_actif', '1') === '1';
$qrBase   = rtrim($p('doc_qr_code_base_url', BASE_URL), '/');
$qrUrl    = $qrBase . '/mandats/show/' . $mandat['id'];

// ─── Helper : valeur ou pointillés ────────────────────────────────────
$dot = function (?string $v, int $n = 30): string {
    $v = trim((string)$v);
    return $v === '' ? str_repeat('.', $n) : htmlspecialchars($v);
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title><?= $tl ?> — <?= htmlspecialchars($numeroRP) ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }

@page { size: A4 portrait; margin: 8mm 12mm; }

html, body { width: 100%; height: 100%; }

body {
    font-family: 'Times New Roman', Times, serif;
    font-size: 10.5pt;
    color: #000;
    background: #fff;
    padding: 6mm 10mm;
    line-height: 1.35;
}

/* ═══ En-tête ═══ */
.entete { margin-bottom: 8px; }
.entete .ligne {
    font-weight: bold;
    text-decoration: underline;
    font-size: 9.5pt;
    line-height: 1.4;
}

/* ═══ Titre ═══ */
.titre-central { text-align: center; margin: 8px 0 6px; }
.titre-central h1 {
    font-size: 17pt;
    font-weight: bold;
    text-decoration: underline;
    letter-spacing: 1px;
    display: inline-block;
    margin: 0;
}
.titre-central .sous-titre {
    font-style: italic;
    font-weight: bold;
    font-size: 12pt;
    margin-top: 1px;
}
.titre-central .au-nom {
    font-weight: bold;
    font-size: 10.5pt;
    margin-top: 6px;
    text-decoration: underline;
}

/* ═══ Corps ═══ */
.corps { margin-top: 4px; }
.corps p { margin-bottom: 4px; text-align: justify; }

.nous-line { text-align: center; margin: 6px 0 2px; }
.nous-line .label { font-weight: bold; }
.nous-line .val {
    display: inline-block;
    min-width: 320px;
    border-bottom: 1px solid #000;
    padding: 0 4px;
    text-align: center;
}

.center-small { text-align: center; font-size: 10pt; margin: 2px 0; }

/* ═══ Layout 2 colonnes ═══ */
.layout {
    display: grid;
    grid-template-columns: 165px 1fr;
    gap: 0 12px;
    margin-top: 6px;
    page-break-inside: avoid;
}

.col-gauche {
    font-size: 9.5pt;
    line-height: 1.55;
}
.col-gauche .item { margin-bottom: 6px; }
.col-gauche .item em { font-style: italic; display: block; }
.col-gauche .underline {
    display: inline-block;
    min-width: 80px;
    border-bottom: 1px solid #000;
}

/* ═══ Identité ═══ */
.identite .ligne {
    margin: 3px 0;
    line-height: 1.6;
}
.identite .lbl  { font-weight: 600; }
.identite .val  {
    display: inline-block;
    border-bottom: 1px solid #000;
    padding: 0 3px;
    min-height: 14px;
    font-size: 10pt;
}

.w-large  { min-width: 240px; }
.w-medium { min-width: 130px; }
.w-small  { min-width: 90px;  }
.w-tiny   { min-width: 50px;  }

/* ═══ Flagrant ═══ */
.flagrant-line {
    margin: 8px 0 4px;
    font-size: 10.5pt;
}
.flagrant-line .val {
    display: inline-block;
    border-bottom: 1px solid #000;
    min-width: 320px;
    padding: 0 3px;
}

/* ═══ Injonction ═══ */
.injonction { margin: 6px 0; text-align: justify; font-size: 10pt; }
.injonction p { margin-bottom: 4px; }

/* ═══ Signature ═══ */
.signature { margin-top: 10px; text-align: center; page-break-inside: avoid; }
.signature .lieu-date {
    text-align: left;
    margin: 4px 0 18px;
    font-style: italic;
    font-size: 10pt;
}
.signature .lieu-date .val {
    display: inline-block;
    border-bottom: 1px solid #000;
    min-width: 180px;
    padding: 0 4px;
}
.signature .titre-sig {
    font-weight: bold;
    text-decoration: underline;
    text-transform: uppercase;
    font-size: 10.5pt;
    margin-top: 30px;
}
.signature .nom-sig {
    font-size: 9pt;
    margin-top: 2px;
    font-style: italic;
}

/* ═══ QR Code ═══ */
.qr-block {
    position: absolute;
    top: 8mm;
    right: 12mm;
    text-align: center;
    font-size: 7pt;
}
.qr-block canvas { display: block; }
.qr-block small { color: #555; display: block; margin-top: 1px; font-size: 6.5pt; }

/* ═══ Pied ═══ */
.pied {
    margin-top: 8px;
    padding-top: 4px;
    border-top: 1px solid #999;
    font-size: 7.5pt;
    color: #555;
    text-align: center;
}

/* ═══ Impression ═══ */
@media print {
    body {
        padding: 4mm 8mm;
        font-size: 10pt;
    }
    .titre-central h1 { font-size: 16pt; }
    .titre-central .sous-titre { font-size: 11.5pt; }
    .qr-block { position: absolute; top: 4mm; right: 8mm; }
    .no-print { display: none !important; }
    /* éviter les sauts de page */
    .layout, .signature, .injonction, .flagrant-line { page-break-inside: avoid; }
}
</style>
</head>
<body onload="initQR();window.setTimeout(function(){window.print();},500)">

<!-- ═══════════════ EN-TÊTE ═══════════════ -->
<div class="entete">
    <div class="ligne"><?= htmlspecialchars($entete1) ?></div>
    <div class="ligne"><?= htmlspecialchars($entete2) ?></div>
    <div class="ligne"><?= htmlspecialchars($entete3) ?></div>
    <div class="ligne"><?= htmlspecialchars($entete4) ?></div>
</div>

<!-- ═══════════════ QR CODE ═══════════════ -->
<?php if ($qrActif): ?>
<div class="qr-block">
    <canvas id="qrCanvas" width="60" height="60"></canvas>
    <small>Vérifier en ligne</small>
</div>
<?php endif; ?>

<!-- ═══════════════ TITRE ═══════════════ -->
<div class="titre-central">
    <h1><?= $tl ?>.</h1>
    <?php if ($isFlagrant): ?>
        <div class="sous-titre">EN CAS DE FLAGRANT DELIT</div>
    <?php endif; ?>
    <div class="au-nom">REPUBLIQUE DU NIGER — AU NOM DU PEUPLE NIGERIEN</div>
</div>

<!-- ═══════════════ CORPS ═══════════════ -->
<div class="corps">

    <div class="nous-line">
        <span class="label">Nous</span>
        <span class="val"><?= htmlspecialchars($mandat['emetteur_nom'] ?? '') ?></span>
    </div>

    <p class="center-small">
        <?= htmlspecialchars($mandat['emetteur_role'] ?? 'Procureur de la République') ?>
        près le Tribunal de Grande Instance Hors Classe de Niamey,
    </p>

    <p class="center-small">En vertu de <?= $article ?> ;</p>

    <p class="center-small">
        Mandons et ordonnons à tous agents de la Force Publique de conduire à la
        <?= htmlspecialchars($lieuExec) ?> de notre Siège en se conformant à la loi :
    </p>

    <!-- Bloc 2 colonnes -->
    <div class="layout">

        <div class="col-gauche">
            <div class="item">
                <strong>N°</strong>
                <span class="underline"><?= htmlspecialchars($numeroRP) ?></span> /RP
            </div>
            <div class="item"><em>Le mandat ci-contre</em></div>
            <div class="item"><em>A été exhibé au prévenu</em></div>
            <div class="item">
                <em>Par moi Procureur de</em>
                <em>La République le</em>
                <span class="underline">
                    <?= !empty($mandat['date_exhibe']) ? date('d/m/Y', strtotime($mandat['date_exhibe'])) : '' ?>
                </span>
            </div>
        </div>

        <div class="identite">
            <div class="ligne">
                <span class="lbl">L<?= $sexeF ? 'a' : 'e' ?> nommé<?= $sexeF ? 'e' : '' ?> :</span>
                <span class="val w-large"><?= $dot($cible, 40) ?></span>
                <span class="lbl">Né<?= $sexeF ? 'e' : '' ?></span>
                <span class="val w-tiny"><?= $ddn ? date('d/m/Y', strtotime($ddn)) : str_repeat('.', 10) ?></span>
            </div>

            <div class="ligne">
                <span class="lbl">A</span>
                <span class="val w-medium"><?= $dot($lieuNais, 18) ?></span>
                <span class="lbl">De</span>
                <span class="val w-medium"><?= $dot($mandat['nouveau_pere'] ?? '', 18) ?></span>
            </div>

            <div class="ligne">
                <span class="lbl">Et de :</span>
                <span class="val w-medium"><?= $dot($mandat['nouveau_mere'] ?? '', 18) ?></span>
                <span class="lbl">Nationalité</span>
                <span class="val w-medium"><?= $dot($nationalite, 16) ?></span>
            </div>

            <div class="ligne">
                <span class="lbl">Profession :</span>
                <span class="val w-medium"><?= $dot($profession, 18) ?></span>
                <span class="lbl">Domicile</span>
                <span class="val w-medium"><?= $dot($domicile, 18) ?></span>
            </div>

            <div class="ligne">
                <span class="lbl">Situation de famille</span>
                <span class="val w-large"><?= $dot($sitFamille, 35) ?></span>
            </div>

            <div class="ligne">
                <span class="lbl">Condamnation</span>
                <span class="val w-large"><?= $dot($condamn, 38) ?></span>
            </div>

            <div class="ligne">
                <span class="lbl">Service militaire :</span>
                <span class="val w-large"><?= $dot($serviceMil, 32) ?></span>
            </div>
        </div>
    </div>

    <!-- Flagrant délit / motif -->
    <div class="flagrant-line">
        <span class="lbl">Arrêté<?= $sexeF ? 'e' : '' ?></span>
        <?php if ($isFlagrant): ?> en Flagrant Délit de <?php else: ?> pour <?php endif; ?>
        <span class="val"><?= $dot($infraction ?: $motif, 50) ?></span>
    </div>

    <!-- Injonction -->
    <div class="injonction">
        <?php if ($isDepot): ?>
            <p>Enjoignons au Surveillant Chef de ladite Prison de le recevoir et de le tenir en
               Dépôt jusqu'à ce qu'il en soit autrement ordonné.</p>
            <p>Requérons tout dépositaire de la Force Publique auquel le présent Mandat
               sera Exhibé de prêter main forte pour son exécution en cas de besoin.</p>
        <?php elseif ($mandat['type_mandat'] === 'arret'): ?>
            <p>Ordonnons à tout Officier de Police Judiciaire et à tout agent de la force publique de
               procéder à l'arrestation immédiate de la personne sus-désignée et de la conduire devant nous.</p>
            <p>Requérons tout dépositaire de la Force Publique auquel le présent Mandat
               sera Exhibé de prêter main forte pour son exécution en cas de besoin.</p>
        <?php elseif ($mandat['type_mandat'] === 'amener'): ?>
            <p>Mandons et ordonnons à tout agent de la Force Publique d'amener devant nous la personne
               sus-désignée pour être entendue.</p>
        <?php elseif ($mandat['type_mandat'] === 'comparution'): ?>
            <p>Notifions à la personne sus-désignée l'ordre de comparaître devant ce tribunal
               aux jour et heure qui lui seront indiqués.</p>
        <?php elseif ($mandat['type_mandat'] === 'perquisition'): ?>
            <p>Mandons et ordonnons à tout Officier de Police Judiciaire de procéder aux perquisitions
               et saisies nécessaires au lieu indiqué, dans le respect des formes prescrites par la loi.</p>
        <?php elseif ($mandat['type_mandat'] === 'liberation'): ?>
            <p>Ordonnons au Surveillant Chef de la Maison d'Arrêt de procéder à la mise en liberté
               immédiate de la personne sus-désignée, sauf si elle est détenue pour autre cause.</p>
        <?php endif; ?>
        <p style="text-indent:18mm">
            En foi de quoi le présent Mandat a été signé par nous Procureur de la
            République et scellé de notre sceau.
        </p>
    </div>

    <!-- Signature -->
    <div class="signature">
        <div class="lieu-date">
            <strong>Fait au Parquet le</strong>
            <span class="val">
                <?= !empty($mandat['date_emission']) ? date('d/m/Y', strtotime($mandat['date_emission'])) : str_repeat('.', 22) ?>
            </span>
        </div>
        <div class="titre-sig">
            <?= htmlspecialchars($mandat['emetteur_role'] ?? 'LE PROCUREUR DE LA REPUBLIQUE') ?>
        </div>
        <?php if (!empty($mandat['emetteur_nom'])): ?>
            <div class="nom-sig"><?= htmlspecialchars($mandat['emetteur_nom']) ?></div>
        <?php endif; ?>
    </div>

</div>

<!-- ═══════════════ PIED ═══════════════ -->
<div class="pied">
    <?= htmlspecialchars($piedPage) ?> —
    <?= htmlspecialchars($numeroRP) ?>
    <?= $numeroRG ? ' — Dossier ' . htmlspecialchars($numeroRG) : '' ?>
    — Généré le <?= date('d/m/Y à H:i') ?>
</div>

<!-- ═══════════════ QR JS ═══════════════ -->
<?php if ($qrActif): ?>
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
function initQR() {
    var c = document.getElementById('qrCanvas');
    if (!c) return;
    QRCode.toCanvas(c, <?= json_encode($qrUrl) ?>, {
        width: 60, margin: 1,
        color: { dark: '#000000', light: '#ffffff' }
    }, function(err){ if(err) console.error('QR error:', err); });
}
</script>
<?php else: ?>
<script>function initQR(){}</script>
<?php endif; ?>

</body>
</html>
