<?php
/**
 * MiseEnCauseController — Gestion des mises en cause liées aux PV
 *
 * Routes :
 *   POST /pv/mise-en-cause/store/{pvId}          → store()
 *   GET  /pv/mise-en-cause/edit/{id}             → edit()
 *   POST /pv/mise-en-cause/update/{id}           → update()
 *   POST /pv/mise-en-cause/delete/{id}           → delete()
 *   POST /pv/mise-en-cause/decision/{id}         → decision()
 *   GET  /api/mises-en-cause/search              → apiSearch()
 *   POST /pv/mise-en-cause/reconduire/{pvId}     → reconduire()
 */
class MiseEnCauseController extends Controller
{
    // ─── POST /pv/mise-en-cause/store/{pvId} ──────────────────────────────
    public function store(string $pvId): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'procureur', 'substitut_procureur', 'president']);
        CSRF::check();

        $pvId = (int)$pvId;
        $pv   = $this->getPV($pvId);
        if (!$pv) {
            $this->flash('error', 'PV introuvable.');
            $this->redirect('/pv');
        }

        // Gestion photo
        $photoPath = null;
        if (!empty($_FILES['photo']['name'])) {
            $photoPath = $this->handlePhotoUpload($_FILES['photo']);
        }

        $stmt = $this->db->prepare(
            "INSERT INTO mises_en_cause
                (pv_id, nom, prenom, alias, nom_mere, date_naissance, lieu_naissance,
                 nationalite, sexe, profession, adresse, telephone, statut, statut_autre_detail,
                 photo, personne_contacter_nom, personne_contacter_tel, personne_contacter_lien,
                 est_connu_archives, nb_affaires_precedentes, notes_antecedents, created_by)
             VALUES
                (:pvid, :nom, :prenom, :alias, :mere, :dn, :ln,
                 :nat, :sexe, :prof, :adr, :tel, :statut, :sad,
                 :photo, :pcnom, :pctel, :pclien,
                 :archives, :nbprev, :notes, :by)"
        );
        $stmt->execute([
            ':pvid'    => $pvId,
            ':nom'     => strtoupper(trim($this->sanitize($_POST['nom'] ?? ''))),
            ':prenom'  => $this->sanitize($_POST['prenom'] ?? ''),
            ':alias'   => $this->sanitize($_POST['alias'] ?? '') ?: null,
            ':mere'    => $this->sanitize($_POST['nom_mere'] ?? '') ?: null,
            ':dn'      => $_POST['date_naissance'] ?: null,
            ':ln'      => $this->sanitize($_POST['lieu_naissance'] ?? '') ?: null,
            ':nat'     => $this->sanitize($_POST['nationalite'] ?? 'Nigérienne'),
            ':sexe'    => $_POST['sexe'] ?? 'M',
            ':prof'    => $this->sanitize($_POST['profession'] ?? '') ?: null,
            ':adr'     => $this->sanitize($_POST['adresse'] ?? '') ?: null,
            ':tel'     => $this->sanitize($_POST['telephone'] ?? '') ?: null,
            ':statut'  => $_POST['statut'] ?? 'mise_en_cause',
            ':sad'     => $this->sanitize($_POST['statut_autre_detail'] ?? '') ?: null,
            ':photo'   => $photoPath,
            ':pcnom'   => $this->sanitize($_POST['personne_contacter_nom'] ?? '') ?: null,
            ':pctel'   => $this->sanitize($_POST['personne_contacter_tel'] ?? '') ?: null,
            ':pclien'  => $this->sanitize($_POST['personne_contacter_lien'] ?? '') ?: null,
            ':archives'=> isset($_POST['est_connu_archives']) ? 1 : 0,
            ':nbprev'  => (int)($_POST['nb_affaires_precedentes'] ?? 0),
            ':notes'   => $this->sanitize($_POST['notes_antecedents'] ?? '') ?: null,
            ':by'      => Auth::userId(),
        ]);

        $this->flash('success', 'Mise en cause enregistrée.');
        $this->redirect('/pv/show/' . $pvId . '#mises-en-cause');
    }

    // ─── GET /pv/mise-en-cause/edit/{id} ──────────────────────────────────
    public function edit(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'procureur', 'substitut_procureur', 'president']);
        $mec  = $this->getMEC((int)$id);
        if (!$mec) { $this->redirect('/pv'); }
        $user  = Auth::currentUser();
        $flash = $this->getFlash();
        $this->view('mises_en_cause/edit', compact('mec', 'flash', 'user'));
    }

    // ─── POST /pv/mise-en-cause/update/{id} ───────────────────────────────
    public function update(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'procureur', 'substitut_procureur', 'president']);
        CSRF::check();

        $mec = $this->getMEC((int)$id);
        if (!$mec) { $this->redirect('/pv'); }

        $photoPath = $mec['photo'];
        if (!empty($_FILES['photo']['name'])) {
            $photoPath = $this->handlePhotoUpload($_FILES['photo']);
        }

        $this->db->prepare(
            "UPDATE mises_en_cause SET
                nom=:nom, prenom=:prenom, alias=:alias, nom_mere=:mere,
                date_naissance=:dn, lieu_naissance=:ln, nationalite=:nat, sexe=:sexe,
                profession=:prof, adresse=:adr, telephone=:tel,
                statut=:statut, statut_autre_detail=:sad,
                photo=:photo,
                personne_contacter_nom=:pcnom, personne_contacter_tel=:pctel, personne_contacter_lien=:pclien,
                est_connu_archives=:archives, nb_affaires_precedentes=:nbprev, notes_antecedents=:notes
             WHERE id=:id"
        )->execute([
            ':nom'     => strtoupper(trim($this->sanitize($_POST['nom'] ?? ''))),
            ':prenom'  => $this->sanitize($_POST['prenom'] ?? ''),
            ':alias'   => $this->sanitize($_POST['alias'] ?? '') ?: null,
            ':mere'    => $this->sanitize($_POST['nom_mere'] ?? '') ?: null,
            ':dn'      => $_POST['date_naissance'] ?: null,
            ':ln'      => $this->sanitize($_POST['lieu_naissance'] ?? '') ?: null,
            ':nat'     => $this->sanitize($_POST['nationalite'] ?? 'Nigérienne'),
            ':sexe'    => $_POST['sexe'] ?? 'M',
            ':prof'    => $this->sanitize($_POST['profession'] ?? '') ?: null,
            ':adr'     => $this->sanitize($_POST['adresse'] ?? '') ?: null,
            ':tel'     => $this->sanitize($_POST['telephone'] ?? '') ?: null,
            ':statut'  => $_POST['statut'] ?? 'mise_en_cause',
            ':sad'     => $this->sanitize($_POST['statut_autre_detail'] ?? '') ?: null,
            ':photo'   => $photoPath,
            ':pcnom'   => $this->sanitize($_POST['personne_contacter_nom'] ?? '') ?: null,
            ':pctel'   => $this->sanitize($_POST['personne_contacter_tel'] ?? '') ?: null,
            ':pclien'  => $this->sanitize($_POST['personne_contacter_lien'] ?? '') ?: null,
            ':archives'=> isset($_POST['est_connu_archives']) ? 1 : 0,
            ':nbprev'  => (int)($_POST['nb_affaires_precedentes'] ?? 0),
            ':notes'   => $this->sanitize($_POST['notes_antecedents'] ?? '') ?: null,
            ':id'      => (int)$id,
        ]);

        $this->flash('success', 'Mise en cause mise à jour.');
        $this->redirect('/pv/show/' . $mec['pv_id'] . '#mises-en-cause');
    }

    // ─── POST /pv/mise-en-cause/delete/{id} ───────────────────────────────
    public function delete(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'procureur', 'president']);
        CSRF::check();

        $mec = $this->getMEC((int)$id);
        if (!$mec) { $this->redirect('/pv'); }

        $pvId = $mec['pv_id'];
        $this->db->prepare("DELETE FROM mises_en_cause WHERE id=?")->execute([(int)$id]);

        $this->flash('success', 'Mise en cause supprimée.');
        $this->redirect('/pv/show/' . $pvId . '#mises-en-cause');
    }

    // ─── POST /pv/mise-en-cause/decision/{id} ─────────────────────────────
    /**
     * Permet au substitut de décider de poursuivre ou non une mise en cause
     */
    public function decision(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'procureur', 'substitut_procureur', 'president']);
        CSRF::check();

        $mec = $this->getMEC((int)$id);
        if (!$mec) { $this->redirect('/pv'); }

        $decision = $_POST['decision_substitut'] ?? 'en_attente';
        if (!in_array($decision, ['poursuivi', 'non_poursuivi', 'en_attente'])) {
            $decision = 'en_attente';
        }

        $this->db->prepare(
            "UPDATE mises_en_cause SET
                decision_substitut=:dec,
                motif_non_poursuite=:motif,
                date_decision=:date
             WHERE id=:id"
        )->execute([
            ':dec'   => $decision,
            ':motif' => ($decision === 'non_poursuivi') ? $this->sanitize($_POST['motif_non_poursuite'] ?? '') : null,
            ':date'  => date('Y-m-d'),
            ':id'    => (int)$id,
        ]);

        $label = ['poursuivi' => 'Poursuivi', 'non_poursuivi' => 'Non poursuivi', 'en_attente' => 'En attente'][$decision];
        $this->flash('success', "Décision enregistrée : {$label}.");
        $this->redirect('/pv/show/' . $mec['pv_id'] . '#mises-en-cause');
    }

    // ─── POST /pv/mise-en-cause/reconduire/{pvId} ─────────────────────────
    /**
     * Reconduire une mise en cause existante (d'un autre PV) vers le PV courant
     */
    public function reconduire(string $pvId): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'procureur', 'substitut_procureur', 'president']);
        CSRF::check();

        $pvId    = (int)$pvId;
        $mecId   = (int)($_POST['mec_source_id'] ?? 0);

        if (!$mecId) {
            $this->flash('error', 'Veuillez sélectionner une mise en cause à reconduire.');
            $this->redirect('/pv/show/' . $pvId . '#mises-en-cause');
        }

        $source = $this->getMEC($mecId);
        if (!$source) {
            $this->flash('error', 'Mise en cause source introuvable.');
            $this->redirect('/pv/show/' . $pvId . '#mises-en-cause');
        }

        // Copier la mise en cause vers le nouveau PV
        $stmt = $this->db->prepare(
            "INSERT INTO mises_en_cause
                (pv_id, nom, prenom, alias, nom_mere, date_naissance, lieu_naissance,
                 nationalite, sexe, profession, adresse, telephone, statut, statut_autre_detail,
                 photo, personne_contacter_nom, personne_contacter_tel, personne_contacter_lien,
                 est_connu_archives, nb_affaires_precedentes, notes_antecedents, created_by)
             VALUES
                (:pvid, :nom, :prenom, :alias, :mere, :dn, :ln,
                 :nat, :sexe, :prof, :adr, :tel, :statut, :sad,
                 :photo, :pcnom, :pctel, :pclien,
                 1, :nbprev, :notes, :by)"
        );
        $nbPrev = (int)($source['nb_affaires_precedentes'] ?? 0) + 1;
        $stmt->execute([
            ':pvid'   => $pvId,
            ':nom'    => $source['nom'],
            ':prenom' => $source['prenom'],
            ':alias'  => $source['alias'],
            ':mere'   => $source['nom_mere'],
            ':dn'     => $source['date_naissance'],
            ':ln'     => $source['lieu_naissance'],
            ':nat'    => $source['nationalite'],
            ':sexe'   => $source['sexe'],
            ':prof'   => $source['profession'],
            ':adr'    => $source['adresse'],
            ':tel'    => $source['telephone'],
            ':statut' => $source['statut'],
            ':sad'    => $source['statut_autre_detail'],
            ':photo'  => $source['photo'],
            ':pcnom'  => $source['personne_contacter_nom'],
            ':pctel'  => $source['personne_contacter_tel'],
            ':pclien' => $source['personne_contacter_lien'],
            ':nbprev' => $nbPrev,
            ':notes'  => $source['notes_antecedents'],
            ':by'     => Auth::userId(),
        ]);

        $this->flash('success', "Mise en cause {$source['nom']} {$source['prenom']} reconduite (affaire #{$nbPrev}).");
        $this->redirect('/pv/show/' . $pvId . '#mises-en-cause');
    }

    // ─── GET /api/mises-en-cause/search ───────────────────────────────────
    public function apiSearch(): void
    {
        Auth::requireLogin();
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            $this->json(['success' => true, 'data' => []]);
            return;
        }
        $stmt = $this->db->prepare(
            "SELECT m.*, p.numero_rg, p.date_reception
             FROM mises_en_cause m
             JOIN pv p ON p.id = m.pv_id
             WHERE (m.nom LIKE :q OR m.prenom LIKE :q OR m.alias LIKE :q)
             ORDER BY m.nom, m.prenom
             LIMIT 30"
        );
        $stmt->execute([':q' => "%{$q}%"]);
        $this->json(['success' => true, 'data' => $stmt->fetchAll()]);
    }

    // ─── Helpers privés ───────────────────────────────────────────────────
    private function getMEC(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT m.*, p.numero_rg FROM mises_en_cause m
             JOIN pv p ON p.id = m.pv_id
             WHERE m.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    private function getPV(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM pv WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    private function handlePhotoUpload(array $file): ?string
    {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $maxSize      = 2 * 1024 * 1024; // 2 Mo

        if ($file['error'] !== UPLOAD_ERR_OK) return null;
        if (!in_array($file['type'], $allowedTypes)) return null;
        if ($file['size'] > $maxSize) return null;

        $uploadDir = ROOT_PATH . '/public/uploads/photos_mec/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'mec_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . strtolower($ext);
        $dest     = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'uploads/photos_mec/' . $filename;
        }
        return null;
    }
}
