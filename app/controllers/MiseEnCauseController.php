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
 *
 * Traçabilité (v2.11) :
 *   Toutes les actions CRUD sont enregistrées dans `mec_historique`.
 *   Le paramètre POST `_redirect_to` (ex: "dossier:4") permet de renvoyer
 *   l'utilisateur vers le dossier d'origine plutôt que le PV.
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

        $mecId = (int)$this->db->lastInsertId();

        // Infractions associées à ce mis en cause (unité d'enquête)
        $this->saveMECInfractions($mecId, $_POST['infractions_mec'] ?? [], 'unite');
        // Qualification substitut sur ce MEC
        if (Auth::hasRole(['substitut_procureur','procureur','admin'])) {
            $this->saveMECInfractions($mecId, $_POST['infractions_mec_substitut'] ?? [], 'substitut');
        }

        // ── Traçabilité ──
        $dossierId = $this->getDossierIdFromRedirect($_POST['_redirect_to'] ?? '');
        $this->traceMEC($mecId, $pvId, $dossierId, 'create', null, null);

        $this->flash('success', 'Mise en cause enregistrée.');
        $this->redirectAfterMEC($pvId, $_POST['_redirect_to'] ?? '', '#mises-en-cause');
    }

    // ─── GET /pv/mise-en-cause/edit/{id} ──────────────────────────────────
    public function edit(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'procureur', 'substitut_procureur', 'president']);
        $mec  = $this->getMEC((int)$id);
        if (!$mec) { $this->redirect('/pv'); }
        $user           = Auth::currentUser();
        $flash          = $this->getFlash();
        $infractions    = $this->db->query("SELECT id, code, libelle, categorie FROM infractions ORDER BY libelle")->fetchAll();
        $mecInfractions = $this->getMECInfractions((int)$id);
        // Passer le contexte de retour (dossier_id éventuel) à la vue
        $redirectTo     = $_GET['redirect_to'] ?? '';
        $this->view('mises_en_cause/edit', compact('mec', 'flash', 'user', 'infractions', 'mecInfractions', 'redirectTo'));
    }

    // ─── POST /pv/mise-en-cause/update/{id} ───────────────────────────────
    public function update(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'procureur', 'substitut_procureur', 'president']);
        CSRF::check();

        $mec = $this->getMEC((int)$id);
        if (!$mec) { $this->redirect('/pv'); }

        // Snapshot avant modification (pour l'audit)
        $dataAvant = json_encode($mec);

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
                est_connu_archives=:archives, nb_affaires_precedentes=:nbprev, notes_antecedents=:notes,
                updated_at=NOW()
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

        // Mise à jour des infractions
        $isSubstitut = Auth::hasRole(['substitut_procureur','procureur','admin']);
        $this->saveMECInfractions((int)$id, $_POST['infractions_mec'] ?? [], 'unite', true);
        if ($isSubstitut) {
            $this->saveMECInfractions((int)$id, $_POST['infractions_mec_substitut'] ?? [], 'substitut', true);
        }

        // ── Traçabilité ──
        $mecApres  = $this->getMEC((int)$id);
        $dataApres = json_encode($mecApres);
        $dossierId = $this->getDossierIdFromRedirect($_POST['_redirect_to'] ?? '');
        $this->traceMEC((int)$id, (int)$mec['pv_id'], $dossierId, 'update', $dataAvant, $dataApres);

        $this->flash('success', 'Mise en cause mise à jour.');
        $this->redirectAfterMEC((int)$mec['pv_id'], $_POST['_redirect_to'] ?? '', '#mises-en-cause');
    }

    // ─── POST /pv/mise-en-cause/delete/{id} ───────────────────────────────
    public function delete(string $id): void
    {
        Auth::requireLogin();
        CSRF::check();

        $user  = Auth::currentUser();
        $role  = $user['role_code'] ?? '';
        $allowed = in_array($role, ['admin', 'greffier', 'procureur', 'substitut_procureur', 'president']);
        if (!$allowed) {
            $this->flash('error', 'Vous n\'avez pas les droits pour supprimer une mise en cause.');
            $this->redirect('/pv');
            return;
        }

        $mec = $this->getMEC((int)$id);
        if (!$mec) { $this->redirect('/pv'); }

        $pvId      = (int)$mec['pv_id'];
        $redirectTo= $_POST['_redirect_to'] ?? '';
        $dossierId = $this->getDossierIdFromRedirect($redirectTo);

        // ── Traçabilité avant suppression ──
        $this->traceMEC((int)$id, $pvId, $dossierId, 'delete', json_encode($mec), null);

        $this->db->prepare("DELETE FROM mises_en_cause WHERE id=?")->execute([(int)$id]);

        $this->flash('success', 'Mise en cause supprimée.');
        $this->redirectAfterMEC($pvId, $redirectTo, '#mises-en-cause');
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

        $dataAvant = json_encode($mec);

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

        // ── Traçabilité ──
        $mecApres  = $this->getMEC((int)$id);
        $dossierId = $this->getDossierIdFromRedirect($_POST['_redirect_to'] ?? '');
        $this->traceMEC((int)$id, (int)$mec['pv_id'], $dossierId, 'update', $dataAvant, json_encode($mecApres));

        $label = ['poursuivi' => 'Poursuivi', 'non_poursuivi' => 'Non poursuivi', 'en_attente' => 'En attente'][$decision];
        $this->flash('success', "Décision enregistrée : {$label}.");
        $this->redirectAfterMEC((int)$mec['pv_id'], $_POST['_redirect_to'] ?? '', '#mises-en-cause');
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
            $this->redirectAfterMEC($pvId, $_POST['_redirect_to'] ?? '', '#mises-en-cause');
        }

        $source = $this->getMEC($mecId);
        if (!$source) {
            $this->flash('error', 'Mise en cause source introuvable.');
            $this->redirectAfterMEC($pvId, $_POST['_redirect_to'] ?? '', '#mises-en-cause');
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

        $newMecId  = (int)$this->db->lastInsertId();
        $dossierId = $this->getDossierIdFromRedirect($_POST['_redirect_to'] ?? '');

        // ── Traçabilité ──
        $this->traceMEC($newMecId, $pvId, $dossierId, 'reconduire',
            json_encode(['source_mec_id' => $mecId, 'source_pv_id' => $source['pv_id']]),
            null);

        $this->flash('success', "Mise en cause {$source['nom']} {$source['prenom']} reconduite (affaire #{$nbPrev}).");
        $this->redirectAfterMEC($pvId, $_POST['_redirect_to'] ?? '', '#mises-en-cause');
    }

    // ─── GET /api/mises-en-cause/search ───────────────────────────────────
    public function apiSearch(): void
    {
        Auth::requireLogin();
        $q      = trim($_GET['q'] ?? '');
        $pvId   = (int)($_GET['exclude_pv'] ?? 0); // exclure les MEC déjà liés au PV courant
        $limit  = min(50, max(1, (int)($_GET['limit'] ?? 30)));

        if ($q === '') {
            // Retourne les 50 dernières mises en cause (pour l'affichage au focus)
            $sql  = "SELECT m.*, p.numero_rg, p.date_reception
                     FROM mises_en_cause m
                     JOIN pv p ON p.id = m.pv_id";
            $sql .= $pvId ? " WHERE m.pv_id != :excl" : "";
            $sql .= " ORDER BY m.created_at DESC LIMIT {$limit}";
            $stmt = $this->db->prepare($sql);
            if ($pvId) { $stmt->execute([':excl' => $pvId]); }
            else       { $stmt->execute(); }
            $this->json(['success' => true, 'data' => $stmt->fetchAll()]);
            return;
        }

        $stmt = $this->db->prepare(
            "SELECT m.*, p.numero_rg, p.date_reception
             FROM mises_en_cause m
             JOIN pv p ON p.id = m.pv_id
             WHERE (m.nom LIKE :q OR m.prenom LIKE :q OR m.alias LIKE :q)"
            . ($pvId ? " AND m.pv_id != :excl" : "")
            . " ORDER BY m.nom, m.prenom
             LIMIT {$limit}"
        );
        $params = [':q' => "%{$q}%"];
        if ($pvId) { $params[':excl'] = $pvId; }
        $stmt->execute($params);
        $this->json(['success' => true, 'data' => $stmt->fetchAll()]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── Helpers traçabilité ──────────────────────────────────────────────
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Enregistre une entrée dans mec_historique.
     * Crée la table à la volée si elle n'existe pas encore (idempotent).
     */
    private function traceMEC(
        ?int $mecId,
        int  $pvId,
        ?int $dossierId,
        string $action,
        ?string $dataAvant,
        ?string $dataApres
    ): void {
        try {
            // Création de la table si absente (migration lazy)
            $this->db->exec(
                "CREATE TABLE IF NOT EXISTS `mec_historique` (
                    `id`             INT(11)      NOT NULL AUTO_INCREMENT,
                    `mec_id`         INT(11)      DEFAULT NULL,
                    `pv_id`          INT(11)      NOT NULL,
                    `dossier_id`     INT(11)      DEFAULT NULL,
                    `user_id`        INT(11)      DEFAULT NULL,
                    `action`         VARCHAR(20)  NOT NULL,
                    `statut_dossier` VARCHAR(50)  DEFAULT NULL,
                    `statut_pv`      VARCHAR(50)  DEFAULT NULL,
                    `nom_mec`        VARCHAR(150) DEFAULT NULL,
                    `prenom_mec`     VARCHAR(150) DEFAULT NULL,
                    `data_avant`     TEXT         DEFAULT NULL,
                    `data_apres`     TEXT         DEFAULT NULL,
                    `ip_address`     VARCHAR(45)  DEFAULT NULL,
                    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_mech_mec`     (`mec_id`),
                    KEY `idx_mech_pv`      (`pv_id`),
                    KEY `idx_mech_dossier` (`dossier_id`),
                    KEY `idx_mech_user`    (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );

            // Récupérer les statuts courants
            $statutPv      = null;
            $statutDossier = null;
            $nomMec        = null;
            $prenomMec     = null;

            $pvRow = $this->db->prepare("SELECT statut FROM pv WHERE id=?")->execute([$pvId])
                     ? null : null;
            try {
                $s = $this->db->prepare("SELECT statut FROM pv WHERE id=?");
                $s->execute([$pvId]);
                $r = $s->fetch();
                $statutPv = $r['statut'] ?? null;
            } catch (\Exception $e) {}

            if ($dossierId) {
                try {
                    $s = $this->db->prepare("SELECT statut FROM dossiers WHERE id=?");
                    $s->execute([$dossierId]);
                    $r = $s->fetch();
                    $statutDossier = $r['statut'] ?? null;
                } catch (\Exception $e) {}
            }

            // Snapshot nom/prénom (utile pour les suppressions où mecId sera null après)
            if ($mecId) {
                try {
                    $s = $this->db->prepare("SELECT nom, prenom FROM mises_en_cause WHERE id=?");
                    $s->execute([$mecId]);
                    $r = $s->fetch();
                    $nomMec    = $r['nom']    ?? null;
                    $prenomMec = $r['prenom'] ?? null;
                } catch (\Exception $e) {}
            }
            // Fallback : extraire depuis data_avant si suppression
            if (!$nomMec && $dataAvant) {
                $d = json_decode($dataAvant, true);
                $nomMec    = $d['nom']    ?? null;
                $prenomMec = $d['prenom'] ?? null;
            }

            $ip = $_SERVER['REMOTE_ADDR'] ?? null;

            $stmt = $this->db->prepare(
                "INSERT INTO mec_historique
                    (mec_id, pv_id, dossier_id, user_id, action,
                     statut_dossier, statut_pv, nom_mec, prenom_mec,
                     data_avant, data_apres, ip_address)
                 VALUES
                    (:mec, :pv, :dos, :usr, :act,
                     :sdos, :spv, :nom, :prenom,
                     :avant, :apres, :ip)"
            );
            $stmt->execute([
                ':mec'    => $mecId,
                ':pv'     => $pvId,
                ':dos'    => $dossierId,
                ':usr'    => Auth::userId(),
                ':act'    => $action,
                ':sdos'   => $statutDossier,
                ':spv'    => $statutPv,
                ':nom'    => $nomMec,
                ':prenom' => $prenomMec,
                ':avant'  => $dataAvant,
                ':apres'  => $dataApres,
                ':ip'     => $ip,
            ]);
        } catch (\Exception $e) {
            // Ne jamais bloquer l'opération principale pour un échec d'audit
            error_log('traceMEC error: ' . $e->getMessage());
        }
    }

    /**
     * Extrait le dossier_id depuis un token "_redirect_to" de la forme "dossier:4".
     */
    private function getDossierIdFromRedirect(string $token): ?int
    {
        if (str_starts_with($token, 'dossier:')) {
            $id = (int)substr($token, 8);
            return $id > 0 ? $id : null;
        }
        return null;
    }

    /**
     * Redirige après une action MEC :
     * - Si _redirect_to = "dossier:{id}" → /dossiers/show/{id}{anchor}
     * - Sinon → /pv/show/{pvId}{anchor}
     */
    private function redirectAfterMEC(int $pvId, string $redirectTo, string $anchor = ''): void
    {
        $dossierId = $this->getDossierIdFromRedirect($redirectTo);
        if ($dossierId) {
            $this->redirect('/dossiers/show/' . $dossierId . $anchor);
        } else {
            $this->redirect('/pv/show/' . $pvId . $anchor);
        }
    }

    // ─── Helpers infractions MEC ──────────────────────────────────────────
    private function saveMECInfractions(int $mecId, array $infIds, string $type, bool $replace = false): void
    {
        if ($replace) {
            try {
                $this->db->prepare("DELETE FROM mec_infractions WHERE mec_id=? AND type=?")->execute([$mecId, $type]);
            } catch (\Exception $e) {}
        }
        if (empty($infIds)) return;
        try {
            $ins = $this->db->prepare(
                "INSERT IGNORE INTO mec_infractions (mec_id, infraction_id, type) VALUES (?,?,?)"
            );
            foreach ($infIds as $iid) {
                if ((int)$iid > 0) $ins->execute([$mecId, (int)$iid, $type]);
            }
        } catch (\Exception $e) {}
    }

    private function getMECInfractions(int $mecId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT mi.type, i.id, i.code, i.libelle, i.categorie
                 FROM mec_infractions mi JOIN infractions i ON i.id = mi.infraction_id
                 WHERE mi.mec_id = ? ORDER BY mi.type, i.libelle"
            );
            $stmt->execute([$mecId]);
            $rows   = $stmt->fetchAll();
            $result = ['unite' => [], 'substitut' => []];
            foreach ($rows as $r) { $result[$r['type']][] = $r; }
            return $result;
        } catch (\Exception $e) {
            return ['unite' => [], 'substitut' => []];
        }
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
