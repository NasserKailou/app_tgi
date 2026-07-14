<?php
/**
 * SituationController — Situation périodique des PVs
 * Routes :
 *   GET  /situation/pv         → index()   (formulaire de filtre + tableau)
 *   GET  /api/situation/export → export()  (CSV)
 */
class SituationController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','procureur','substitut_procureur','president','greffier']);
        $user  = Auth::currentUser();
        $flash = $this->getFlash();

        // ── Paramètres de filtre ────────────────────────────────────────────
        $dateDebut  = $_GET['date_debut']  ?? date('Y-m-01');           // 1er du mois courant
        $dateFin    = $_GET['date_fin']    ?? date('Y-m-d');            // aujourd'hui
        $statut     = $_GET['statut']      ?? '';
        $modeP      = $_GET['mode_poursuite'] ?? '';
        $substitutId= (int)($_GET['substitut_id'] ?? 0);
        $uniteId    = (int)($_GET['unite_id'] ?? 0);
        $typeAff    = $_GET['type_affaire'] ?? '';

        // ── Construction de la requête ──────────────────────────────────────
        $where  = ["p.date_reception BETWEEN :dd AND :df"];
        $params = ['dd' => $dateDebut, 'df' => $dateFin];

        if ($statut)       { $where[] = "p.statut = :statut";          $params['statut']    = $statut; }
        if ($modeP)        { $where[] = "p.mode_poursuite = :mp";       $params['mp']        = $modeP; }
        if ($substitutId)  { $where[] = "p.substitut_id = :sub";        $params['sub']       = $substitutId; }
        if ($uniteId)      { $where[] = "p.unite_enquete_id = :ue";     $params['ue']        = $uniteId; }
        if ($typeAff)      { $where[] = "p.type_affaire = :type";       $params['type']      = $typeAff; }

        $whereSQL = 'WHERE ' . implode(' AND ', $where);

        // ── Liste des PVs ───────────────────────────────────────────────────
        $sql = "SELECT p.*,
                       ue.nom  AS unite_nom,
                       us.nom  AS sub_nom,  us.prenom AS sub_prenom,
                       inf.libelle AS infraction_libelle, inf.code AS infraction_code,
                       qsub.libelle AS qual_sub_libelle,
                       r.nom   AS region_nom
                FROM pv p
                LEFT JOIN unites_enquete ue  ON p.unite_enquete_id = ue.id
                LEFT JOIN users          us  ON p.substitut_id     = us.id
                LEFT JOIN infractions    inf ON p.infraction_id     = inf.id
                LEFT JOIN infractions   qsub ON p.qualification_substitut_id = qsub.id
                LEFT JOIN regions         r  ON p.region_id          = r.id
                $whereSQL
                ORDER BY p.date_reception DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $pvList = $stmt->fetchAll();

        // ── Statistiques de synthèse ─────────────────────────────────────────
        $total = count($pvList);

        // Par statut
        $byStatut = [];
        foreach ($pvList as $pv) { $byStatut[$pv['statut']] = ($byStatut[$pv['statut']] ?? 0) + 1; }

        // Par mode de poursuite
        $byMode = [];
        foreach ($pvList as $pv) {
            $m = $pv['mode_poursuite'] ?? 'non_défini';
            $byMode[$m] = ($byMode[$m] ?? 0) + 1;
        }

        // Par unité d'enquête
        $byUnite = [];
        foreach ($pvList as $pv) {
            $u = $pv['unite_nom'] ?? 'Non renseigné';
            $byUnite[$u] = ($byUnite[$u] ?? 0) + 1;
        }

        // Par substitut
        $bySub = [];
        foreach ($pvList as $pv) {
            $s = trim(($pv['sub_prenom']??'').' '.($pv['sub_nom']??''));
            if (!$s) $s = 'Non affecté';
            $bySub[$s] = ($bySub[$s] ?? 0) + 1;
        }

        // Délai moyen de réception → affectation (en jours)
        $delaisMoy = 0;
        $withAff   = 0;
        foreach ($pvList as $pv) {
            if (!empty($pv['date_affectation_substitut']) && !empty($pv['date_reception'])) {
                $diff = (strtotime($pv['date_affectation_substitut']) - strtotime($pv['date_reception'])) / 86400;
                if ($diff >= 0) { $delaisMoy += $diff; $withAff++; }
            }
        }
        $delaiMoyenAffectation = $withAff > 0 ? round($delaisMoy / $withAff, 1) : null;

        // ── Données pour les listes de filtre ──────────────────────────────
        $substituts = $this->db->query(
            "SELECT u.id, u.nom, u.prenom FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE r.code = 'substitut_procureur' AND u.actif = 1
             ORDER BY u.nom"
        )->fetchAll();

        $unites = $this->db->query("SELECT id, nom FROM unites_enquete WHERE actif=1 ORDER BY nom")->fetchAll();

        $this->view('situation/index', compact(
            'user','flash',
            'pvList','total',
            'dateDebut','dateFin','statut','modeP','substitutId','uniteId','typeAff',
            'byStatut','byMode','byUnite','bySub',
            'delaiMoyenAffectation',
            'substituts','unites'
        ));
    }

    /**
     * Export CSV de la situation courante
     */
    public function export(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','procureur','substitut_procureur','president','greffier']);

        $dateDebut  = $_GET['date_debut']  ?? date('Y-m-01');
        $dateFin    = $_GET['date_fin']    ?? date('Y-m-d');
        $statut     = $_GET['statut']      ?? '';
        $modeP      = $_GET['mode_poursuite'] ?? '';
        $substitutId= (int)($_GET['substitut_id'] ?? 0);
        $uniteId    = (int)($_GET['unite_id'] ?? 0);
        $typeAff    = $_GET['type_affaire'] ?? '';

        $where  = ["p.date_reception BETWEEN :dd AND :df"];
        $params = ['dd' => $dateDebut, 'df' => $dateFin];
        if ($statut)      { $where[] = "p.statut = :statut";        $params['statut'] = $statut; }
        if ($modeP)       { $where[] = "p.mode_poursuite = :mp";    $params['mp']     = $modeP; }
        if ($substitutId) { $where[] = "p.substitut_id = :sub";     $params['sub']    = $substitutId; }
        if ($uniteId)     { $where[] = "p.unite_enquete_id = :ue";  $params['ue']     = $uniteId; }
        if ($typeAff)     { $where[] = "p.type_affaire = :type";    $params['type']   = $typeAff; }

        $whereSQL = 'WHERE ' . implode(' AND ', $where);
        $sql = "SELECT p.numero_rg, p.numero_pv, p.date_pv, p.date_reception,
                       p.type_affaire, p.statut, p.mode_poursuite,
                       ue.nom AS unite_nom,
                       CONCAT(us.prenom,' ',us.nom) AS substitut,
                       inf.libelle AS infraction,
                       p.date_affectation_substitut,
                       p.lois_applicables,
                       r.nom AS region
                FROM pv p
                LEFT JOIN unites_enquete ue  ON p.unite_enquete_id = ue.id
                LEFT JOIN users          us  ON p.substitut_id     = us.id
                LEFT JOIN infractions    inf ON p.infraction_id     = inf.id
                LEFT JOIN regions         r  ON p.region_id        = r.id
                $whereSQL
                ORDER BY p.date_reception DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // ── Envoi CSV ───────────────────────────────────────────────────────
        $filename = 'situation_pv_' . $dateDebut . '_' . $dateFin . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');
        // BOM UTF-8 pour Excel
        fputs($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'N° RG','N° PV','Date PV','Date Réception',
            'Type Affaire','Statut','Mode Poursuite',
            'Unité Enquête','Substitut','Infraction',
            'Date Affectation','Lois Applicables','Région'
        ], ';');

        $statLabels = [
            'nouveau'                  => 'Nouveau',
            'recu'                     => 'Reçu',
            'en_traitement'            => 'En traitement',
            'classe'                   => 'Classé',
            'transfere'                => 'Transféré',
            'transfere_instruction'    => 'Transféré instruction',
            'transfere_jugement_direct'=> 'Audience directe',
        ];
        $mpLabels = [
            'RI' => 'Réquisitoire Introductif', 'CD' => 'Citation Directe',
            'FD' => 'Flagrant Délit',           'CRPC' => 'CRPC',
            'autre' => 'Autre',
        ];

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['numero_rg'],
                $r['numero_pv'],
                $r['date_pv']       ? date('d/m/Y', strtotime($r['date_pv']))       : '',
                $r['date_reception'] ? date('d/m/Y', strtotime($r['date_reception'])) : '',
                $r['type_affaire'],
                $statLabels[$r['statut']] ?? $r['statut'],
                $mpLabels[$r['mode_poursuite']] ?? ($r['mode_poursuite'] ?: 'Non défini'),
                $r['unite_nom'],
                $r['substitut'],
                $r['infraction'],
                $r['date_affectation_substitut'] ? date('d/m/Y', strtotime($r['date_affectation_substitut'])) : '',
                $r['lois_applicables'],
                $r['region'],
            ], ';');
        }
        fclose($out);
        exit;
    }

    /**
     * Situation périodique des dossiers CRPC
     * GET /situation/crpc
     */
    public function crpc(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','procureur','substitut_procureur','president','greffier']);
        $user  = Auth::currentUser();
        $flash = $this->getFlash();

        // ── Paramètres de filtre ──────────────────────────────────────────
        $dateDebut   = $_GET['date_debut']   ?? date('Y-m-01');
        $dateFin     = $_GET['date_fin']     ?? date('Y-m-d');
        $substitutId = (int)($_GET['substitut_id'] ?? 0);
        $statutFilter= $_GET['statut']       ?? '';
        $exportCsv   = ($_GET['export']      ?? '') === 'csv';

        // ── Requête principale ────────────────────────────────────────────
        $where  = ["p.date_reception BETWEEN :dd AND :df", "p.mode_poursuite = 'CRPC'"];
        $params = ['dd' => $dateDebut, 'df' => $dateFin];

        if ($substitutId)  { $where[] = "p.substitut_id = :sub";      $params['sub']    = $substitutId; }
        if ($statutFilter) { $where[] = "cd.statut = :statut";         $params['statut'] = $statutFilter; }

        $whereSQL = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT cd.*,
                       p.numero_rg  AS pv_numero_rg,
                       p.numero_pv  AS pv_numero_pv,
                       p.date_reception,
                       us.nom       AS sub_nom,
                       us.prenom    AS sub_prenom
                FROM crpc_dossiers cd
                JOIN pv p   ON cd.pv_id      = p.id
                LEFT JOIN users us ON p.substitut_id = us.id
                $whereSQL
                ORDER BY cd.date_mise_en_oeuvre DESC, p.date_reception DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $crpcList = $stmt->fetchAll();

        // ── Charger les personnes pour chaque dossier ────────────────────
        foreach ($crpcList as &$d) {
            $pStmt = $this->db->prepare(
                "SELECT * FROM crpc_personnes WHERE crpc_id = :id ORDER BY numero_ordre, id"
            );
            $pStmt->execute(['id' => $d['id']]);
            $d['personnes'] = $pStmt->fetchAll();
        }
        unset($d);

        // ── Statistiques ─────────────────────────────────────────────────
        $total    = count($crpcList);
        $byStatut = [];
        foreach ($crpcList as $d) {
            $byStatut[$d['statut']] = ($byStatut[$d['statut']] ?? 0) + 1;
        }

        // ── Nom du substitut filtré (pour affichage) ─────────────────────
        $substitutNom = '';
        if ($substitutId) {
            $sRow = $this->db->prepare("SELECT prenom, nom FROM users WHERE id = :id");
            $sRow->execute(['id' => $substitutId]);
            $sRow = $sRow->fetch();
            $substitutNom = $sRow ? trim($sRow['prenom'].' '.$sRow['nom']) : '';
        }

        // ── Export CSV ────────────────────────────────────────────────────
        if ($exportCsv) {
            $filename = 'situation_crpc_' . $dateDebut . '_' . $dateFin . '.csv';
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'N° RG','N° PV','Date CRPC','Personne(s)',
                'Qualification','Texte applicable','Peine prévue',
                'Avocat','Peine proposée','Sursis sub.','Amende proposée (FCFA)',
                'Date audience homo.','Homologation','Peine homologuée',
                'Sursis homo.','Amende homologuée (FCFA)',
                'Motif refus','Substitut','Statut'
            ], ';');
            $statMap = ['en_cours'=>'En cours','homologuee'=>'Homologuée','refusee'=>'Refusée','abandonnee'=>'Abandonnée'];
            foreach ($crpcList as $d) {
                $personnes  = implode(' / ', array_map(fn($p) => strtoupper($p['nom_prenom']), $d['personnes']));
                $homoLabel  = match((string)$d['homologation']) { '1'=>'Oui','0'=>'Non', default=>'En attente' };
                fputcsv($out, [
                    $d['pv_numero_rg'],
                    $d['pv_numero_pv'],
                    $d['date_mise_en_oeuvre'] ? date('d/m/Y', strtotime($d['date_mise_en_oeuvre'])) : '',
                    $personnes,
                    $d['qualification_faits'],
                    $d['texte_applicable'],
                    $d['peine_prevue'],
                    $d['assistance_avocat'] ? ('Oui — '.($d['nom_avocat']??'')) : ($d['renonciation_avocat'] ? 'Renonciation' : 'Non'),
                    $d['peine_emprisonnement'],
                    $d['sursis_substitut'] ? 'Oui' : 'Non',
                    $d['amende_proposee'] ?? '',
                    $d['date_audience_homologation'] ? date('d/m/Y', strtotime($d['date_audience_homologation'])) : '',
                    $homoLabel,
                    $d['peine_emprisonnement_homo'],
                    $d['sursis_homologue'] ? 'Oui' : 'Non',
                    $d['amende_homologuee'] ?? '',
                    $d['motif_refus_homologation'],
                    trim($d['sub_prenom'].' '.$d['sub_nom']),
                    $statMap[$d['statut']] ?? $d['statut'],
                ], ';');
            }
            fclose($out);
            exit;
        }

        // ── Listes de filtres ─────────────────────────────────────────────
        $substituts = $this->db->query(
            "SELECT u.id, u.nom, u.prenom FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE r.code = 'substitut_procureur' AND u.actif = 1
             ORDER BY u.nom"
        )->fetchAll();

        $this->view('situation/crpc', compact(
            'user','flash',
            'crpcList','total','byStatut',
            'dateDebut','dateFin','substitutId','substitutNom','statutFilter',
            'substituts'
        ));
    }
}
