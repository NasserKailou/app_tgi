<?php
class PVController extends Controller {

    // ─── Visibilité : restreindre aux entrées de l'utilisateur sauf admin/procureur ───
    private function addVisibilityFilter(array &$where, array &$params, string $alias = 'p'): void {
        $user = Auth::currentUser();
        $role = $user['role_code'] ?? '';
        // Admin et procureur voient tout
        if (in_array($role, ['admin','procureur'])) return;
        $uid = (int)($user['id'] ?? 0);
        if ($role === 'substitut_procureur') {
            // Voit ses PV affectés + ceux qu'il a créés
            $where[]            = "({$alias}.substitut_id = :vis_uid OR {$alias}.created_by = :vis_uid2)";
            $params['vis_uid']  = $uid;
            $params['vis_uid2'] = $uid;
        } else {
            // Greffier, etc. : seulement ce qu'il a créé
            $where[]             = "{$alias}.created_by = :vis_uid";
            $params['vis_uid']   = $uid;
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
            "SELECT d.*, u.nom AS uploader_nom, u.prenom AS uploader_prenom
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

        $this->view('pv/show', compact(
            'pv','flash','user','substituts','cabinets','infractions',
            'dossier','misesEnCause','pvsMemeRP','pvDocuments','isSubstitut','pvInfractions'
        ));
    }

    // Vérifier si l'utilisateur courant peut accéder à ce PV
    private function canAccessPV(array $pv): bool {
        $user = Auth::currentUser();
        $role = $user['role_code'] ?? '';
        if (in_array($role, ['admin','procureur'])) return true;
        $uid = (int)($user['id'] ?? 0);
        if ($role === 'substitut_procureur') {
            return (int)($pv['substitut_id'] ?? 0) === $uid || (int)($pv['created_by'] ?? 0) === $uid;
        }
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
        $isSubstitut = in_array($role, ['admin','procureur','substitut_procureur']);

        // Substitut peut mettre à jour qualification + lois_applicables
        if ($isSubstitut) {
            $stmt = $this->db->prepare(
                "UPDATE pv SET qualification_substitut_id=:qsub, qualification_details=:qdet,
                 lois_applicables=:lois WHERE id=:id"
            );
            $stmt->execute([
                'qsub' => !empty($_POST['qualification_substitut_id']) ? (int)$_POST['qualification_substitut_id'] : null,
                'qdet' => $this->sanitize($_POST['qualification_details'] ?? ''),
                'lois' => $this->sanitize($_POST['lois_applicables'] ?? ''),
                'id'   => (int)$id,
            ]);

            // Infractions substitut (multi-select)
            $this->db->prepare("DELETE FROM pv_infractions WHERE pv_id=? AND type='substitut'")->execute([(int)$id]);
            if (!empty($_POST['infractions_substitut']) && is_array($_POST['infractions_substitut'])) {
                $insIS = $this->db->prepare(
                    "INSERT IGNORE INTO pv_infractions (pv_id, infraction_id, type, est_complicite, notes) VALUES (?,?,'substitut',?,?)"
                );
                foreach ($_POST['infractions_substitut'] as $iid) {
                    $complicite = isset($_POST['complicite_' . $iid]) ? 1 : 0;
                    $notes      = $this->sanitize($_POST['notes_infraction_' . $iid] ?? '');
                    $insIS->execute([(int)$id, (int)$iid, $complicite, $notes ?: null]);
                }
            }
        }

        // Greffier/admin peut modifier les données générales
        if (in_array($role, ['admin','greffier','procureur'])) {
            $stmt2 = $this->db->prepare(
                "UPDATE pv SET numero_pv=:pv, unite_enquete_id=:ue, date_pv=:dpv, date_reception=:drec,
                 type_affaire=:type, infraction_id=:infr, est_antiterroriste=:anti, region_id=:reg, departement_id=:dep,
                 commune_id=:com, description_faits=:desc WHERE id=:id"
            );
            $stmt2->execute([
                'pv'   => $this->sanitize($_POST['numero_pv'] ?? ''),
                'ue'   => !empty($_POST['unite_enquete_id']) ? (int)$_POST['unite_enquete_id'] : null,
                'dpv'  => $_POST['date_pv'],
                'drec' => $_POST['date_reception'],
                'type' => $_POST['type_affaire'],
                'infr' => !empty($_POST['infraction_id']) ? (int)$_POST['infraction_id'] : null,
                'anti' => isset($_POST['est_antiterroriste']) ? 1 : 0,
                'reg'  => !empty($_POST['region_id']) ? (int)$_POST['region_id'] : null,
                'dep'  => !empty($_POST['departement_id']) ? (int)$_POST['departement_id'] : null,
                'com'  => !empty($_POST['commune_id']) ? (int)$_POST['commune_id'] : null,
                'desc' => $this->sanitize($_POST['description_faits'] ?? ''),
                'id'   => (int)$id,
            ]);

            // Mise à jour primo intervenants
            $this->db->prepare("DELETE FROM pv_primo_intervenants WHERE pv_id=?")->execute([(int)$id]);
            if (!empty($_POST['primo_intervenants']) && is_array($_POST['primo_intervenants'])) {
                $insPI = $this->db->prepare("INSERT IGNORE INTO pv_primo_intervenants (pv_id, primo_intervenant_id) VALUES (?,?)");
                foreach ($_POST['primo_intervenants'] as $piId) {
                    $insPI->execute([(int)$id, (int)$piId]);
                }
            }

            // Infractions unité (multi)
            $this->db->prepare("DELETE FROM pv_infractions WHERE pv_id=? AND type='unite'")->execute([(int)$id]);
            if (!empty($_POST['infractions_unite']) && is_array($_POST['infractions_unite'])) {
                $insIU = $this->db->prepare("INSERT IGNORE INTO pv_infractions (pv_id, infraction_id, type) VALUES (?,?,'unite')");
                foreach ($_POST['infractions_unite'] as $iid) {
                    $insIU->execute([(int)$id, (int)$iid]);
                }
            }
        }

        $this->flash('success', 'PV mis à jour avec succès.');
        $this->redirect('/pv/show/' . $id);
    }

    public function affecter(string $id): void {
        Auth::requireLogin();
        CSRF::check();
        Auth::requireRole(['admin','procureur','president']);
        $substitutId = (int)($_POST['substitut_id'] ?? 0);
        if (!$substitutId) {
            $this->flash('error', 'Veuillez sélectionner un substitut.');
            $this->redirect('/pv/show/' . $id);
        }
        $this->db->prepare("UPDATE pv SET substitut_id=:s, statut='en_traitement', date_affectation_substitut=CURDATE() WHERE id=:id")
            ->execute(['s' => $substitutId, 'id' => (int)$id]);
        $this->flash('success', 'PV affecté au substitut du procureur.');
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

        $label = $destination === 'instruction' ? "Cabinet d'instruction" : 'Audience directe';
        $this->flash('success', "Dossier créé : {$numeroRG}" . ($numeroRI ? " / RI {$numeroRI}" : '') . " → {$label}.");
        $this->redirect('/dossiers/show/' . $dossierId);
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
        Auth::requireLogin();
        Auth::requireRole(['admin','procureur','substitut_procureur']);
        CSRF::check();

        $pvId = (int)$pvId;
        $pvStmt = $this->db->prepare("SELECT id FROM pv WHERE id=?");
        $pvStmt->execute([$pvId]);
        if (!$pvStmt->fetch()) {
            $this->json(['error' => 'PV introuvable'], 404); return;
        }

        if (empty($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['error' => 'Aucun fichier ou erreur upload'], 400); return;
        }

        $file    = $_FILES['fichier'];
        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            $this->json(['error' => 'Fichier trop volumineux (max 10 Mo)'], 400); return;
        }

        $allowed = ['pdf','doc','docx','jpg','jpeg','png','xlsx','xls','odt'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $this->json(['error' => 'Type de fichier non autorisé'], 400); return;
        }

        $dir = ROOT_PATH . '/public/uploads/documents/pv_' . $pvId . '/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $hash    = hash('sha256', $file['name'] . microtime(true));
        $newName = $hash . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '_', $file['name']);
        move_uploaded_file($file['tmp_name'], $dir . $newName);

