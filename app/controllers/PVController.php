<?php
class PVController extends Controller {

    // ─── Visibilité : restreindre selon le rôle ───────────────────────────────
    private function addVisibilityFilter(array &$where, array &$params, string $alias = 'p'): void {
        $user = Auth::currentUser();
        $role = $user['role_code'] ?? '';
        // Admin et procureur voient tout
        if (in_array($role, ['admin','procureur'])) return;
        $uid = (int)($user['id'] ?? 0);
        if ($role === 'substitut_procureur') {
            // Substitut : voit seulement les PV qui lui sont affectés OU qu'il a créés
            $where[]            = "({$alias}.substitut_id = :vis_uid OR {$alias}.created_by = :vis_uid2)";
            $params['vis_uid']  = $uid;
            $params['vis_uid2'] = $uid;
        } elseif ($role === 'greffier') {
            // Greffier : voit TOUS les PV (pas de restriction)
            // NOTE : la distinction créateur/affectant est visible dans l'UI via les champs
            // created_by et substitut_id mais n'est pas un filtre de visibilité
            // — aucune clause WHERE ajoutée
            return;
        } else {
            // Autres rôles : seulement les PV qu'ils ont créés
            $where[]           = "{$alias}.created_by = :vis_uid";
            $params['vis_uid'] = $uid;
        }
    }

    public function index(): void {
        Auth::requireLogin();
        $flash = $this->getFlash();
        $user  = Auth::currentUser();

        $where  = [];
        $params = [];
        $search    = trim($_GET['q'] ?? '');
        $statut    = $_GET['statut'] ?? '';
        $type      = $_GET['type'] ?? '';
        $antiterro = $_GET['antiterro'] ?? '';
        $rp        = trim($_GET['rp'] ?? '');

        if ($search) {
            $where[]  = "(p.numero_rg LIKE :q OR p.numero_pv LIKE :q OR p.description_faits LIKE :q OR p.lois_applicables LIKE :q)";
            $params['q'] = "%{$search}%";
        }
        // Recherche par numéro RP : retourne tous les PV liés au même RP
        if ($rp) {
            $where[]    = "p.numero_rp = :rp";
            $params['rp'] = $rp;
        }
        if ($statut) {
            $where[] = "p.statut = :statut";
            $params['statut'] = $statut;
        }
        if ($type) {
            $where[] = "p.type_affaire = :type";
            $params['type'] = $type;
        }
        if ($antiterro === '1') {
            $where[] = "p.est_antiterroriste = 1";
        }

        // Filtre de visibilité par rôle
        $this->addVisibilityFilter($where, $params);

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM pv p $whereSQL");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sql = "SELECT p.*, ue.nom as unite_nom,
                       us.nom as substitut_nom, us.prenom as substitut_prenom,
                       r.nom as region_nom, dep.nom as dept_nom, c.nom as commune_nom
                FROM pv p
                LEFT JOIN unites_enquete ue ON p.unite_enquete_id = ue.id
                LEFT JOIN users us ON p.substitut_id = us.id
                LEFT JOIN regions r ON p.region_id = r.id
                LEFT JOIN departements dep ON p.departement_id = dep.id
                LEFT JOIN communes c ON p.commune_id = c.id
                $whereSQL ORDER BY p.created_at DESC LIMIT $perPage OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $pvList = $stmt->fetchAll();

        $totalPages = ceil($total / $perPage);
        $substituts = $this->db->query("SELECT u.* FROM users u JOIN roles r ON u.role_id=r.id WHERE r.code='substitut_procureur' AND u.actif=1")->fetchAll();

        $this->view('pv/index', compact('pvList','total','page','perPage','totalPages','search','statut','type','antiterro','rp','substituts','flash','user'));
    }

    public function create(): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','procureur','substitut_procureur','president']);
        $user        = Auth::currentUser();
        $unites      = $this->db->query("SELECT * FROM unites_enquete WHERE actif=1 ORDER BY nom")->fetchAll();
        $regions     = $this->db->query("SELECT * FROM regions ORDER BY nom")->fetchAll();
        $primos      = $this->db->query("SELECT * FROM primo_intervenants WHERE actif=1 ORDER BY nom")->fetchAll();
        $infractions = $this->db->query("SELECT id, code, libelle, categorie FROM infractions ORDER BY libelle")->fetchAll();
        $num         = new Numerotation($this->db);
        $suggestRG   = $num->genererRG();

        // Construire les données géographiques complètes pour la cascade JS
        $geoDataForJs = [];
        foreach ($regions as $r) {
            $depts = $this->db->prepare("SELECT id, nom FROM departements WHERE region_id=? ORDER BY nom");
            $depts->execute([$r['id']]);
            $deptsData = [];
            foreach ($depts->fetchAll(PDO::FETCH_ASSOC) as $d) {
                $comms = $this->db->prepare("SELECT id, nom FROM communes WHERE departement_id=? ORDER BY nom");
                $comms->execute([$d['id']]);
                $deptsData[] = [
                    'id'       => (int)$d['id'],
                    'nom'      => $d['nom'],
                    'communes' => array_map(function($c){ return ['id'=>(int)$c['id'],'nom'=>$c['nom']]; },
                                           $comms->fetchAll(PDO::FETCH_ASSOC))
                ];
            }
            $geoDataForJs[$r['id']] = ['id' => (int)$r['id'], 'nom' => $r['nom'], 'departements' => $deptsData];
        }
        // Fallback si la base ne contient pas encore les données géo
        if (empty($geoDataForJs)) {
            require_once ROOT_PATH . '/app/config/niger_geo.php';
            $fid = 100;
            foreach ($departements_par_region as $rNom => $deps) {
                $rid = $fid++;
                $dd = [];
                foreach ($deps as $dNom) {
                    $did = $fid++;
                    $comms = [];
                    foreach (($communes_par_departement[$dNom] ?? []) as $cNom) {
                        $comms[] = ['id' => $fid++, 'nom' => $cNom];
                    }
                    $dd[] = ['id'=>$did,'nom'=>$dNom,'communes'=>$comms];
                }
                $geoDataForJs[$rid] = ['id'=>$rid,'nom'=>$rNom,'departements'=>$dd];
            }
        }
        $geoJson = json_encode($geoDataForJs, JSON_UNESCAPED_UNICODE);

