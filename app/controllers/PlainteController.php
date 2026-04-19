<?php
/**
 * PlainteController — Gestion des plaintes au parquet
 *
 * Routes :
 *   GET  /plaintes                   → index()
 *   GET  /plaintes/create            → create()
 *   POST /plaintes/store             → store()
 *   GET  /plaintes/show/{id}         → show()
 *   GET  /plaintes/edit/{id}         → edit()
 *   POST /plaintes/update/{id}       → update()
 *   POST /plaintes/traiter/{id}      → traiter()
 *   POST /plaintes/classer/{id}      → classer()
 *   POST /plaintes/creer-pv/{id}     → creerPV()
 */
class PlainteController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $user    = Auth::currentUser();
        $search  = trim($_GET['q'] ?? '');
        $statut  = $_GET['statut'] ?? '';
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;

        $where  = [];
        $params = [];
        if ($search) {
            $where[]      = "(pl.numero_plainte LIKE :q OR pl.plaignant_nom LIKE :q OR pl.nature_plainte LIKE :q)";
            $params[':q'] = "%{$search}%";
        }
        if ($statut) {
            $where[]          = 'pl.statut = :statut';
            $params[':statut'] = $statut;
        }
        $wSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmtC = $this->db->prepare("SELECT COUNT(*) FROM plaintes pl $wSQL");
        $stmtC->execute($params);
        $total = (int)$stmtC->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt   = $this->db->prepare(
            "SELECT pl.*,
                    us.nom AS sub_nom, us.prenom AS sub_prenom
             FROM plaintes pl
             LEFT JOIN users us ON us.id = pl.substitut_id
             $wSQL
             ORDER BY pl.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute($params);
        $plaintes = $stmt->fetchAll();

        $totalPages = max(1, (int)ceil($total / $perPage));
        $flash      = $this->getFlash();
        $this->view('plaintes/index', compact('plaintes', 'total', 'page', 'totalPages', 'search', 'statut', 'flash', 'user'));
    }

    public function create(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'procureur', 'substitut_procureur', 'president']);
        $user       = Auth::currentUser();
        $substituts = $this->db->query(
            "SELECT u.* FROM users u JOIN roles r ON u.role_id=r.id
             WHERE r.code='substitut_procureur' AND u.actif=1 ORDER BY u.nom"
        )->fetchAll();
        $flash = $this->getFlash();
        $this->view('plaintes/create', compact('substituts', 'flash', 'user'));
    }

    public function store(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'procureur', 'substitut_procureur', 'president']);
        CSRF::check();

        // Vérification unicité numéro plainte
        $numPlainte = strtoupper(trim($_POST['numero_plainte'] ?? ''));
        if (!$numPlainte) {
            // Auto-génération
            $year       = date('Y');
            $lastId     = (int)($this->db->query("SELECT MAX(id) FROM plaintes")->fetchColumn() ?? 0);
            $numPlainte = 'PLT-' . $year . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
        } else {
            // Vérifier unicité
            $chk = $this->db->prepare("SELECT COUNT(*) FROM plaintes WHERE numero_plainte=?");
            $chk->execute([$numPlainte]);
            if ((int)$chk->fetchColumn() > 0) {
                $this->flash('error', "Le numéro de plainte {$numPlainte} existe déjà.");
                $this->redirect('/plaintes/create');
                return;
            }
        }

        // Pièce jointe optionnelle
        $piecePath = null;
        if (!empty($_FILES['piece_jointe']['name'])) {
            $piecePath = $this->handlePieceUpload($_FILES['piece_jointe']);
        }

        $stmt = $this->db->prepare(
            "INSERT INTO plaintes
                (numero_plainte, date_plainte, date_reception,
                 plaignant_nom, plaignant_prenom, plaignant_telephone, plaignant_adresse,
                 plaignant_email, plaignant_qualite,
                 mis_en_cause_nom, mis_en_cause_adresse,
                 nature_plainte, description_faits, lieu_faits, date_faits,
                 pieces_jointes, substitut_id, observations, statut, created_by)
             VALUES
                (:num, :dp, :dr,
                 :pnom, :pprenom, :ptel, :padr,
                 :pemail, :pqual,
                 :mecnom, :mecadr,
                 :nature, :desc, :lieu, :dfaits,
                 :pj, :sub, :obs, 'deposee', :by)"
        );
        $stmt->execute([
            ':num'    => $numPlainte,
            ':dp'     => $_POST['date_plainte'] ?? date('Y-m-d'),
            ':dr'     => $_POST['date_reception'] ?? date('Y-m-d'),
            ':pnom'   => strtoupper(trim($this->sanitize($_POST['plaignant_nom'] ?? ''))),
            ':pprenom'=> $this->sanitize($_POST['plaignant_prenom'] ?? ''),
            ':ptel'   => $this->sanitize($_POST['plaignant_telephone'] ?? ''),
            ':padr'   => $this->sanitize($_POST['plaignant_adresse'] ?? ''),
            ':pemail' => strtolower(trim($_POST['plaignant_email'] ?? '')),
            ':pqual'  => $_POST['plaignant_qualite'] ?? 'personne_physique',
            ':mecnom' => $this->sanitize($_POST['mis_en_cause_nom'] ?? ''),
            ':mecadr' => $this->sanitize($_POST['mis_en_cause_adresse'] ?? ''),
            ':nature' => $this->sanitize($_POST['nature_plainte'] ?? ''),
            ':desc'   => $this->sanitize($_POST['description_faits'] ?? ''),
            ':lieu'   => $this->sanitize($_POST['lieu_faits'] ?? ''),
            ':dfaits' => $_POST['date_faits'] ?: null,
            ':pj'     => $piecePath,
            ':sub'    => !empty($_POST['substitut_id']) ? (int)$_POST['substitut_id'] : null,
            ':obs'    => $this->sanitize($_POST['observations'] ?? ''),
            ':by'     => Auth::userId(),
        ]);
        $plainteId = (int)$this->db->lastInsertId();

        $this->flash('success', "Plainte {$numPlainte} enregistrée.");
        $this->redirect('/plaintes/show/' . $plainteId);
    }

    public function show(string $id): void
    {
        Auth::requireLogin();
        $plainte = $this->getPlainte((int)$id);
        if (!$plainte) { $this->redirect('/plaintes'); }
        $user  = Auth::currentUser();
        $flash = $this->getFlash();
        $substituts = $this->db->query(
            "SELECT u.* FROM users u JOIN roles r ON u.role_id=r.id
             WHERE r.code='substitut_procureur' AND u.actif=1 ORDER BY u.nom"
        )->fetchAll();
        $this->view('plaintes/show', compact('plainte', 'flash', 'user', 'substituts'));
    }

    public function edit(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'procureur', 'substitut_procureur', 'president']);
        $plainte = $this->getPlainte((int)$id);
        if (!$plainte) { $this->redirect('/plaintes'); }
        $user       = Auth::currentUser();
        $flash      = $this->getFlash();
        $substituts = $this->db->query(
            "SELECT u.* FROM users u JOIN roles r ON u.role_id=r.id
             WHERE r.code='substitut_procureur' AND u.actif=1 ORDER BY u.nom"
        )->fetchAll();
        $this->view('plaintes/edit', compact('plainte', 'substituts', 'flash', 'user'));
    }

    public function update(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'procureur', 'substitut_procureur', 'president']);
        CSRF::check();

        $plainte = $this->getPlainte((int)$id);
        if (!$plainte) { $this->redirect('/plaintes'); }

        $this->db->prepare(
            "UPDATE plaintes SET
                date_plainte=:dp, date_reception=:dr,
                plaignant_nom=:pnom, plaignant_prenom=:pprenom, plaignant_telephone=:ptel,
                plaignant_adresse=:padr, plaignant_email=:pemail, plaignant_qualite=:pqual,
                mis_en_cause_nom=:mecnom, mis_en_cause_adresse=:mecadr,
                nature_plainte=:nature, description_faits=:desc, lieu_faits=:lieu, date_faits=:dfaits,
                substitut_id=:sub, observations=:obs
             WHERE id=:id"
        )->execute([
            ':dp'     => $_POST['date_plainte'] ?? $plainte['date_plainte'],
            ':dr'     => $_POST['date_reception'] ?? $plainte['date_reception'],
            ':pnom'   => strtoupper(trim($this->sanitize($_POST['plaignant_nom'] ?? ''))),
            ':pprenom'=> $this->sanitize($_POST['plaignant_prenom'] ?? ''),
            ':ptel'   => $this->sanitize($_POST['plaignant_telephone'] ?? ''),
            ':padr'   => $this->sanitize($_POST['plaignant_adresse'] ?? ''),
            ':pemail' => strtolower(trim($_POST['plaignant_email'] ?? '')),
            ':pqual'  => $_POST['plaignant_qualite'] ?? 'personne_physique',
            ':mecnom' => $this->sanitize($_POST['mis_en_cause_nom'] ?? ''),
            ':mecadr' => $this->sanitize($_POST['mis_en_cause_adresse'] ?? ''),
            ':nature' => $this->sanitize($_POST['nature_plainte'] ?? ''),
            ':desc'   => $this->sanitize($_POST['description_faits'] ?? ''),
            ':lieu'   => $this->sanitize($_POST['lieu_faits'] ?? ''),
            ':dfaits' => $_POST['date_faits'] ?: null,
            ':sub'    => !empty($_POST['substitut_id']) ? (int)$_POST['substitut_id'] : null,
            ':obs'    => $this->sanitize($_POST['observations'] ?? ''),
            ':id'     => (int)$id,
        ]);

        $this->flash('success', 'Plainte mise à jour.');
        $this->redirect('/plaintes/show/' . $id);
    }

    public function traiter(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'procureur', 'substitut_procureur', 'president']);
        CSRF::check();

        $this->db->prepare(
            "UPDATE plaintes SET statut='en_examen', substitut_id=:sub WHERE id=:id"
        )->execute([
            ':sub' => !empty($_POST['substitut_id']) ? (int)$_POST['substitut_id'] : null,
            ':id'  => (int)$id,
        ]);

        $this->flash('success', 'Plainte mise en examen.');
        $this->redirect('/plaintes/show/' . $id);
    }

    public function classer(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'procureur', 'substitut_procureur', 'president']);
        CSRF::check();

        $motif   = $this->sanitize($_POST['motif_classement'] ?? '');
        $statut  = $_POST['statut_classement'] ?? 'classee';
        if (!in_array($statut, ['classee', 'irrecevable'])) $statut = 'classee';

        $this->db->prepare(
            "UPDATE plaintes SET statut=:s, motif_classement=:m WHERE id=:id"
        )->execute([':s' => $statut, ':m' => $motif, ':id' => (int)$id]);

        $this->flash('success', 'Plainte classée.');
        $this->redirect('/plaintes/show/' . $id);
    }

    /**
     * Crée un PV depuis une plainte
     */
    public function creerPV(string $id): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin', 'greffier', 'procureur', 'substitut_procureur', 'president']);
        CSRF::check();

        $plainte = $this->getPlainte((int)$id);
        if (!$plainte) { $this->redirect('/plaintes'); }

        $num      = new Numerotation($this->db);
        $numeroRG = $num->genererRG();

        $stmt = $this->db->prepare(
            "INSERT INTO pv (numero_pv, numero_rg, date_pv, date_reception, type_affaire,
              description_faits, statut, created_by)
             VALUES (:pv, :rg, :dpv, :drec, 'droit_commun_majeur', :desc, 'recu', :by)"
        );
        $stmt->execute([
            ':pv'   => 'PLT-' . $plainte['numero_plainte'],
            ':rg'   => $numeroRG,
            ':dpv'  => date('Y-m-d'),
            ':drec' => date('Y-m-d'),
            ':desc' => "Plainte de {$plainte['plaignant_nom']} {$plainte['plaignant_prenom']} — {$plainte['nature_plainte']}\n{$plainte['description_faits']}",
            ':by'   => Auth::userId(),
        ]);
        $pvId = (int)$this->db->lastInsertId();

        // Lier la plainte au PV
        $this->db->prepare("UPDATE plaintes SET pv_id=:pvid, statut='transmise_pv' WHERE id=:id")
            ->execute([':pvid' => $pvId, ':id' => (int)$id]);

        $this->flash('success', "PV {$numeroRG} créé depuis la plainte. Complétez les informations.");
        $this->redirect('/pv/show/' . $pvId);
    }

    // ─── Helpers privés ───────────────────────────────────────────────────
    private function getPlainte(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT pl.*,
                    us.nom AS sub_nom, us.prenom AS sub_prenom,
                    p.numero_rg AS pv_numero_rg
             FROM plaintes pl
             LEFT JOIN users us ON us.id = pl.substitut_id
             LEFT JOIN pv p     ON p.id  = pl.pv_id
             WHERE pl.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    private function handlePieceUpload(array $file): ?string
    {
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg',
                         'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxSize = 10 * 1024 * 1024; // 10 Mo

        if ($file['error'] !== UPLOAD_ERR_OK) return null;
        if (!in_array($file['type'], $allowedTypes)) return null;
        if ($file['size'] > $maxSize) return null;

        $uploadDir = ROOT_PATH . '/public/uploads/plaintes/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'plt_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . strtolower($ext);
        $dest     = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'uploads/plaintes/' . $filename;
        }
        return null;
    }
}
