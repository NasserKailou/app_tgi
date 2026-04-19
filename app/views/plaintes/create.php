<?php $pageTitle = 'Nouvelle plainte'; ?>
<div class="mb-4 mt-2">
  <nav aria-label="breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/plaintes">Plaintes</a></li>
    <li class="breadcrumb-item active">Nouvelle plainte</li>
  </ol></nav>
  <h4 class="fw-bold py-2 px-3 rounded text-white" style="background-color:#c0392b;">
    <i class="bi bi-megaphone-fill me-2"></i>Enregistrer une nouvelle plainte
  </h4>
</div>

<?php if (!empty($flash['error'])): foreach ($flash['error'] as $msg): ?>
<div class="alert alert-danger alert-dismissible fade show">
  <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($msg) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endforeach; endif; ?>

<form method="POST" action="<?= BASE_URL ?>/plaintes/store" enctype="multipart/form-data" novalidate id="formPlainte">
  <?= CSRF::field() ?>
  <div class="row g-4">
    <!-- Colonne gauche -->
    <div class="col-lg-8">

      <!-- Identification -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-danger text-white fw-semibold">
          <i class="bi bi-hash me-2"></i>Identification de la plainte
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-5">
              <label class="form-label fw-semibold">N° Plainte</label>
              <input type="text" name="numero_plainte" class="form-control font-monospace text-uppercase"
                     placeholder="Auto-généré si vide"
                     value="<?= htmlspecialchars($_POST['numero_plainte'] ?? '') ?>">
              <div class="form-text">Laissez vide pour auto-génération</div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Date de la plainte <span class="text-danger">*</span></label>
              <input type="date" name="date_plainte" class="form-control" required
                     value="<?= htmlspecialchars($_POST['date_plainte'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Date de réception <span class="text-danger">*</span></label>
              <input type="date" name="date_reception" class="form-control" required
                     value="<?= htmlspecialchars($_POST['date_reception'] ?? date('Y-m-d')) ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- Informations du plaignant -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-danger text-white fw-semibold">
          <i class="bi bi-person-fill me-2"></i>Informations du plaignant
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-5">
              <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
              <input type="text" name="plaignant_nom" class="form-control text-uppercase" required
                     placeholder="NOM DE FAMILLE"
                     value="<?= htmlspecialchars($_POST['plaignant_nom'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Prénom</label>
              <input type="text" name="plaignant_prenom" class="form-control"
                     value="<?= htmlspecialchars($_POST['plaignant_prenom'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Qualité</label>
              <select name="plaignant_qualite" class="form-select">
                <option value="personne_physique" <?= ($_POST['plaignant_qualite'] ?? 'personne_physique') === 'personne_physique' ? 'selected' : '' ?>>Personne physique</option>
                <option value="personne_morale"   <?= ($_POST['plaignant_qualite'] ?? '') === 'personne_morale' ? 'selected' : '' ?>>Personne morale</option>
                <option value="administration"    <?= ($_POST['plaignant_qualite'] ?? '') === 'administration' ? 'selected' : '' ?>>Administration</option>
                <option value="autre"             <?= ($_POST['plaignant_qualite'] ?? '') === 'autre' ? 'selected' : '' ?>>Autre</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Téléphone</label>
              <input type="tel" name="plaignant_telephone" class="form-control"
                     value="<?= htmlspecialchars($_POST['plaignant_telephone'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Email</label>
              <input type="email" name="plaignant_email" class="form-control"
                     value="<?= htmlspecialchars($_POST['plaignant_email'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Adresse</label>
              <input type="text" name="plaignant_adresse" class="form-control"
                     value="<?= htmlspecialchars($_POST['plaignant_adresse'] ?? '') ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- Mises en cause (liste dynamique) -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-danger text-white fw-semibold d-flex justify-content-between align-items-center">
          <span><i class="bi bi-people-fill me-2"></i>Personnes mises en cause</span>
          <button type="button" class="btn btn-sm btn-light text-danger fw-bold" onclick="ajouterMEC()">
            <i class="bi bi-plus-circle-fill me-1"></i>Ajouter
          </button>
        </div>
        <div class="card-body p-0">
          <div id="mecContainer">
            <!-- Premier mis en cause -->
            <div class="mec-block border-bottom p-3" id="mecBlock0">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-semibold mb-0 text-warning"><i class="bi bi-person-dash me-2"></i>Mis en cause n°1</h6>
              </div>
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Nom</label>
                  <input type="text" name="mec[0][nom]" class="form-control text-uppercase"
                         placeholder="NOM" value="<?= htmlspecialchars($_POST['mis_en_cause_nom'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Prénom</label>
                  <input type="text" name="mec[0][prenom]" class="form-control" placeholder="Prénom">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Alias / Surnom</label>
                  <input type="text" name="mec[0][alias]" class="form-control" placeholder="dit...">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Adresse connue</label>
                  <input type="text" name="mec[0][adresse]" class="form-control"
                         value="<?= htmlspecialchars($_POST['mis_en_cause_adresse'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Téléphone</label>
                  <input type="text" name="mec[0][telephone]" class="form-control">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Statut</label>
                  <select name="mec[0][statut]" class="form-select">
                    <option value="mise_en_cause">Mis en cause</option>
                    <option value="prevenu">Prévenu</option>
                    <option value="temoin">Témoin</option>
                    <option value="autre">Autre</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="p-3 text-center border-top bg-light">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="ajouterMEC()">
              <i class="bi bi-plus-circle me-1"></i>Ajouter un autre mis en cause
            </button>
          </div>
        </div>
      </div>

      <!-- Faits et nature -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-danger text-white fw-semibold">
          <i class="bi bi-journal-text me-2"></i>Nature et faits
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Nature de la plainte <span class="text-danger">*</span></label>
              <input type="text" name="nature_plainte" class="form-control" required
                     placeholder="Ex: Vol avec violence, Escroquerie, Coups et blessures..."
                     value="<?= htmlspecialchars($_POST['nature_plainte'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Lieu des faits</label>
              <input type="text" name="lieu_faits" class="form-control"
                     value="<?= htmlspecialchars($_POST['lieu_faits'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Date des faits</label>
              <input type="date" name="date_faits" class="form-control"
                     value="<?= htmlspecialchars($_POST['date_faits'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Description des faits</label>
              <textarea name="description_faits" class="form-control" rows="4"
                        placeholder="Décrire les faits en détail..."><?= htmlspecialchars($_POST['description_faits'] ?? '') ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Pièces jointes <small class="text-muted">PDF, JPG, PNG, DOC ≤ 10 Mo</small></label>
              <input type="file" name="piece_jointe" class="form-control"
                     accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
            </div>
          </div>
        </div>
      </div>

    </div><!-- /.col-lg-8 -->

    <!-- Colonne droite -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-danger text-white fw-semibold">
          <i class="bi bi-person-badge me-2"></i>Affectation
        </div>
        <div class="card-body">
          <label class="form-label fw-semibold">Substitut chargé</label>
          <select name="substitut_id" class="form-select">
            <option value="">— Non affecté —</option>
            <?php foreach ($substituts as $s): ?>
            <option value="<?= $s['id'] ?>" <?= ($_POST['substitut_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($s['prenom'] . ' ' . $s['nom']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <div class="mt-3">
            <label class="form-label fw-semibold">Observations internes</label>
            <textarea name="observations" class="form-control" rows="3"
                      placeholder="Notes internes..."><?= htmlspecialchars($_POST['observations'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm bg-light">
        <div class="card-body py-4">
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-danger px-5">
              <i class="bi bi-check-circle me-2"></i>Enregistrer la plainte
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="imprimerConvocation()">
              <i class="bi bi-printer me-2"></i>Imprimer la convocation
            </button>
            <a href="<?= BASE_URL ?>/plaintes" class="btn btn-link text-muted text-center">
              <i class="bi bi-arrow-left me-1"></i>Annuler
            </a>
          </div>
          <div class="alert alert-info small mt-3 mb-0">
            <i class="bi bi-info-circle me-1"></i>
            Le bouton "Imprimer" génère une convocation provisoire avec les données actuellement saisies (sans enregistrement).
          </div>
        </div>
      </div>
    </div>

  </div><!-- /.row -->
</form>

<!-- Zone d'impression convocation (cachée) -->
<div id="convocationPrintZone" style="display:none;"></div>

<script>
var mecCount = 1;

function ajouterMEC() {
    var idx = mecCount;
    mecCount++;
    var html = '<div class="mec-block border-bottom p-3" id="mecBlock' + idx + '">' +
        '<div class="d-flex justify-content-between align-items-center mb-2">' +
        '<h6 class="fw-semibold mb-0 text-warning"><i class="bi bi-person-dash me-2"></i>Mis en cause n°' + mecCount + '</h6>' +
        '<button type="button" class="btn btn-sm btn-outline-danger py-0" onclick="supprimerMEC(' + idx + ')">' +
        '<i class="bi bi-x-circle me-1"></i>Supprimer</button></div>' +
        '<div class="row g-3">' +
        '<div class="col-md-4"><label class="form-label">Nom</label>' +
        '<input type="text" name="mec[' + idx + '][nom]" class="form-control text-uppercase" placeholder="NOM"></div>' +
        '<div class="col-md-4"><label class="form-label">Prénom</label>' +
        '<input type="text" name="mec[' + idx + '][prenom]" class="form-control" placeholder="Prénom"></div>' +
        '<div class="col-md-4"><label class="form-label">Alias / Surnom</label>' +
        '<input type="text" name="mec[' + idx + '][alias]" class="form-control" placeholder="dit..."></div>' +
        '<div class="col-md-6"><label class="form-label">Adresse connue</label>' +
        '<input type="text" name="mec[' + idx + '][adresse]" class="form-control"></div>' +
        '<div class="col-md-3"><label class="form-label">Téléphone</label>' +
        '<input type="text" name="mec[' + idx + '][telephone]" class="form-control"></div>' +
        '<div class="col-md-3"><label class="form-label">Statut</label>' +
        '<select name="mec[' + idx + '][statut]" class="form-select">' +
        '<option value="mise_en_cause">Mis en cause</option>' +
        '<option value="prevenu">Prévenu</option>' +
        '<option value="temoin">Témoin</option>' +
        '<option value="autre">Autre</option>' +
        '</select></div>' +
        '</div></div>';
    document.getElementById('mecContainer').insertAdjacentHTML('beforeend', html);
}

function supprimerMEC(idx) {
    var block = document.getElementById('mecBlock' + idx);
    if (block) block.remove();
}

function imprimerConvocation() {
    // Récupérer les données du formulaire
    var form = document.getElementById('formPlainte');
    var plaignantNom    = form.querySelector('[name="plaignant_nom"]').value || '—';
    var plaignantPrenom = form.querySelector('[name="plaignant_prenom"]').value || '';
    var plaignantAdr    = form.querySelector('[name="plaignant_adresse"]').value || '—';
    var plaignantTel    = form.querySelector('[name="plaignant_telephone"]').value || '—';
    var naturePlainte   = form.querySelector('[name="nature_plainte"]').value || '—';
    var dateReception   = form.querySelector('[name="date_reception"]').value || '<?= date("Y-m-d") ?>';
    var dateFmt = dateReception ? new Date(dateReception).toLocaleDateString('fr-FR') : '—';
    var today = new Date().toLocaleDateString('fr-FR');

    // Construire le HTML de la convocation
    var html = '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">' +
        '<title>Convocation</title>' +
        '<style>body{font-family:serif;font-size:12pt;margin:40px;}' +
        '.entete{text-align:center;border-bottom:2px solid #000;margin-bottom:20px;padding-bottom:10px;}' +
        '.titre{font-size:16pt;font-weight:bold;text-align:center;margin:20px 0;}' +
        '.corps{margin:20px 0;line-height:1.8;}' +
        '.signature{margin-top:60px;text-align:right;}' +
        '.underline{border-bottom:1px solid #000;display:inline-block;min-width:200px;}' +
        '</style></head><body>' +
        '<div class="entete">' +
        '<p><strong>REPUBLIQUE DU NIGER<br>Fraternité — Travail — Progrès</strong></p>' +
        '<p>MINISTERE DE LA JUSTICE<br>Tribunal de Grande Instance Hors Classe de Niamey</p>' +
        '<p>Le Parquet</p>' +
        '</div>' +
        '<div class="titre">CONVOCATION</div>' +
        '<div class="corps">' +
        '<p>Monsieur / Madame <strong>' + plaignantNom + ' ' + plaignantPrenom + '</strong><br>' +
        'Demeurant à : ' + plaignantAdr + '<br>' +
        'Téléphone : ' + plaignantTel + '</p>' +
        '<p>Vous êtes prié(e) de vous présenter au <strong>Parquet du Tribunal de Grande Instance ' +
        'Hors Classe de Niamey</strong>, sis au Palais de Justice de Niamey,</p>' +
        '<p>Le : <strong class="underline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong> ' +
        'à <strong class="underline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong> heures</p>' +
        '<p>Objet : <strong>Suite à votre plainte du ' + dateFmt + ' concernant : ' + naturePlainte + '</strong></p>' +
        '<p>Prière de vous munir de votre pièce d\'identité et du présent document.</p>' +
        '<p><em>Toute absence non justifiée pourra entraîner l\'application des dispositions légales en vigueur.</em></p>' +
        '</div>' +
        '<div class="signature">' +
        '<p>Niamey, le ' + today + '</p>' +
        '<p>Le Substitut du Procureur de la République</p>' +
        '<br><br>' +
        '<p>____________________________</p>' +
        '</div>' +
        '</body></html>';

    var win = window.open('', '_blank', 'width=800,height=600');
    win.document.write(html);
    win.document.close();
    win.focus();
    win.print();
}
</script>