        $this->view('pv/create', compact('unites','regions','primos','infractions','suggestRG','user','geoJson'));
    }

    public function store(): void {
        Auth::requireLogin();
        CSRF::check();
        $num = new Numerotation($this->db);
        $numeroRG = $num->genererRG();

        // Vérification unicité du numéro RP (Registre du Parquet)
        $numeroRP = trim($this->sanitize($_POST['numero_rp'] ?? ''));
        if ($numeroRP) {
            $chkRP = $this->db->prepare("SELECT COUNT(*) FROM pv WHERE numero_rp = ?");
            $chkRP->execute([$numeroRP]);
            // On autorise plusieurs PV avec le même RP (multi-PV par affaire)
            // Mais on avertit s'il existe déjà
        }

        $stmt = $this->db->prepare(
            "INSERT INTO pv (numero_pv, numero_rg, numero_rp, numero_ordre, unite_enquete_id, date_pv, date_reception,
              type_affaire, infraction_id, est_antiterroriste, region_id, departement_id, commune_id,
              description_faits, statut, created_by)
             VALUES (:pv,:rg,:rp,:nordre,:ue,:dpv,:drec,:type,:infr,:anti,:reg,:dep,:com,:desc,'recu',:by)"
        );
        $stmt->execute([
            'pv'     => $this->sanitize($_POST['numero_pv'] ?? ''),
            'rg'     => $numeroRG,
            'rp'     => $numeroRP ?: null,
            'nordre' => $this->sanitize($_POST['numero_ordre'] ?? '') ?: null,
            'ue'     => !empty($_POST['unite_enquete_id']) ? (int)$_POST['unite_enquete_id'] : null,
            'dpv'    => $_POST['date_pv'],
            'drec'   => $_POST['date_reception'],
            'type'   => $_POST['type_affaire'],
            'infr'   => !empty($_POST['infraction_id']) ? (int)$_POST['infraction_id'] : null,
            'anti'   => isset($_POST['est_antiterroriste']) ? 1 : 0,
            'reg'    => !empty($_POST['region_id']) ? (int)$_POST['region_id'] : null,
            'dep'    => !empty($_POST['departement_id']) ? (int)$_POST['departement_id'] : null,
            'com'    => !empty($_POST['commune_id']) ? (int)$_POST['commune_id'] : null,
            'desc'   => $this->sanitize($_POST['description_faits'] ?? ''),
            'by'     => Auth::userId(),
        ]);
        $pvId = (int)$this->db->lastInsertId();

        // Infractions multiples (unité d'enquête) — cases à cocher
        if (!empty($_POST['infractions_unite']) && is_array($_POST['infractions_unite'])) {
            $insInfr = $this->db->prepare(
                "INSERT IGNORE INTO pv_infractions (pv_id, infraction_id, type) VALUES (?,?,'unite')"
            );
            foreach ($_POST['infractions_unite'] as $iid) {
                $insInfr->execute([$pvId, (int)$iid]);
            }
        }

        // Primo intervenants
        if (!empty($_POST['primo_intervenants']) && is_array($_POST['primo_intervenants'])) {
            $insPI = $this->db->prepare("INSERT IGNORE INTO pv_primo_intervenants (pv_id, primo_intervenant_id) VALUES (?,?)");
            foreach ($_POST['primo_intervenants'] as $piId) {
                $insPI->execute([$pvId, (int)$piId]);
            }
        }

        $this->flash('success', "PV enregistré avec le numéro {$numeroRG}" . ($numeroRP ? " / RP {$numeroRP}" : '') . ".");
        $this->redirect('/pv/show/' . $pvId);
    }

    public function show(string $id): void {
        Auth::requireLogin();
        $pv = $this->getPVDetail((int)$id);
        if (!$pv) { $this->redirect('/pv'); }

        // Vérification de visibilité
        if (!$this->canAccessPV($pv)) {
            $this->flash('error', 'Accès refusé à ce PV.');
            $this->redirect('/pv');
            return;
        }

        $flash = $this->getFlash();
        $user  = Auth::currentUser();
        $substituts  = $this->db->query("SELECT u.* FROM users u JOIN roles r ON u.role_id=r.id WHERE r.code='substitut_procureur' AND u.actif=1")->fetchAll();
        $cabinets    = $this->db->query("SELECT * FROM cabinets_instruction WHERE actif=1")->fetchAll();
        $infractions = $this->db->query("SELECT id, code, libelle, categorie FROM infractions ORDER BY libelle")->fetchAll();

        // Dossier(s) lié(s) — via dossier_pvs (multi-PV) ou pv_id direct
        $dossier = null;
        if ($pv['id']) {
            $dossierStmt = $this->db->prepare(
                "SELECT d.* FROM dossiers d
                 LEFT JOIN dossier_pvs dp ON dp.dossier_id = d.id
                 WHERE d.pv_id=? OR dp.pv_id=?
                 ORDER BY d.created_at DESC LIMIT 1"
            );
            $dossierStmt->execute([$pv['id'], $pv['id']]);
            $dossier = $dossierStmt->fetch() ?: null;
        }

        // PVs avec le même RP (affaire liée)
        $pvsMemeRP = [];
        if (!empty($pv['numero_rp'])) {
            $rpStmt = $this->db->prepare(
                "SELECT p.id, p.numero_rg, p.numero_pv, p.date_reception, p.statut
                 FROM pv p WHERE p.numero_rp = ? AND p.id != ?
                 ORDER BY p.date_reception ASC"
            );
            $rpStmt->execute([$pv['numero_rp'], $pv['id']]);
            $pvsMemeRP = $rpStmt->fetchAll();
        }

        // Mises en cause liées à ce PV
        $mecStmt = $this->db->prepare(
            "SELECT * FROM mises_en_cause WHERE pv_id=? ORDER BY nom, prenom"
        );
        $mecStmt->execute([(int)$id]);
        $misesEnCause = $mecStmt->fetchAll();

        // Pièces jointes du PV
              $docsStmt = $this->db->prepare(
            "SELECT d.id, d.pv_id, d.nom_original, d.nom_stockage, d.chemin_fichier,
                    d.mime_type, d.taille_octets AS taille, d.description, d.created_at,
                    u.nom AS uploader_nom, u.prenom AS uploader_prenom
             FROM documents d
             LEFT JOIN users u ON d.uploaded_by = u.id
             WHERE d.pv_id = ?
             ORDER BY d.created_at DESC"
        );
        $docsStmt->execute([(int)$id]);
        $pvDocuments = $docsStmt->fetchAll();


        $isSubstitut = Auth::hasRole(['admin','procureur','substitut_procureur']);

        // pvInfractions : alias pour la vue (qui utilise $pvInfractions['unite'] et ['substitut'])
        $pvInfractions = [
            'unite'    => array_values($pv['infractions_unite']    ?? []),
            'substitut'=> array_values($pv['infractions_substitut'] ?? []),
        ];

        // Fiche CRPC liée à ce PV (si mode_poursuite = CRPC)
        $crpcDossier  = null;
        if (($pv['mode_poursuite'] ?? '') === 'CRPC') {
            $crpcStmt = $this->db->prepare(
                "SELECT cd.*, cp.id AS has_personnes
                 FROM crpc_dossiers cd
                 LEFT JOIN crpc_personnes cp ON cp.crpc_id = cd.id
                 WHERE cd.pv_id = ?
                 GROUP BY cd.id
                 ORDER BY cd.created_at DESC LIMIT 1"
            );
            $crpcStmt->execute([(int)$id]);
            $crpcDossier = $crpcStmt->fetch() ?: null;
        }

        $this->view('pv/show', compact(
            'pv','flash','user','substituts','cabinets','infractions',
            'dossier','misesEnCause','pvsMemeRP','pvDocuments','isSubstitut','pvInfractions',
            'crpcDossier'
        ));
    }

    // Vérifier si l'utilisateur courant peut accéder à ce PV
    private function canAccessPV(array $pv): bool {
        $user = Auth::currentUser();
        $role = $user['role_code'] ?? '';
        if (in_array($role, ['admin','procureur'])) return true;
        $uid = (int)($user['id'] ?? 0);
        if ($role === 'substitut_procureur') {
            // Substitut : seulement les PV qui lui sont affectés ou qu'il a créés
            return (int)($pv['substitut_id'] ?? 0) === $uid || (int)($pv['created_by'] ?? 0) === $uid;
        }
        if ($role === 'greffier') {
            // Greffier : accès à tous les PV
            return true;
        }
        // Autres rôles : seulement ce qu'ils ont créé
        return (int)($pv['created_by'] ?? 0) === $uid;
    }

    public function edit(string $id): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','greffier','procureur']);
        $pv          = $this->getPVDetail((int)$id);
        if (!$pv) { $this->redirect('/pv'); }
        $user        = Auth::currentUser();
        $unites      = $this->db->query("SELECT * FROM unites_enquete WHERE actif=1 ORDER BY nom")->fetchAll();
        $regions     = $this->db->query("SELECT * FROM regions ORDER BY nom")->fetchAll();
        $primos      = $this->db->query("SELECT * FROM primo_intervenants WHERE actif=1 ORDER BY nom")->fetchAll();
        $infractions = $this->db->query("SELECT id, code, libelle, categorie FROM infractions ORDER BY libelle")->fetchAll();

        // Geo JSON pour cascade
        $geoDataForJs = [];
        foreach ($regions as $r) {
            $depts = $this->db->prepare("SELECT id, nom FROM departements WHERE region_id=? ORDER BY nom");
            $depts->execute([$r['id']]);
            $deptsData = [];
            foreach ($depts->fetchAll(PDO::FETCH_ASSOC) as $d2) {
                $comms = $this->db->prepare("SELECT id, nom FROM communes WHERE departement_id=? ORDER BY nom");
                $comms->execute([$d2['id']]);
                $deptsData[] = [
                    'id'       => (int)$d2['id'],
                    'nom'      => $d2['nom'],
                    'communes' => array_map(fn($c)=>['id'=>(int)$c['id'],'nom'=>$c['nom']],
                                           $comms->fetchAll(PDO::FETCH_ASSOC))
                ];
            }
            $geoDataForJs[$r['id']] = ['id'=>(int)$r['id'],'nom'=>$r['nom'],'departements'=>$deptsData];
        }
        $geoJson = json_encode($geoDataForJs, JSON_UNESCAPED_UNICODE);

        $this->view('pv/edit', compact('pv','unites','regions','primos','infractions','user','geoJson'));
    }

    public function update(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        $user = Auth::currentUser();
        $role = $user['role_code'] ?? '';
        $pvId = (int)$id;

        // ── Contexte du formulaire soumis ────────────────────────────────────
        // 'qualification' = formulaire de qualification substitut (show.php)
        // 'edit_general'  = formulaire d'édition générale (edit.php)
        // Ce champ évite qu'un formulaire partiel n'écrase les données de l'autre.
        $formContext = $_POST['_form_context'] ?? 'edit_general';

        // ════════════════════════════════════════════════════════════════════
        // BLOC A — Qualification substitut
        // Acteurs : substitut_procureur, procureur, admin
        // Mis à jour UNIQUEMENT quand _form_context = 'qualification'
        // N'efface jamais pv_infractions type='unite'
        // ════════════════════════════════════════════════════════════════════
        if ($formContext === 'qualification'
            && in_array($role, ['admin', 'procureur', 'substitut_procureur'])
        ) {
            // Calcul de qualification_substitut_id : première infraction cochée
            $qsubId = null;
            if (!empty($_POST['infractions_substitut']) && is_array($_POST['infractions_substitut'])) {
                $first = reset($_POST['infractions_substitut']);
                if ($first) $qsubId = (int)$first;
            }

            $this->db->prepare(
                "UPDATE pv SET qualification_substitut_id=:qsub,
                               qualification_details=:qdet,
                               lois_applicables=:lois
                 WHERE id=:id"
            )->execute([
                'qsub' => $qsubId,
                'qdet' => $this->sanitize($_POST['qualification_details'] ?? ''),
                'lois' => $this->sanitize($_POST['lois_applicables'] ?? ''),
                'id'   => $pvId,
            ]);

            // Mise à jour infractions substitut UNIQUEMENT — sans toucher aux infractions unité
            $this->db->prepare(
                "DELETE FROM pv_infractions WHERE pv_id=? AND type='substitut'"
            )->execute([$pvId]);

            if (!empty($_POST['infractions_substitut']) && is_array($_POST['infractions_substitut'])) {
                $insIS = $this->db->prepare(
                    "INSERT IGNORE INTO pv_infractions
                         (pv_id, infraction_id, type, est_complicite, notes)
                     VALUES (?,?,'substitut',?,?)"
                );
                foreach ($_POST['infractions_substitut'] as $iid) {
                    $complicite = isset($_POST['complicite_' . $iid]) ? 1 : 0;
                    $notes      = $this->sanitize($_POST['notes_infraction_' . $iid] ?? '');
                    $insIS->execute([$pvId, (int)$iid, $complicite, $notes ?: null]);
                }
            }

            $this->flash('success', 'Qualification enregistrée avec succès.');
            $this->redirect('/pv/show/' . $pvId);
            return;
        }

        // ════════════════════════════════════════════════════════════════════
        // BLOC B — Édition générale (edit.php)
        // Acteurs : admin, greffier, procureur
        // Met à jour données générales + infractions_unite
        // Ne touche jamais aux infractions_substitut ni à qualification_substitut_id
        // ════════════════════════════════════════════════════════════════════
        if (in_array($role, ['admin', 'greffier', 'procureur'])) {
            // Validation unicité du N° RP (si renseigné et différent de l'actuel)
            $numeroRP    = trim($this->sanitize($_POST['numero_rp'] ?? ''));
            $numeroOrdre = trim($this->sanitize($_POST['numero_ordre'] ?? ''));
            if ($numeroRP !== '') {
                $chkRP = $this->db->prepare(
                    "SELECT COUNT(*) FROM pv WHERE numero_rp = ? AND id != ?"
                );
                $chkRP->execute([$numeroRP, $pvId]);
                if ((int)$chkRP->fetchColumn() > 0) {
                    $this->flash('error', "Le N° RP « {$numeroRP} » est déjà utilisé par un autre PV.");
                    $this->redirect('/pv/edit/' . $pvId);
                    return;
                }
            }

            $this->db->prepare(
                "UPDATE pv SET numero_pv=:pv, numero_rp=:rp, numero_ordre=:nordre,
                 unite_enquete_id=:ue, date_pv=:dpv, date_reception=:drec,
                 type_affaire=:type, infraction_id=:infr, est_antiterroriste=:anti,
                 region_id=:reg, departement_id=:dep, commune_id=:com,
                 description_faits=:desc WHERE id=:id"
            )->execute([
                'pv'     => $this->sanitize($_POST['numero_pv'] ?? ''),
                'rp'     => $numeroRP ?: null,
                'nordre' => $numeroOrdre ?: null,
                'ue'     => !empty($_POST['unite_enquete_id']) ? (int)$_POST['unite_enquete_id'] : null,
                'dpv'    => $_POST['date_pv'],
                'drec'   => $_POST['date_reception'],
                'type'   => $_POST['type_affaire'],
                'infr'   => !empty($_POST['infraction_id']) ? (int)$_POST['infraction_id'] : null,
                'anti'   => isset($_POST['est_antiterroriste']) ? 1 : 0,
                'reg'    => !empty($_POST['region_id']) ? (int)$_POST['region_id'] : null,
                'dep'    => !empty($_POST['departement_id']) ? (int)$_POST['departement_id'] : null,
                'com'    => !empty($_POST['commune_id']) ? (int)$_POST['commune_id'] : null,
                'desc'   => $this->sanitize($_POST['description_faits'] ?? ''),
                'id'     => $pvId,
            ]);

            // Primo intervenants
            $this->db->prepare(
                "DELETE FROM pv_primo_intervenants WHERE pv_id=?"
            )->execute([$pvId]);
            if (!empty($_POST['primo_intervenants']) && is_array($_POST['primo_intervenants'])) {
                $insPI = $this->db->prepare(
                    "INSERT IGNORE INTO pv_primo_intervenants (pv_id, primo_intervenant_id) VALUES (?,?)"
                );
                foreach ($_POST['primo_intervenants'] as $piId) {
                    $insPI->execute([$pvId, (int)$piId]);
                }
            }

            // Infractions unité — mise à jour complète (le formulaire edit.php envoie toujours la liste complète)
            // Les infractions substitut ne sont PAS touchées ici.
            $this->db->prepare(
                "DELETE FROM pv_infractions WHERE pv_id=? AND type='unite'"
            )->execute([$pvId]);
            if (!empty($_POST['infractions_unite']) && is_array($_POST['infractions_unite'])) {
                $insIU = $this->db->prepare(
                    "INSERT IGNORE INTO pv_infractions (pv_id, infraction_id, type) VALUES (?,?,'unite')"
                );
                foreach ($_POST['infractions_unite'] as $iid) {
                    $insIU->execute([$pvId, (int)$iid]);
                }
            }
        }

        $this->flash('success', 'PV mis à jour avec succès.');
        $this->redirect('/pv/show/' . $pvId);
    }

    public function affecter(string $id): void {
        Auth::requireLogin();
        CSRF::check();

        $user = Auth::currentUser();
        $uid  = (int)($user['id'] ?? 0);
        $role = $user['role_code'] ?? '';
        $pvId = (int)$id;

        // Greffier avec droit pv_affecter accordé, ou rôles standards
        $allowedByRole     = in_array($role, ['admin','procureur','president']);
        $allowedByFonction = ($role === 'greffier'
            && DroitsController::hasFuncAccess($uid, 'pv_affecter'));

        if (!$allowedByRole && !$allowedByFonction) {
            $this->flash('error', 'Vous n\'avez pas les droits pour affecter un substitut.');
            $this->redirect('/pv/show/' . $id);
            return;
        }

        $nouveauSubstitutId = (int)($_POST['substitut_id'] ?? 0);
        if (!$nouveauSubstitutId) {
            $this->flash('error', 'Veuillez sélectionner un substitut.');
            $this->redirect('/pv/show/' . $id);
            return;
        }

        // Récupérer le PV actuel pour tracer l'ancienne affectation
        $stmtPV = $this->db->prepare("SELECT substitut_id, statut FROM pv WHERE id = ?");
        $stmtPV->execute([$pvId]);
        $pvActuel = $stmtPV->fetch();
        $ancienSubstitutId = $pvActuel ? ((int)$pvActuel['substitut_id'] ?: null) : null;

        // Refus si même substitut que l'actuel
        if ($ancienSubstitutId && $ancienSubstitutId === $nouveauSubstitutId) {
            $this->flash('error', 'Ce substitut est déjà assigné à ce PV.');
            $this->redirect('/pv/show/' . $id);
            return;
        }

        // Mise à jour du PV
        $this->db->prepare(
            "UPDATE pv SET substitut_id=:s, statut='en_traitement', date_affectation_substitut=CURDATE() WHERE id=:id"
        )->execute(['s' => $nouveauSubstitutId, 'id' => $pvId]);

        // ── Traçabilité dans mouvements_pv (si la table existe) ─────────────
        try {
            // Récupérer noms des substituts pour le message
            $nomAncien  = '';
            $nomNouveau = '';
            if ($ancienSubstitutId) {
                $stA = $this->db->prepare("SELECT CONCAT(prenom,' ',nom) n FROM users WHERE id=?");
                $stA->execute([$ancienSubstitutId]);
                $nomAncien = $stA->fetchColumn() ?: "Substitut #{$ancienSubstitutId}";
            }
            $stN = $this->db->prepare("SELECT CONCAT(prenom,' ',nom) n FROM users WHERE id=?");
            $stN->execute([$nouveauSubstitutId]);
            $nomNouveau = $stN->fetchColumn() ?: "Substitut #{$nouveauSubstitutId}";

            $motif  = $this->sanitize($_POST['motif_reaffectation'] ?? '');
            $desc   = $ancienSubstitutId
                ? "Réaffectation : {$nomAncien} → {$nomNouveau}" . ($motif ? " — {$motif}" : '')
                : "Affectation initiale : {$nomNouveau}";

            $this->db->prepare(
                "INSERT INTO mouvements_pv
                 (pv_id, user_id, type_mouvement, ancien_substitut_id, nouveau_substitut_id, description, created_at)
                 VALUES (?, ?, 'affectation_substitut', ?, ?, ?, NOW())"
            )->execute([$pvId, $uid, $ancienSubstitutId, $nouveauSubstitutId, $desc]);
        } catch (\Exception $e) {
            // La table mouvements_pv peut ne pas exister — on trace dans mouvements_dossier si lié
            try {
                $stD = $this->db->prepare("SELECT id FROM dossiers WHERE pv_id=? LIMIT 1");
                $stD->execute([$pvId]);
                $dossierId = $stD->fetchColumn();
                if ($dossierId) {
                    $motif = $this->sanitize($_POST['motif_reaffectation'] ?? '');
                    $desc  = $ancienSubstitutId
                        ? "PV : réaffectation substitut → {$nomNouveau}" . ($motif ? " — {$motif}" : '')
                        : "PV : affectation substitut → {$nomNouveau}";
                    $this->db->prepare(
                        "INSERT INTO mouvements_dossier
                         (dossier_id, user_id, type_mouvement, nouveau_statut, description)
                         VALUES (?, ?, 'affectation_substitut', 'en_traitement', ?)"
                    )->execute([$dossierId, $uid, $desc]);
                }
            } catch (\Exception $e2) { /* silencieux */ }
        }

        $msgFlash = $ancienSubstitutId
            ? "Substitut réaffecté avec succès. Historique tracé."
            : "PV affecté au substitut du procureur.";
        $this->flash('success', $msgFlash);
        $this->redirect('/pv/show/' . $id);
    }

    public function classer(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','procureur','substitut_procureur']);
        $motif = $this->sanitize($_POST['motif_classement'] ?? '');
        $this->db->prepare("UPDATE pv SET statut='classe', motif_classement=:m, date_classement=CURDATE() WHERE id=:id")
            ->execute(['m' => $motif, 'id' => (int)$id]);
        $this->flash('success', 'PV classé sans suite.');
        $this->redirect('/pv/show/' . $id);
    }

    public function declasser(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','procureur']);
        $id = (int)$id;
        $motif = $this->sanitize($_POST['motif_declassement'] ?? '');
        if (!$motif) {
            $this->flash('error', 'Veuillez indiquer le motif du déclassement.');
            $this->redirect('/pv/show/' . $id);
            return;
        }
        $stmtPV = $this->db->prepare("SELECT statut FROM pv WHERE id=?");
        $stmtPV->execute([$id]);
        $pv = $stmtPV->fetch();
        if (!$pv || $pv['statut'] !== 'classe') {
            $this->flash('error', 'Ce PV n\'est pas classé sans suite.');
            $this->redirect('/pv/show/' . $id);
            return;
        }
        $this->db->prepare(
            "UPDATE pv SET statut='en_traitement', motif_classement=NULL,
             motif_declassement=:m, date_declassement=CURDATE()
             WHERE id=:id"
        )->execute([':m' => $motif, ':id' => $id]);
        $this->flash('success', 'PV déclassé et remis en traitement.');
        $this->redirect('/pv/show/' . $id);
    }

    // ── Suppression définitive d'un PV (admin uniquement) ─────────────────────
    public function delete(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin']);

        $id = (int)$id;
        if ($id <= 0) {
            $this->flash('error', 'Identifiant de PV invalide.');
            $this->redirect('/pv');
            return;
        }

        // Vérifier que le PV existe
        $stmtChk = $this->db->prepare("SELECT id, numero_rg FROM pv WHERE id = ?");
        $stmtChk->execute([$id]);
        $pvRow = $stmtChk->fetch();
        if (!$pvRow) {
            $this->flash('error', 'PV introuvable.');
            $this->redirect('/pv');
            return;
        }

        // ── 1. Supprimer les fichiers physiques des documents liés ────────────
        $docsStmt = $this->db->prepare(
            "SELECT chemin_fichier FROM documents WHERE pv_id = ?"
        );
        $docsStmt->execute([$id]);
        foreach ($docsStmt->fetchAll() as $doc) {
            if (!empty($doc['chemin_fichier'])) {
                $absPath = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR
                    . str_replace('/', DIRECTORY_SEPARATOR, ltrim($doc['chemin_fichier'], '/'));
                if (file_exists($absPath)) {
                    @unlink($absPath);
                }
            }
        }

        // ── 2. Supprimer les enregistrements liés (cascade si non activée) ────
        $this->db->prepare("DELETE FROM documents              WHERE pv_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM pv_infractions         WHERE pv_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM pv_primo_intervenants  WHERE pv_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM mises_en_cause         WHERE pv_id = ?")->execute([$id]);
        // Dissocier les dossiers liés (ne pas supprimer les dossiers eux-mêmes)
        $this->db->prepare("UPDATE dossiers SET pv_id = NULL   WHERE pv_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM dossier_pvs            WHERE pv_id = ?")->execute([$id]);

        // ── 3. Supprimer le PV lui-même ───────────────────────────────────────
        $this->db->prepare("DELETE FROM pv WHERE id = ?")->execute([$id]);

        $this->flash('success', "PV {$pvRow['numero_rg']} supprimé définitivement.");
        $this->redirect('/pv');
    }

    public function transferer(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','procureur','substitut_procureur']);

        $stmtPV = $this->db->prepare("SELECT * FROM pv WHERE id=?");
        $stmtPV->execute([(int)$id]);
        $pvData = $stmtPV->fetch();
        if (!$pvData) { $this->redirect('/pv'); }

        $num    = new Numerotation($this->db);
        $annee  = date('Y');

        $modePoursuite = $_POST['mode_poursuite'] ?? 'RI';
        if (!in_array($modePoursuite, ['RI','CD','FD','CRPC','autre'])) {
            $modePoursuite = 'RI';
        }

        $destination = ($modePoursuite === 'RI') ? 'instruction' : 'audience';

        $numeroRP = $pvData['numero_rp'] ?? null;
        if (!$numeroRP) {
            $numeroRP = $num->genererRP($annee);
        }

        $numeroRI  = null;
        $cabinetId = null;
        if ($destination === 'instruction') {
            $numeroRI = trim($this->sanitize($_POST['numero_ri'] ?? ''));
            if ($numeroRI) {
                $chkRI = $this->db->prepare("SELECT COUNT(*) FROM dossiers WHERE numero_ri = ?");
                $chkRI->execute([$numeroRI]);
                if ((int)$chkRI->fetchColumn() > 0) {
                    $this->flash('error', "Le numéro RI {$numeroRI} existe déjà dans le système.");
                    $this->redirect('/pv/show/' . $id);
                    return;
                }
            } else {
                $numeroRI = $num->genererRI($annee);
            }
            $cabinetId = !empty($_POST['cabinet_id']) ? (int)$_POST['cabinet_id'] : null;
        }

        $numeroRG    = $num->genererRG($annee);
        $statut      = ($destination === 'instruction') ? 'en_instruction' : 'en_audience';
        $dateInstDeb = ($destination === 'instruction') ? date('Y-m-d') : null;
        $dateLimite  = ($destination === 'instruction')
            ? date('Y-m-d', strtotime('+' . DELAI_INSTRUCTION_MOIS . ' months'))
            : date('Y-m-d', strtotime('+30 days'));

        $ins = $this->db->prepare(
            "INSERT INTO dossiers (pv_id, numero_rg, numero_rp, numero_ri, type_affaire,
             date_enregistrement, objet, statut, substitut_id, cabinet_id, mode_poursuite,
             date_limite_traitement, date_instruction_debut, created_by)
             VALUES (:pvid, :rg, :rp, :ri, :type, CURDATE(), :objet, :statut, :sub, :cab, :mp, :dlim, :dinst, :by)"
        );
        $ins->execute([
            'pvid'  => (int)$id,
            'rg'    => $numeroRG,
            'rp'    => $numeroRP,
            'ri'    => $numeroRI,
            'type'  => $pvData['type_affaire'],
            'objet' => $this->sanitize($_POST['objet'] ?? $pvData['description_faits'] ?? 'À compléter'),
            'statut'=> $statut,
            'sub'   => $pvData['substitut_id'],
            'cab'   => $cabinetId,
            'mp'    => $modePoursuite,
            'dlim'  => $dateLimite,
            'dinst' => $dateInstDeb,
            'by'    => Auth::userId(),
        ]);
        $dossierId = (int)$this->db->lastInsertId();

        // Enregistrer la jonction dans dossier_pvs
        $this->db->prepare("INSERT IGNORE INTO dossier_pvs (dossier_id, pv_id, joint_par) VALUES (?,?,?)")
            ->execute([$dossierId, (int)$id, Auth::userId()]);

        // Historique
        $mpLabels = [
            'RI'    => 'Réquisitoire Introductif (→ Cabinet instruction)',
            'CD'    => 'Citation Directe (→ Audience directe)',
            'FD'    => 'Flagrant Délit (→ Audience directe)',
            'CRPC'  => 'CRPC (→ Audience directe)',
            'autre' => 'Autre',
        ];
        $histDesc = "Dossier créé depuis PV {$pvData['numero_rg']} — Mode : " . ($mpLabels[$modePoursuite] ?? $modePoursuite);
        $this->db->prepare("INSERT INTO mouvements_dossier (dossier_id, user_id, type_mouvement, nouveau_statut, description) VALUES (?,?,?,?,?)")
            ->execute([$dossierId, Auth::userId(), 'creation', $statut, $histDesc]);

        // Mettre à jour le PV
        $pvStatut = ($destination === 'instruction') ? 'transfere_instruction' : 'transfere_jugement_direct';
        $this->db->prepare("UPDATE pv SET statut=:s, mode_poursuite=:mp WHERE id=:id")
            ->execute([':s' => $pvStatut, ':mp' => $modePoursuite, ':id' => (int)$id]);

        // ── CRPC : sauvegarder la fiche si mode = CRPC ───────────────────────
        if ($modePoursuite === 'CRPC') {
            // Résoudre la valeur homologation (peut être '', '0', '1')
            $homoRaw = $_POST['crpc_homologation'] ?? '';
            $homoVal = ($homoRaw === '') ? null : (int)$homoRaw;

            $crpcStmt = $this->db->prepare(
                "INSERT INTO crpc_dossiers
                 (pv_id, dossier_id, substitut_id, date_mise_en_oeuvre,
                  qualification_faits, date_faits, texte_applicable, peine_prevue,
                  assistance_avocat, renonciation_avocat, nom_avocat,
                  peine_emprisonnement, sursis_substitut, amende_proposee,
                  date_audience_homologation, homologation,
                  peine_emprisonnement_homo, sursis_homologue, amende_homologuee,
                  motif_refus_homologation, notes, statut, created_by)
                 VALUES
                 (:pvid, :dosid, :subid, :dmeo,
                  :qual, :dfaits, :texte, :peine,
                  :asavo, :renavo, :nomavo,
                  :peimp, :sursis, :amende,
                  :dahom, :homo,
                  :peiho, :surho, :amho,
                  :motref, :notes, :statut, :by)"
            );
            $crpcId = null;
            try {
                $crpcStmt->execute([
                    'pvid'   => (int)$id,
                    'dosid'  => $dossierId,
                    'subid'  => $pvData['substitut_id'],
                    'dmeo'   => !empty($_POST['crpc_date_mise_en_oeuvre']) ? $_POST['crpc_date_mise_en_oeuvre'] : null,
                    'qual'   => $this->sanitize($_POST['crpc_qualification_faits'] ?? ''),
                    'dfaits' => !empty($_POST['crpc_date_faits']) ? $_POST['crpc_date_faits'] : null,
                    'texte'  => $this->sanitize($_POST['crpc_texte_applicable'] ?? ''),
                    'peine'  => $this->sanitize($_POST['crpc_peine_prevue'] ?? ''),
                    'asavo'  => isset($_POST['crpc_assistance_avocat']) ? 1 : 0,
                    'renavo' => isset($_POST['crpc_renonciation_avocat']) ? 1 : 0,
                    'nomavo' => $this->sanitize($_POST['crpc_nom_avocat'] ?? ''),
                    'peimp'  => $this->sanitize($_POST['crpc_peine_emprisonnement'] ?? ''),
                    'sursis' => (int)($_POST['crpc_sursis_substitut'] ?? 0),
                    'amende' => !empty($_POST['crpc_amende_proposee']) ? (float)$_POST['crpc_amende_proposee'] : null,
                    'dahom'  => !empty($_POST['crpc_date_audience_homologation']) ? $_POST['crpc_date_audience_homologation'] : null,
                    'homo'   => $homoVal,
                    'peiho'  => $this->sanitize($_POST['crpc_peine_emprisonnement_homo'] ?? ''),
                    'surho'  => (int)($_POST['crpc_sursis_homologue'] ?? 0),
                    'amho'   => !empty($_POST['crpc_amende_homologuee']) ? (float)$_POST['crpc_amende_homologuee'] : null,
                    'motref' => $this->sanitize($_POST['crpc_motif_refus_homologation'] ?? ''),
                    'notes'  => $this->sanitize($_POST['crpc_notes'] ?? ''),
                    'statut' => ($homoVal === null) ? 'en_cours' : ($homoVal ? 'homologuee' : 'refusee'),
                    'by'     => Auth::userId(),
                ]);
                $crpcId = (int)$this->db->lastInsertId();

                // Personnes poursuivies
                $nomsArr  = (array)($_POST['crpc_nom_prenom']   ?? []);
                $sexeArr  = (array)($_POST['crpc_sexe']         ?? []);
                $ageArr   = (array)($_POST['crpc_age']          ?? []);
                $natArr   = (array)($_POST['crpc_nationalite']  ?? []);
                $profArr  = (array)($_POST['crpc_profession']   ?? []);
                $quartArr = (array)($_POST['crpc_quartier']     ?? []);
                $mecArr   = (array)($_POST['crpc_mec_id']       ?? []);

                $insPers = $this->db->prepare(
                    "INSERT INTO crpc_personnes
                     (crpc_id, mec_id, numero_ordre, nom_prenom, sexe, age, nationalite, profession, quartier)
                     VALUES (?,?,?,?,?,?,?,?,?)"
                );
                foreach ($nomsArr as $i => $nom) {
                    $nom = $this->sanitize(trim($nom));
                    if (!$nom) continue;
                    $mecId = !empty($mecArr[$i]) ? (int)$mecArr[$i] : null;
                    $age   = !empty($ageArr[$i])  ? (int)$ageArr[$i]  : null;
                    $insPers->execute([
                        $crpcId,
                        $mecId,
                        $i + 1,
                        $nom,
                        $sexeArr[$i] ?? null,
                        $age,
                        $this->sanitize($natArr[$i] ?? 'Nigérienne'),
                        $this->sanitize($profArr[$i] ?? ''),
                        $this->sanitize($quartArr[$i] ?? ''),
                    ]);
                }
            } catch (\Exception $e) {
                // CRPC table may not exist yet — silently skip (migration pending)
                // In production, run migrations/v2.7_crpc.sql first
            }
        }


        $label = $destination === 'instruction' ? "Cabinet d'instruction" : 'Audience directe';
        $this->flash('success', "Dossier créé : {$numeroRG}" . ($numeroRI ? " / RI {$numeroRI}" : '') . " → {$label}.");
        $this->redirect('/dossiers/show/' . $dossierId);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Modifier la fiche CRPC (GET : formulaire d'édition)
    // Route : GET /crpc/edit/{crpc_id}
    // ──────────────────────────────────────────────────────────────────────────
    public function editCrpc(string $crpcId): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','procureur','substitut_procureur']);
        $user  = Auth::currentUser();
        $flash = $this->getFlash();

        // Charger la fiche CRPC
        $stmtC = $this->db->prepare(
            "SELECT cd.*, p.numero_rg AS pv_numero_rg, p.numero_pv AS pv_numero_pv,
                    p.id AS pv_id_ref, p.description_faits,
                    p.qualification_details, p.lois_applicables,
                    us.nom AS substitut_nom, us.prenom AS substitut_prenom
             FROM crpc_dossiers cd
             JOIN pv p   ON cd.pv_id     = p.id
             LEFT JOIN users us ON p.substitut_id = us.id
             WHERE cd.id = ?"
        );
        $stmtC->execute([(int)$crpcId]);
        $crpc = $stmtC->fetch();
        if (!$crpc) {
            $this->flash('error', 'Dossier CRPC introuvable.');
            $this->redirect('/pv');
            return;
        }

        // Charger le PV associé
        $stmtP = $this->db->prepare("SELECT * FROM pv WHERE id = ?");
        $stmtP->execute([$crpc['pv_id']]);
        $pv = $stmtP->fetch();

        // Cacher le nom du substitut dans un champ calculé pour affichage
        $crpc['substitut_nom_cache'] = trim(($crpc['substitut_prenom'] ?? '') . ' ' . ($crpc['substitut_nom'] ?? ''));

        // Charger les personnes poursuivies
        $stmtPers = $this->db->prepare(
            "SELECT * FROM crpc_personnes WHERE crpc_id = ? ORDER BY numero_ordre, id"
        );
        $stmtPers->execute([(int)$crpcId]);
        $crpcPersonnes = $stmtPers->fetchAll();

        $this->view('crpc/edit', compact('user', 'flash', 'crpc', 'pv', 'crpcPersonnes'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Sauvegarder les modifications CRPC (POST)
    // Route : POST /crpc/update/{crpc_id}
    // ──────────────────────────────────────────────────────────────────────────
    public function updateCrpc(string $crpcId): void
    {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','procureur','substitut_procureur']);

        // Charger la fiche CRPC (vérification d'existence)
        $stmtC = $this->db->prepare("SELECT * FROM crpc_dossiers WHERE id = ?");
        $stmtC->execute([(int)$crpcId]);
        $crpc = $stmtC->fetch();
        if (!$crpc) {
            $this->flash('error', 'Dossier CRPC introuvable.');
            $this->redirect('/pv');
            return;
        }

        $pvId = $crpc['pv_id'];

        // Résoudre la valeur homologation
        $homoRaw = $_POST['crpc_homologation'] ?? '';
        $homoVal = ($homoRaw === '') ? null : (int)$homoRaw;

        // Calculer le statut automatiquement si non fourni ou cohérent avec homo
        $statutPost = $_POST['crpc_statut'] ?? '';
        if ($homoVal === 1 && $statutPost !== 'abandonnee') {
            $statut = 'homologuee';
        } elseif ($homoVal === 0 && $statutPost !== 'abandonnee') {
            $statut = 'refusee';
        } elseif ($statutPost) {
            $statut = $statutPost;
        } else {
            $statut = 'en_cours';
        }

        // Mise à jour de la fiche CRPC principale
        $upd = $this->db->prepare(
            "UPDATE crpc_dossiers SET
               date_mise_en_oeuvre          = :dmeo,
               qualification_faits          = :qual,
               date_faits                   = :dfaits,
               texte_applicable             = :texte,
               peine_prevue                 = :peine,
               assistance_avocat            = :asavo,
               renonciation_avocat          = :renavo,
               nom_avocat                   = :nomavo,
               peine_emprisonnement         = :peimp,
               sursis_substitut             = :sursis,
               amende_proposee              = :amende,
               date_audience_homologation   = :dahom,
               homologation                 = :homo,
               peine_emprisonnement_homo    = :peiho,
               sursis_homologue             = :surho,
               amende_homologuee            = :amho,
               motif_refus_homologation     = :motref,
               notes                        = :notes,
               statut                       = :statut
             WHERE id = :id"
        );
        $upd->execute([
            'dmeo'   => !empty($_POST['crpc_date_mise_en_oeuvre']) ? $_POST['crpc_date_mise_en_oeuvre'] : null,
            'qual'   => $this->sanitize($_POST['crpc_qualification_faits'] ?? ''),
            'dfaits' => !empty($_POST['crpc_date_faits']) ? $_POST['crpc_date_faits'] : null,
            'texte'  => $this->sanitize($_POST['crpc_texte_applicable'] ?? ''),
            'peine'  => $this->sanitize($_POST['crpc_peine_prevue'] ?? ''),
            'asavo'  => isset($_POST['crpc_assistance_avocat']) ? 1 : 0,
            'renavo' => isset($_POST['crpc_renonciation_avocat']) ? 1 : 0,
            'nomavo' => $this->sanitize($_POST['crpc_nom_avocat'] ?? ''),
            'peimp'  => $this->sanitize($_POST['crpc_peine_emprisonnement'] ?? ''),
            'sursis' => (int)($_POST['crpc_sursis_substitut'] ?? 0),
            'amende' => !empty($_POST['crpc_amende_proposee']) ? (float)$_POST['crpc_amende_proposee'] : null,
            'dahom'  => !empty($_POST['crpc_date_audience_homologation']) ? $_POST['crpc_date_audience_homologation'] : null,
            'homo'   => $homoVal,
            'peiho'  => $this->sanitize($_POST['crpc_peine_emprisonnement_homo'] ?? ''),
            'surho'  => (int)($_POST['crpc_sursis_homologue'] ?? 0),
            'amho'   => !empty($_POST['crpc_amende_homologuee']) ? (float)$_POST['crpc_amende_homologuee'] : null,
            'motref' => $this->sanitize($_POST['crpc_motif_refus_homologation'] ?? ''),
            'notes'  => $this->sanitize($_POST['crpc_notes'] ?? ''),
            'statut' => $statut,
            'id'     => (int)$crpcId,
        ]);

        // ── Mettre à jour les personnes poursuivies ──────────────────────────
        $nomsArr     = (array)($_POST['crpc_nom_prenom']    ?? []);
        $sexeArr     = (array)($_POST['crpc_sexe']          ?? []);
        $ageArr      = (array)($_POST['crpc_age']           ?? []);
        $natArr      = (array)($_POST['crpc_nationalite']   ?? []);
        $profArr     = (array)($_POST['crpc_profession']    ?? []);
        $quartArr    = (array)($_POST['crpc_quartier']      ?? []);
        $mecArr      = (array)($_POST['crpc_mec_id']        ?? []);
        $personneIds = (array)($_POST['crpc_personne_id']   ?? []);

        // Supprimer toutes les personnes existantes puis réinsérer
        $this->db->prepare("DELETE FROM crpc_personnes WHERE crpc_id = ?")->execute([(int)$crpcId]);

        $insPers = $this->db->prepare(
            "INSERT INTO crpc_personnes
             (crpc_id, mec_id, numero_ordre, nom_prenom, sexe, age, nationalite, profession, quartier)
             VALUES (?,?,?,?,?,?,?,?,?)"
        );
        foreach ($nomsArr as $i => $nom) {
            $nom = $this->sanitize(trim($nom));
            if (!$nom) continue;
            $mecId = !empty($mecArr[$i]) ? (int)$mecArr[$i] : null;
            $age   = !empty($ageArr[$i])  ? (int)$ageArr[$i]  : null;
            $insPers->execute([
                (int)$crpcId,
                $mecId,
                $i + 1,
                $nom,
                $sexeArr[$i] ?? null,
                $age,
                $this->sanitize($natArr[$i] ?? 'Nigérienne'),
                $this->sanitize($profArr[$i] ?? ''),
                $this->sanitize($quartArr[$i] ?? ''),
            ]);
        }

        $this->flash('success', 'Fiche CRPC mise à jour avec succès.');
        $this->redirect('/pv/show/' . $pvId);
    }

    // Fusionner plusieurs PVs (même RP) dans un seul dossier
    public function fusionner(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','procureur','substitut_procureur']);

        $pvIds = array_map('intval', (array)($_POST['pv_ids'] ?? []));
        if (empty($pvIds)) {
            $this->flash('error', 'Sélectionnez au moins un PV à fusionner.');
            $this->redirect('/pv/show/' . $id);
            return;
        }

        // Vérifier qu'il y a un dossier existant pour ce PV
        $dossierStmt = $this->db->prepare(
            "SELECT d.* FROM dossiers d
             LEFT JOIN dossier_pvs dp ON dp.dossier_id = d.id
             WHERE d.pv_id=? OR dp.pv_id=?
             ORDER BY d.created_at DESC LIMIT 1"
        );
        $dossierStmt->execute([(int)$id, (int)$id]);
        $dossier = $dossierStmt->fetch();

        if (!$dossier) {
            $this->flash('error', 'Ce PV n\'a pas encore de dossier. Transférez-le d\'abord.');
            $this->redirect('/pv/show/' . $id);
            return;
        }

        $insJonction = $this->db->prepare("INSERT IGNORE INTO dossier_pvs (dossier_id, pv_id, joint_par) VALUES (?,?,?)");
        foreach ($pvIds as $pid) {
            $insJonction->execute([$dossier['id'], $pid, Auth::userId()]);
            // Mettre à jour le statut du PV joint
            $this->db->prepare("UPDATE pv SET statut='transfere_instruction' WHERE id=?")->execute([$pid]);
        }

        $this->flash('success', count($pvIds) . ' PV(s) fusionné(s) dans le dossier ' . $dossier['numero_rg'] . '.');
        $this->redirect('/dossiers/show/' . $dossier['id']);
    }

    // API endpoint : upload pièce jointe pour un PV (réservé substitut)
public function uploadDocument(string $pvId): void {
    // Force la réponse JSON dès le départ
    header('Content-Type: application/json; charset=utf-8');

    Auth::requireLogin();
    Auth::requireRole(['admin','procureur','substitut_procureur']);
    CSRF::check();

    $pvId = (int)$pvId;

    // 1. Vérifier que le PV existe et récupérer le dossier_id éventuel
    $pvStmt = $this->db->prepare("SELECT id, dossier_id FROM pv p
                                  LEFT JOIN dossier_pvs dp ON dp.pv_id = p.id
                                  WHERE p.id = ? LIMIT 1");
    // Note : dossier_id sur pv n'existe pas, on récupère via dossier_pvs ou dossiers.pv_id
    $pvStmt = $this->db->prepare(
        "SELECT p.id,
                COALESCE(dp.dossier_id, d.id) AS dossier_id
         FROM pv p
         LEFT JOIN dossier_pvs dp ON dp.pv_id = p.id
         LEFT JOIN dossiers d ON d.pv_id = p.id
         WHERE p.id = ?
         LIMIT 1"
    );
    $pvStmt->execute([$pvId]);
    $pvRow = $pvStmt->fetch(PDO::FETCH_ASSOC);
    if (!$pvRow) {
        echo json_encode(['error' => 'PV introuvable']);
        return;
    }

    // 2. Vérifier le fichier reçu
    if (empty($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
        $errMap = [
            UPLOAD_ERR_INI_SIZE   => 'Fichier trop volumineux (limite serveur)',
            UPLOAD_ERR_FORM_SIZE  => 'Fichier trop volumineux (limite formulaire)',
            UPLOAD_ERR_PARTIAL    => 'Upload partiel',
            UPLOAD_ERR_NO_FILE    => 'Aucun fichier envoyé',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
            UPLOAD_ERR_CANT_WRITE => 'Impossible d\'écrire sur disque',
            UPLOAD_ERR_EXTENSION  => 'Upload bloqué par une extension PHP',
        ];
        $code = $_FILES['fichier']['error'] ?? UPLOAD_ERR_NO_FILE;
        echo json_encode(['error' => $errMap[$code] ?? 'Erreur upload']);
        return;
    }

    $file = $_FILES['fichier'];

    // 3. Validation taille (10 Mo)
    if ($file['size'] > 10 * 1024 * 1024) {
        echo json_encode(['error' => 'Fichier trop volumineux (max 10 Mo)']);
        return;
    }

    // 4. Validation extension
    $allowed = ['pdf','doc','docx','jpg','jpeg','png','xlsx','xls','odt'];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        echo json_encode(['error' => 'Type de fichier non autorisé']);
        return;
    }

    // 5. Création du dossier de destination
    $dir = ROOT_PATH . '/public/uploads/documents/pv_' . $pvId . '/';
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        echo json_encode(['error' => 'Impossible de créer le dossier de stockage']);
        return;
    }
    if (!is_writable($dir)) {
        echo json_encode(['error' => 'Dossier de stockage non accessible en écriture : ' . $dir]);
        return;
    }

    // 6. Nom de fichier sécurisé
    $hash    = bin2hex(random_bytes(8));
    $safe    = preg_replace('/[^a-zA-Z0-9.\-_]/', '_', $file['name']);
    $newName = $hash . '_' . $safe;
    $absPath = $dir . $newName;

    if (!move_uploaded_file($file['tmp_name'], $absPath)) {
        echo json_encode(['error' => 'Échec de l\'écriture du fichier sur le disque']);
        return;
    }

    // 7. Détection MIME
    $mimeType = $file['type'] ?? 'application/octet-stream';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $detected = finfo_file($finfo, $absPath);
            finfo_close($finfo);
            if ($detected) $mimeType = $detected;
        }
    }

    // 8. INSERT en base — colonnes EXACTES du schéma documents
    try {
        $user = Auth::currentUser();
        $roleCode = $user['role_code'] ?? null;

        $sql = "INSERT INTO documents
                (dossier_id, pv_id, nom_original, nom_stockage, chemin_fichier,
                 type_document, mime_type, taille_octets, description,
                 uploaded_by, uploaded_by_role, created_at)
                VALUES (?, ?, ?, ?, ?, 'piece_jointe', ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            $pvRow['dossier_id'] ?: null,
            $pvId,
            $file['name'],
            $newName,
            'uploads/documents/pv_' . $pvId . '/' . $newName,
            $mimeType,
            (int)$file['size'],
            $this->sanitize($_POST['description'] ?? '') ?: null,
            (int)Auth::userId(),
            $roleCode,
        ]);

        if (!$ok) {
            @unlink($absPath);
            $errInfo = $stmt->errorInfo();
            echo json_encode(['error' => 'Erreur SQL : ' . ($errInfo[2] ?? 'inconnue')]);
            return;
        }

        $docId = (int)$this->db->lastInsertId();

        echo json_encode([
            'success' => true,
            'id'      => $docId,
            'nom'     => $file['name'],
            'url'     => BASE_URL . '/documents/view/' . $docId,
            'taille'  => (int)$file['size'],
            'message' => 'Document uploadé avec succès',
        ]);
    } catch (\Throwable $e) {
        @unlink($absPath);
        echo json_encode(['error' => 'Exception : ' . $e->getMessage()]);
    }
}


    // ─── Suppression d'une pièce jointe PV ───────────────────────────────────
    public function deleteDocument(string $id): void {
        header('Content-Type: application/json; charset=utf-8');
        Auth::requireLogin();
        CSRF::check();

        $id = (int)$id;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID invalide.']);
            return;
        }

        // Charger le document + son PV pour vérifier les droits
        $stmt = $this->db->prepare(
            "SELECT d.*, p.created_by AS pv_created_by, p.substitut_id AS pv_substitut_id
             FROM documents d
             LEFT JOIN pv p ON p.id = d.pv_id
             WHERE d.id = :id AND d.pv_id IS NOT NULL"
        );
        $stmt->execute([':id' => $id]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$doc) {
            echo json_encode(['success' => false, 'error' => 'Document introuvable.']);
            return;
        }

        // Contrôle d'accès :
        //   admin/procureur → tous droits
        //   substitut       → seulement ses propres PVs (affecté ou créé)
        //   greffier        → seulement les documents qu'il a uploadés
        $user = Auth::currentUser();
        $role = $user['role_code'] ?? '';
        $uid  = (int)($user['id'] ?? 0);

        $canDelete = false;
        if (in_array($role, ['admin', 'procureur'])) {
            $canDelete = true;
        } elseif ($role === 'substitut_procureur') {
            $canDelete = ((int)$doc['pv_substitut_id'] === $uid || (int)$doc['pv_created_by'] === $uid);
        } else {
            // greffier + autres : uniquement ce qu'ils ont uploadé
            $canDelete = ((int)$doc['uploaded_by'] === $uid);
        }

        if (!$canDelete) {
            echo json_encode(['success' => false, 'error' => "Accès refusé — vous n'avez pas le droit de supprimer ce document."]);
            return;
        }

        // Suppression du fichier physique
        $cheminAbs = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $doc['chemin_fichier']);
        if (file_exists($cheminAbs)) {
            @unlink($cheminAbs);
        }

        // Suppression en base
        $this->db->prepare("DELETE FROM documents WHERE id = :id")->execute([':id' => $id]);

        echo json_encode(['success' => true, 'message' => 'Document supprimé.']);
    }

    // API endpoint : liste des documents d'un PV