        // Détecter MIME
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $dir . $newName);
        finfo_close($finfo);

        $desc = $this->sanitize($_POST['description'] ?? '');

        // Insérer dans documents — compatible avec schema variable
        $cols = $this->db->query("SHOW COLUMNS FROM documents")->fetchAll(PDO::FETCH_COLUMN);
        $insert = ['pv_id','nom_original','nom_stockage','chemin','taille','mime_type','description','uploaded_by','uploaded_by_role'];
        $insertCols = array_filter($insert, fn($c) => in_array($c, $cols));
        $insertCols = array_values($insertCols);

        $data = [
            'pv_id'          => $pvId,
            'nom_original'   => $file['name'],
            'nom_stockage'   => $newName,
            'chemin'         => 'uploads/documents/pv_' . $pvId . '/' . $newName,
            'taille'         => $file['size'],
            'mime_type'      => $mimeType,
            'description'    => $desc ?: null,
            'uploaded_by'    => Auth::userId(),
            'uploaded_by_role'=> Auth::roleCode(),
        ];

        $keys = array_filter(array_keys($data), fn($k) => in_array($k, $insertCols));
        $keys = array_values($keys);
        $sqlCols = implode(',', $keys);
        $sqlVals = implode(',', array_map(fn($k)=>':'.$k, $keys));
        $insStmt = $this->db->prepare("INSERT INTO documents ($sqlCols) VALUES ($sqlVals)");
        $filteredData = array_intersect_key($data, array_flip($keys));
        $insStmt->execute($filteredData);
        $newId = (int)$this->db->lastInsertId();

        $this->json([
            'id'      => $newId,
            'nom'     => $file['name'],
            'url'     => BASE_URL . '/documents/view/' . $newId,
            'taille'  => $file['size'],
            'message' => 'Document uploadé avec succès',
        ]);
    }

    // API endpoint : liste des documents d'un PV
    public function listDocuments(string $pvId): void {
        Auth::requireLogin();
        $pvId = (int)$pvId;
        $stmt = $this->db->prepare(
            "SELECT d.id, d.nom_original, d.taille, d.mime_type,
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
            $doc['url']    = BASE_URL . '/documents/view/' . $doc['id'];
            $doc['taille_fmt'] = $this->formatSize((int)$doc['taille']);
            $doc['inline'] = in_array($doc['mime_type'], ['application/pdf','image/jpeg','image/png','image/gif']);
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
                    qsub.libelle as qualification_sub_libelle, qsub.code as qualification_sub_code
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
