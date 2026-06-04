<?php
/**
 * MandatController — Gestion des mandats de justice
 * Routes :
 *   GET  /mandats                    → index()
 *   GET  /mandats/create             → create()
 *   POST /mandats/store              → store()
 *   GET  /mandats/show/{id}          → show()
 *   GET  /mandats/edit/{id}          → edit()
 *   POST /mandats/update/{id}        → update()
 *   POST /mandats/delete/{id}        → delete()
 *   GET  /mandats/print/{id}         → printMandat()
 *   POST /mandats/update-statut/{id} → updateStatut()
 *   GET  /api/mandat-person-search   → apiSearch()
 */
class MandatController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // GET /mandats
    // ─────────────────────────────────────────────────────────────
    public function index(): void
    {
        Auth::requireLogin();
        $flash = $this->getFlash();
        $user  = Auth::currentUser();

        $search  = trim($_GET['q']      ?? '');
        $type    = trim($_GET['type']   ?? '');
        $statut  = trim($_GET['statut'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        $where  = ['1=1'];
        $params = [];
        if ($search) {
            $where[]           = '(m.numero LIKE :q OR m.motif LIKE :q2 OR m.nouveau_nom LIKE :q3)';
            $params[':q']      = "%$search%";
            $params[':q2']     = "%$search%";
            $params[':q3']     = "%$search%";
        }
        if ($type)   { $where[] = 'm.type_mandat = :type';   $params[':type']   = $type; }
        if ($statut) { $where[] = 'm.statut = :statut';      $params[':statut'] = $statut; }

        $whereStr = implode(' AND ', $where);

        $total = (int)$this->db->prepare("SELECT COUNT(*) FROM mandats m WHERE $whereStr")
                               ->execute($params) ? $this->db->prepare("SELECT COUNT(*) FROM mandats m WHERE $whereStr")->execute($params) : 0;
        $stmtC = $this->db->prepare("SELECT COUNT(*) FROM mandats m WHERE $whereStr");
        $stmtC->execute($params);
        $total = (int)$stmtC->fetchColumn();

        $stmt = $this->db->prepare(
            "SELECT m.*,
                    CONCAT(u.prenom,' ',u.nom) AS emetteur_nom,
                    d.numero_rg,
                    CONCAT(det.prenom,' ',det.nom) AS detenu_nom,
                    CONCAT(p.prenom,' ',p.nom)   AS partie_nom
             FROM mandats m
             LEFT JOIN users   u   ON m.emetteur_id = u.id
             LEFT JOIN dossiers d  ON m.dossier_id  = d.id
             LEFT JOIN detenus det ON m.detenu_id   = det.id
             LEFT JOIN parties p   ON m.partie_id   = p.id
             WHERE $whereStr
             ORDER BY m.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        $mandats     = $stmt->fetchAll();
        $totalPages  = max(1, (int)ceil($total / $perPage));

        $this->view('mandats/index', compact(
            'mandats','total','page','perPage','totalPages',
            'search','type','statut','flash','user'
        ));
    }

    // ─────────────────────────────────────────────────────────────
    // GET /mandats/create
    // ─────────────────────────────────────────────────────────────
    public function create(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','procureur','juge_instruction','president']);

        $dossiers  = $this->db->query(
            "SELECT id, numero_rg, objet FROM dossiers
             WHERE statut NOT IN ('juge','classe')
             ORDER BY numero_rg DESC LIMIT 200"
        )->fetchAll();

        $detenus = $this->db->query(
            "SELECT id, CONCAT(prenom,' ',nom,' — N°écrou: ',numero_ecrou) AS label
             FROM detenus WHERE statut='incarcere' ORDER BY nom, prenom LIMIT 500"
        )->fetchAll();

        $this->view('mandats/create', compact('dossiers','detenus'));
    }

    // ─────────────────────────────────────────────────────────────
// POST /mandats/store
// ─────────────────────────────────────────────────────────────
public function store(): void
{
    Auth::requireLogin();
    Auth::requireRole(['admin','procureur','juge_instruction','president']);
    CSRF::check();

    $type       = $_POST['type_mandat']      ?? '';
    $dossierId  = (int)($_POST['dossier_id'] ?? 0) ?: null;
    $motif      = $this->sanitize($_POST['motif'] ?? '');
    $infraction = $this->sanitize($_POST['infraction_libelle'] ?? '');
    $lieu       = $this->sanitize($_POST['lieu_execution']     ?? '');
    $dateEmis   = $_POST['date_emission']    ?? date('Y-m-d');
    $dateExp    = $_POST['date_expiration']  ?? null;
    $dateExhibe = $_POST['date_exhibe']      ?? null;
    $flagrant   = !empty($_POST['flagrant_delit']) ? 1 : 0;

    // Cible : détenu, partie, ou nouvelle personne
    $detenuId  = (int)($_POST['detenu_id']  ?? 0) ?: null;
    $partieId  = (int)($_POST['partie_id']  ?? 0) ?: null;
    $nvNom     = $this->sanitize($_POST['nouveau_nom']            ?? '');
    $nvPrenom  = $this->sanitize($_POST['nouveau_prenom']         ?? '');
    $nvDdn     = $_POST['nouveau_ddn']                            ?? null;
    $nvLieuN   = $this->sanitize($_POST['nouveau_lieu_naissance'] ?? '');
    $nvPere    = $this->sanitize($_POST['nouveau_pere']           ?? '');
    $nvMere    = $this->sanitize($_POST['nouveau_mere']           ?? '');
    $sexe      = in_array($_POST['sexe'] ?? '', ['M','F'], true) ? $_POST['sexe'] : null;
    $nvNat     = $this->sanitize($_POST['nouveau_nationalite']    ?? 'Nigérienne');
    $nvAddr    = $this->sanitize($_POST['nouveau_adresse']        ?? '');
    $nvProf    = $this->sanitize($_POST['nouveau_profession']     ?? '');
    $sitFam    = $this->sanitize($_POST['situation_famille']      ?? '');
    $servMil   = $this->sanitize($_POST['service_militaire']      ?? '');
    $condamn   = $this->sanitize($_POST['condamnations']          ?? '');

    if (!$type || !$motif) {
        $this->flash('error', 'Le type et le motif sont obligatoires.');
        $this->redirect('/mandats/create');
        return;
    }

    // Générer le numéro
    $num = (new Numerotation($this->db))->genererMandat();

    $stmt = $this->db->prepare(
        "INSERT INTO mandats
            (numero, type_mandat, dossier_id,
             detenu_id, partie_id,
             nouveau_nom, nouveau_prenom, nouveau_ddn, nouveau_lieu_naissance,
             nouveau_pere, nouveau_mere, sexe,
             nouveau_nationalite, nouveau_adresse, nouveau_profession,
             situation_famille, service_militaire, condamnations,
             motif, infraction_libelle, lieu_execution,
             emetteur_id, date_emission, date_expiration, date_exhibe, flagrant_delit,
             statut, created_by)
         VALUES
            (:num, :type, :dos,
             :det, :par,
             :nvn, :nvp, :nvd, :nvln,
             :nvpere, :nvmere, :sexe,
             :nvnat, :nvaddr, :nvprof,
             :sitfam, :servmil, :condamn,
             :motif, :inf, :lieu,
             :emit, :demis, :dexp, :dexhibe, :flagrant,
             'emis', :cb)"
    );
    $stmt->execute([
        ':num'      => $num,
        ':type'     => $type,
        ':dos'      => $dossierId,
        ':det'      => $detenuId,
        ':par'      => $partieId,
        ':nvn'      => $nvNom    ?: null,
        ':nvp'      => $nvPrenom ?: null,
        ':nvd'      => $nvDdn    ?: null,
        ':nvln'     => $nvLieuN  ?: null,
        ':nvpere'   => $nvPere   ?: null,
        ':nvmere'   => $nvMere   ?: null,
        ':sexe'     => $sexe,
        ':nvnat'    => $nvNat    ?: 'Nigérienne',
        ':nvaddr'   => $nvAddr   ?: null,
        ':nvprof'   => $nvProf   ?: null,
        ':sitfam'   => $sitFam   ?: null,
        ':servmil'  => $servMil  ?: null,
        ':condamn'  => $condamn  ?: null,
        ':motif'    => $motif,
        ':inf'      => $infraction ?: null,
        ':lieu'     => $lieu       ?: null,
        ':emit'     => Auth::userId(),
        ':demis'    => $dateEmis,
        ':dexp'     => $dateExp    ?: null,
        ':dexhibe'  => $dateExhibe ?: null,
        ':flagrant' => $flagrant,
        ':cb'       => Auth::userId(),
    ]);
    $newId = (int)$this->db->lastInsertId();

    // Si nouveau + dossier → créer automatiquement une partie dans le dossier
    if ($dossierId && $nvNom && !$detenuId && !$partieId) {
        $this->db->prepare(
            "INSERT INTO parties (dossier_id,type_partie,nom,prenom,date_naissance,nationalite,profession,adresse)
             VALUES (:dos,'prevenu',:n,:p,:d,:nat,:prof,:addr)"
        )->execute([
            ':dos'  => $dossierId,
            ':n'    => $nvNom,
            ':p'    => $nvPrenom ?: null,
            ':d'    => $nvDdn    ?: null,
            ':nat'  => $nvNat,
            ':prof' => $nvProf   ?: null,
            ':addr' => $nvAddr   ?: null,
        ]);
    }

    $this->flash('success', "Mandat $num émis avec succès.");
    $this->redirect('/mandats/show/' . $newId);
}

    // ─────────────────────────────────────────────────────────────
    // GET /mandats/show/{id}
    // ─────────────────────────────────────────────────────────────
    public function show(string $id): void
    {
        Auth::requireLogin();
        $flash  = $this->getFlash();
        $mandat = $this->getMandatDetail((int)$id);
        if (!$mandat) { $this->redirect('/mandats'); return; }
        $this->view('mandats/show', compact('mandat','flash'));
    }

    // ─────────────────────────────────────────────────────────────
    // GET /mandats/edit/{id}  — Formulaire de modification
    // ─────────────────────────────────────────────────────────────
    public function edit(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','procureur','juge_instruction','president']);

        $id     = (int)$id;
        $mandat = $this->getMandatDetail($id);
        if (!$mandat) {
            $this->flash('error', 'Mandat introuvable.');
            $this->redirect('/mandats');
            return;
        }

        // Droit de modifier : admin/procureur OU émetteur du mandat
        $user    = Auth::currentUser();
        $role    = $user['role_code'] ?? '';
        $isAdmin = in_array($role, ['admin','procureur'], true);
        $isOwner = (int)$mandat['emetteur_id'] === (int)($user['id'] ?? 0);
        if (!$isAdmin && !$isOwner) {
            $this->flash('error', "Vous n'avez pas le droit de modifier ce mandat.");
            $this->redirect('/mandats/show/' . $id);
            return;
        }

        // Blocage si statut figé
        if (in_array($mandat['statut'], ['execute','revoque','annule','expire'], true)) {
            $this->flash('error', "Impossible de modifier un mandat dont le statut est « {$mandat['statut']} ».");
            $this->redirect('/mandats/show/' . $id);
            return;
        }

        $dossiers = $this->db->query(
            "SELECT id, numero_rg, objet FROM dossiers
             WHERE statut NOT IN ('juge','classe')
             ORDER BY numero_rg DESC LIMIT 200"
        )->fetchAll();

        $detenus = $this->db->query(
            "SELECT id, CONCAT(prenom,' ',nom,' — N°écrou: ',numero_ecrou) AS label
             FROM detenus WHERE statut='incarcere' ORDER BY nom, prenom LIMIT 500"
        )->fetchAll();

        $flash = $this->getFlash();
        $this->view('mandats/edit', compact('mandat','dossiers','detenus','flash'));
    }

    // ─────────────────────────────────────────────────────────────
// POST /mandats/update/{id}
// ─────────────────────────────────────────────────────────────
public function update(string $id): void
{
    Auth::requireLogin();
    Auth::requireRole(['admin','procureur','juge_instruction','president']);
    CSRF::check();

    $id     = (int)$id;
    $mandat = $this->getMandatDetail($id);
    if (!$mandat) {
        $this->flash('error', 'Mandat introuvable.');
        $this->redirect('/mandats');
        return;
    }

    $user    = Auth::currentUser();
    $role    = $user['role_code'] ?? '';
    $isAdmin = in_array($role, ['admin','procureur'], true);
    $isOwner = (int)$mandat['emetteur_id'] === (int)($user['id'] ?? 0);
    if (!$isAdmin && !$isOwner) {
        $this->flash('error', "Vous n'avez pas le droit de modifier ce mandat.");
        $this->redirect('/mandats/show/' . $id);
        return;
    }
    if (in_array($mandat['statut'], ['execute','revoque','annule','expire'], true)) {
        $this->flash('error', "Impossible de modifier un mandat dont le statut est « {$mandat['statut']} ».");
        $this->redirect('/mandats/show/' . $id);
        return;
    }

    $type       = $_POST['type_mandat']      ?? $mandat['type_mandat'];
    $dossierId  = (int)($_POST['dossier_id'] ?? 0) ?: null;
    $motif      = $this->sanitize($_POST['motif'] ?? '');
    $infraction = $this->sanitize($_POST['infraction_libelle'] ?? '');
    $lieu       = $this->sanitize($_POST['lieu_execution']     ?? '');
    $dateEmis   = $_POST['date_emission']    ?? $mandat['date_emission'];
    $dateExp    = $_POST['date_expiration']  ?? null;
    $dateExhibe = $_POST['date_exhibe']      ?? null;
    $flagrant   = !empty($_POST['flagrant_delit']) ? 1 : 0;

    $detenuId  = (int)($_POST['detenu_id']  ?? 0) ?: null;
    $partieId  = (int)($_POST['partie_id']  ?? 0) ?: null;
    $nvNom     = $this->sanitize($_POST['nouveau_nom']            ?? '');
    $nvPrenom  = $this->sanitize($_POST['nouveau_prenom']         ?? '');
    $nvDdn     = $_POST['nouveau_ddn']                            ?? null;
    $nvLieuN   = $this->sanitize($_POST['nouveau_lieu_naissance'] ?? '');
    $nvPere    = $this->sanitize($_POST['nouveau_pere']           ?? '');
    $nvMere    = $this->sanitize($_POST['nouveau_mere']           ?? '');
    $sexe      = in_array($_POST['sexe'] ?? '', ['M','F'], true) ? $_POST['sexe'] : null;
    $nvNat     = $this->sanitize($_POST['nouveau_nationalite']    ?? 'Nigérienne');
    $nvAddr    = $this->sanitize($_POST['nouveau_adresse']        ?? '');
    $nvProf    = $this->sanitize($_POST['nouveau_profession']     ?? '');
    $sitFam    = $this->sanitize($_POST['situation_famille']      ?? '');
    $servMil   = $this->sanitize($_POST['service_militaire']      ?? '');
    $condamn   = $this->sanitize($_POST['condamnations']          ?? '');

    if (!$type || !$motif) {
        $this->flash('error', 'Le type et le motif sont obligatoires.');
        $this->redirect('/mandats/edit/' . $id);
        return;
    }

    $stmt = $this->db->prepare(
        "UPDATE mandats SET
            type_mandat            = :type,
            dossier_id             = :dos,
            detenu_id              = :det,
            partie_id              = :par,
            nouveau_nom            = :nvn,
            nouveau_prenom         = :nvp,
            nouveau_ddn            = :nvd,
            nouveau_lieu_naissance = :nvln,
            nouveau_pere           = :nvpere,
            nouveau_mere           = :nvmere,
            sexe                   = :sexe,
            nouveau_nationalite    = :nvnat,
            nouveau_adresse        = :nvaddr,
            nouveau_profession     = :nvprof,
            situation_famille      = :sitfam,
            service_militaire      = :servmil,
            condamnations          = :condamn,
            motif                  = :motif,
            infraction_libelle     = :inf,
            lieu_execution         = :lieu,
            date_emission          = :demis,
            date_expiration        = :dexp,
            date_exhibe            = :dexhibe,
            flagrant_delit         = :flagrant,
            updated_at             = NOW()
         WHERE id = :id"
    );
    $stmt->execute([
        ':type'     => $type,
        ':dos'      => $dossierId,
        ':det'      => $detenuId,
        ':par'      => $partieId,
        ':nvn'      => $nvNom    ?: null,
        ':nvp'      => $nvPrenom ?: null,
        ':nvd'      => $nvDdn    ?: null,
        ':nvln'     => $nvLieuN  ?: null,
        ':nvpere'   => $nvPere   ?: null,
        ':nvmere'   => $nvMere   ?: null,
        ':sexe'     => $sexe,
        ':nvnat'    => $nvNat    ?: 'Nigérienne',
        ':nvaddr'   => $nvAddr   ?: null,
        ':nvprof'   => $nvProf   ?: null,
        ':sitfam'   => $sitFam   ?: null,
        ':servmil'  => $servMil  ?: null,
        ':condamn'  => $condamn  ?: null,
        ':motif'    => $motif,
        ':inf'      => $infraction ?: null,
        ':lieu'     => $lieu       ?: null,
        ':demis'    => $dateEmis,
        ':dexp'     => $dateExp    ?: null,
        ':dexhibe'  => $dateExhibe ?: null,
        ':flagrant' => $flagrant,
        ':id'       => $id,
    ]);

    $this->flash('success', "Mandat {$mandat['numero']} mis à jour avec succès.");
    $this->redirect('/mandats/show/' . $id);
}

    // ─────────────────────────────────────────────────────────────
    // POST /mandats/delete/{id}  — Suppression
    // ─────────────────────────────────────────────────────────────
    public function delete(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','procureur']);
        CSRF::check();

        $id   = (int)$id;
        $stmt = $this->db->prepare("SELECT id, numero, statut FROM mandats WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $mandat = $stmt->fetch();

        if (!$mandat) {
            $this->flash('error', 'Mandat introuvable.');
            $this->redirect('/mandats');
            return;
        }

        // Un mandat exécuté ne peut pas être supprimé (trace légale)
        if ($mandat['statut'] === 'execute') {
            $this->flash('error', "Impossible de supprimer un mandat déjà exécuté.");
            $this->redirect('/mandats/show/' . $id);
            return;
        }

        try {
            $this->db->prepare("DELETE FROM mandats WHERE id = :id")
                     ->execute([':id' => $id]);
            $this->flash('success', "Mandat {$mandat['numero']} supprimé avec succès.");
        } catch (Throwable $e) {
            error_log('[MandatController::delete] ' . $e->getMessage());
            $this->flash('error', "Erreur lors de la suppression : " . $e->getMessage());
        }

        $this->redirect('/mandats');
    }

    // ─────────────────────────────────────────────────────────────
    // GET /mandats/print/{id}  — Document imprimable
    // ─────────────────────────────────────────────────────────────
    public function printMandat(string $id): void
    {
        Auth::requireLogin();
        $mandat = $this->getMandatDetail((int)$id);
        if (!$mandat) { http_response_code(404); exit('Mandat introuvable.'); }
        $this->view('mandats/print', compact('mandat'), 'print');
    }

    // ─────────────────────────────────────────────────────────────
    // POST /mandats/update-statut/{id}
    // ─────────────────────────────────────────────────────────────
    public function updateStatut(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','procureur','greffier','juge_instruction']);
        CSRF::check();

        $id     = (int)$id;
        $statut = $_POST['statut']        ?? '';
        $obs    = $this->sanitize($_POST['observations'] ?? '');
        $exec   = $this->sanitize($_POST['executant_nom'] ?? '');
        $dExec  = $_POST['date_execution'] ?? null;

        $validStatuts = ['emis','signifie','execute','annule','expire'];
        if (!in_array($statut, $validStatuts, true)) {
            $this->flash('error','Statut invalide.'); $this->redirect('/mandats/show/'.$id); return;
        }

        $this->db->prepare(
            "UPDATE mandats SET statut=:s, observations=:o, executant_nom=:e,
                               date_execution=:d, updated_at=NOW()
             WHERE id=:id"
        )->execute([
            ':s'  => $statut,
            ':o'  => $obs    ?: null,
            ':e'  => $exec   ?: null,
            ':d'  => ($dExec ?: null),
            ':id' => $id,
        ]);
        $this->flash('success', 'Statut du mandat mis à jour.');
        $this->redirect('/mandats/show/' . $id);
    }

    // ─────────────────────────────────────────────────────────────
    // GET /api/mandat-person-search?q=...&type=detenu|partie
    // ─────────────────────────────────────────────────────────────
    public function apiSearch(): void
    {
        Auth::requireLogin();
        $q    = trim($_GET['q']    ?? '');
        $type = trim($_GET['type'] ?? 'detenu');

        if (strlen($q) < 2) { $this->json([]); return; }

        if ($type === 'detenu') {
            $stmt = $this->db->prepare(
                "SELECT id,
                        CONCAT(prenom,' ',nom) AS label,
                        numero_ecrou, statut
                 FROM detenus
                 WHERE nom LIKE :q OR prenom LIKE :q2 OR numero_ecrou LIKE :q3
                 ORDER BY nom LIMIT 20"
            );
            $stmt->execute([':q'=>"%$q%",':q2'=>"%$q%",':q3'=>"%$q%"]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT p.id,
                        CONCAT(p.prenom,' ',p.nom,' (RG: ',d.numero_rg,')') AS label,
                        p.type_partie AS statut,
                        d.numero_rg
                 FROM parties p
                 JOIN dossiers d ON p.dossier_id = d.id
                 WHERE p.nom LIKE :q OR p.prenom LIKE :q2
                 ORDER BY p.nom LIMIT 20"
            );
            $stmt->execute([':q'=>"%$q%",':q2'=>"%$q%"]);
        }
        $this->json($stmt->fetchAll());
    }

    // ─────────────────────────────────────────────────────────────
    // Helper
    // ─────────────────────────────────────────────────────────────
    private function getMandatDetail(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT m.*,
                    CONCAT(u.prenom,' ',u.nom)    AS emetteur_nom,
                    r.code                        AS emetteur_role,
                    r.libelle                     AS emetteur_role_libelle,
                    d.numero_rg, d.objet          AS dossier_objet,
                    CONCAT(det.prenom,' ',det.nom) AS detenu_label,
                    det.numero_ecrou,
                    CONCAT(p.prenom,' ',p.nom)    AS partie_label,
                    p.type_partie
             FROM mandats m
             LEFT JOIN users   u   ON m.emetteur_id = u.id
             LEFT JOIN roles   r   ON u.role_id     = r.id
             LEFT JOIN dossiers d  ON m.dossier_id  = d.id
             LEFT JOIN detenus det ON m.detenu_id   = det.id
             LEFT JOIN parties p   ON m.partie_id   = p.id
             WHERE m.id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }
}