public function listDocuments(string $pvId): void {
    Auth::requireLogin();
    $pvId = (int)$pvId;
    $stmt = $this->db->prepare(
        "SELECT d.id, d.nom_original,
                d.taille_octets AS taille, d.mime_type,
                d.description, d.created_at,
                u.nom AS uploader_nom, u.prenom AS uploader_prenom
         FROM documents d
         LEFT JOIN users u ON d.uploaded_by = u.id
         WHERE d.pv_id = ?
         ORDER BY d.created_at DESC"
    );
    $stmt->execute([$pvId]);
    $docs = $stmt->fetchAll();
    foreach ($docs as &$doc) {
        $doc['url']        = BASE_URL . '/documents/view/' . $doc['id'];
        $doc['taille_fmt'] = $this->formatSize((int)$doc['taille']);
        $doc['inline']     = in_array($doc['mime_type'], ['application/pdf','image/jpeg','image/png','image/gif']);
    }
    $this->json($docs);
}


    private function formatSize(int $bytes): string {
        if ($bytes >= 1048576) return round($bytes/1048576,1) . ' Mo';
        if ($bytes >= 1024) return round($bytes/1024,1) . ' Ko';
        return $bytes . ' o';
    }

    // API endpoints AJAX géo
    public function apiDepartements(string $region_id): void {
        $stmt = $this->db->prepare("SELECT id, nom FROM departements WHERE region_id=? ORDER BY nom");
        $stmt->execute([(int)$region_id]);
        $this->json($stmt->fetchAll());
    }

    public function apiCommunes(string $departement_id): void {
        $stmt = $this->db->prepare("SELECT id, nom FROM communes WHERE departement_id=? ORDER BY nom");
        $stmt->execute([(int)$departement_id]);
        $this->json($stmt->fetchAll());
    }

    // API : recherche par RP
    public function apiSearchRP(): void {
        Auth::requireLogin();
        $rp = trim($_GET['rp'] ?? '');
        if (!$rp) { $this->json([]); return; }
        $stmt = $this->db->prepare(
            "SELECT p.id, p.numero_rg, p.numero_pv, p.numero_rp, p.date_reception, p.statut, p.type_affaire
             FROM pv p WHERE p.numero_rp = ? ORDER BY p.date_reception DESC"
        );
        $stmt->execute([$rp]);
        $this->json($stmt->fetchAll());
    }

    private function getPVDetail(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT p.*, ue.nom as unite_nom, ue.type as unite_type,
                    us.nom as substitut_nom, us.prenom as substitut_prenom,
                    r.nom as region_nom, dep.nom as dept_nom, c.nom as commune_nom,
                    cb.nom as created_by_nom, cb.prenom as created_by_prenom,
                    inf.libelle as infraction_libelle, inf.code as infraction_code, inf.categorie as infraction_categorie,
                    qsub.libelle as qualification_substitut_libelle, qsub.code as qualification_substitut_code
             FROM pv p
             LEFT JOIN unites_enquete ue ON p.unite_enquete_id = ue.id
             LEFT JOIN users us ON p.substitut_id = us.id
             LEFT JOIN regions r ON p.region_id = r.id
             LEFT JOIN departements dep ON p.departement_id = dep.id
             LEFT JOIN communes c ON p.commune_id = c.id
             LEFT JOIN users cb ON p.created_by = cb.id
             LEFT JOIN infractions inf ON p.infraction_id = inf.id
             LEFT JOIN infractions qsub ON p.qualification_substitut_id = qsub.id
             WHERE p.id = ?"
        );
        $stmt->execute([$id]);
        $pv = $stmt->fetch();
        if (!$pv) return null;

        // Primo intervenants
        $piStmt = $this->db->prepare(
            "SELECT pi.* FROM primo_intervenants pi
             JOIN pv_primo_intervenants ppi ON pi.id = ppi.primo_intervenant_id
             WHERE ppi.pv_id = ?"
        );
        $piStmt->execute([$id]);
        $pv['primo_intervenants'] = $piStmt->fetchAll();
        $pv['primo_ids'] = array_column($pv['primo_intervenants'], 'id');

        // Infractions multiples
        try {
            $infStmt = $this->db->prepare(
                "SELECT pi.*, inf.libelle, inf.code, inf.categorie
                 FROM pv_infractions pi
                 JOIN infractions inf ON pi.infraction_id = inf.id
                 WHERE pi.pv_id = ?
                 ORDER BY pi.type, inf.libelle"
            );
            $infStmt->execute([$id]);
            $allInfr = $infStmt->fetchAll();
            $pv['infractions_unite']     = array_filter($allInfr, fn($i) => $i['type'] === 'unite');
            $pv['infractions_substitut'] = array_filter($allInfr, fn($i) => $i['type'] === 'substitut');
        } catch (\Exception $e) {
            $pv['infractions_unite']     = [];
            $pv['infractions_substitut'] = [];
        }

        return $pv;
    }
}
