<?php
/**
 * DashboardController — Tableau de bord TGI-NY
 * v2.3 : graphique PV par mois mis à jour, génération de rapports,
 *        filtrage par rôle, sécurité renforcée
 */
class DashboardController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        $flash = $this->getFlash();
        $user  = Auth::currentUser();

        // Stats rapides (filtrées par rôle)
        $stats        = $this->getStats($user);
        $alerteHelper = new AlerteHelper($this->db);
        $nbAlertes    = $alerteHelper->countUnread($user['id']);

        // Derniers PV (filtrés par rôle)
        $pvWhere  = '';
        $pvParams = [];
        if ($user['role_code'] === 'substitut_procureur') {
            $pvWhere  = 'WHERE p.substitut_id = ?';
            $pvParams = [$user['id']];
        }
        $derniersPV = $this->db->prepare(
            "SELECT p.*, u.nom as unite_nom
             FROM pv p
             LEFT JOIN unites_enquete u ON p.unite_enquete_id = u.id
             $pvWhere
             ORDER BY p.created_at DESC LIMIT 10"
        );
        $derniersPV->execute($pvParams);
        $derniersPV = $derniersPV->fetchAll();

        // Prochaines audiences
        $prochainesAudiences = $this->db->query(
            "SELECT a.*, d.numero_rg, s.nom as salle_nom,
                    u.nom as president_nom, u.prenom as president_prenom
             FROM audiences a
             JOIN dossiers d ON a.dossier_id = d.id
             LEFT JOIN salles_audience s ON a.salle_id = s.id
             LEFT JOIN users u ON a.president_id = u.id
             WHERE a.statut = 'planifiee' AND a.date_audience >= NOW()
             ORDER BY a.date_audience ASC LIMIT 10"
        )->fetchAll();

        // Derniers rapports générés
        $dernierRapports = [];
        try {
            $rStmt = $this->db->prepare(
                "SELECT r.*, u.nom as generateur_nom
                 FROM rapports r LEFT JOIN users u ON r.genere_par = u.id
                 ORDER BY r.created_at DESC LIMIT 5"
            );
            $rStmt->execute();
            $dernierRapports = $rStmt->fetchAll();
        } catch (\Exception $e) {}

        $this->view('dashboard/index', compact(
            'stats','derniersPV','prochainesAudiences',
            'nbAlertes','flash','user','dernierRapports'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GÉNÉRER UN RAPPORT
    // ─────────────────────────────────────────────────────────────────────────
    public function genererRapport(): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','procureur','president','greffier']);
        CSRF::check();

        $type      = $_POST['type_rapport'] ?? 'quotidien';
        $dateDebut = $_POST['date_debut'] ?? date('Y-m-d');
        $dateFin   = $_POST['date_fin']   ?? date('Y-m-d');

        // Valider les dates
        if ($dateDebut > $dateFin) {
            $this->flash('error', 'La date de début doit être antérieure à la date de fin.');
            $this->redirect('/dashboard');
            return;
        }

        $data = $this->buildReportData($dateDebut, $dateFin);

        $titre = match($type) {
            'quotidien'    => 'Rapport quotidien du ' . date('d/m/Y', strtotime($dateDebut)),
            'hebdomadaire' => 'Rapport hebdomadaire du ' . date('d/m/Y', strtotime($dateDebut)) . ' au ' . date('d/m/Y', strtotime($dateFin)),
            'mensuel'      => 'Rapport mensuel — ' . strftime('%B %Y', strtotime($dateDebut)),
            'annuel'       => 'Rapport annuel ' . date('Y', strtotime($dateDebut)),
            default        => 'Rapport personnalisé du ' . $dateDebut . ' au ' . $dateFin,
        };

        // Sauvegarder le rapport
        $this->db->prepare(
            "INSERT INTO rapports (type, titre, date_debut, date_fin, contenu_json, genere_par)
             VALUES (?,?,?,?,?,?)"
        )->execute([
            $type,
            $titre,
            $dateDebut,
            $dateFin,
            json_encode($data, JSON_UNESCAPED_UNICODE),
            Auth::userId(),
        ]);
        $rapportId = (int)$this->db->lastInsertId();

        // Générer le HTML du rapport pour l'affichage / impression
        $this->flash('success', "Rapport « {$titre} » généré avec succès.");
        $this->redirect('/dashboard/rapport/' . $rapportId);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VOIR UN RAPPORT
    // ─────────────────────────────────────────────────────────────────────────
    public function voirRapport(string $id): void {
        Auth::requireLogin();
        Auth::requireRole(['admin','procureur','president','greffier']);

        $stmt = $this->db->prepare(
            "SELECT r.*, u.nom as generateur_nom, u.prenom as generateur_prenom
             FROM rapports r LEFT JOIN users u ON r.genere_par = u.id
             WHERE r.id = ?"
        );
        $stmt->execute([(int)$id]);
        $rapport = $stmt->fetch();
        if (!$rapport) {
            $this->flash('error', 'Rapport introuvable.');
            $this->redirect('/dashboard');
            return;
        }

        $data = json_decode($rapport['contenu_json'], true) ?: [];
        $user = Auth::currentUser();
        $flash = $this->getFlash();
        $this->view('dashboard/rapport', compact('rapport','data','user','flash'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DONNÉES DU RAPPORT
    // ─────────────────────────────────────────────────────────────────────────
    private function buildReportData(string $dateDebut, string $dateFin): array {
        $data = [];

        // PVs reçus sur la période
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as total,
                    SUM(est_antiterroriste) as antiterro,
                    COUNT(CASE WHEN type_affaire='penale' THEN 1 END) as penale,
                    COUNT(CASE WHEN type_affaire='civile' THEN 1 END) as civile,
                    COUNT(CASE WHEN type_affaire='commerciale' THEN 1 END) as commerciale,
                    COUNT(CASE WHEN statut='classe' THEN 1 END) as classes
             FROM pv WHERE date_reception BETWEEN ? AND ?"
        );
        $stmt->execute([$dateDebut, $dateFin]);
        $data['pv'] = $stmt->fetch();

        // Dossiers créés
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as total,
                    COUNT(CASE WHEN statut='juge' THEN 1 END) as juges,
                    COUNT(CASE WHEN statut='classe' THEN 1 END) as classes
             FROM dossiers WHERE date_enregistrement BETWEEN ? AND ?"
        );
        $stmt->execute([$dateDebut, $dateFin]);
        $data['dossiers'] = $stmt->fetch();

        // Audiences
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as total,
                    COUNT(CASE WHEN statut='tenue' THEN 1 END) as tenues
             FROM audiences WHERE date_audience BETWEEN ? AND ?"
        );
        $stmt->execute([$dateDebut, $dateFin]);
        $data['audiences'] = $stmt->fetch();

        // Mises en cause
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as total,
                    COUNT(CASE WHEN decision_substitut='poursuivi' THEN 1 END) as poursuivis,
                    COUNT(CASE WHEN decision_substitut='non_poursuivi' THEN 1 END) as non_poursuivis,
                    COUNT(CASE WHEN sexe='F' THEN 1 END) as femmes
             FROM mises_en_cause WHERE created_at BETWEEN ? AND ?"
        );
        $stmt->execute([$dateDebut . ' 00:00:00', $dateFin . ' 23:59:59']);
        $data['mec'] = $stmt->fetch();

        // Infractions les plus fréquentes
        $stmt = $this->db->prepare(
            "SELECT i.libelle, COUNT(*) as nb
             FROM pv_infractions pi
             JOIN infractions i ON i.id = pi.infraction_id
             JOIN pv p ON p.id = pi.pv_id
             WHERE p.date_reception BETWEEN ? AND ?
             GROUP BY i.id ORDER BY nb DESC LIMIT 5"
        );
        try {
            $stmt->execute([$dateDebut, $dateFin]);
            $data['top_infractions'] = $stmt->fetchAll();
        } catch (\Exception $e) {
            // Fallback : utiliser infraction_id du PV
            $stmt2 = $this->db->prepare(
                "SELECT i.libelle, COUNT(*) as nb
                 FROM pv p JOIN infractions i ON i.id = p.infraction_id
                 WHERE p.date_reception BETWEEN ? AND ? AND p.infraction_id IS NOT NULL
                 GROUP BY i.id ORDER BY nb DESC LIMIT 5"
            );
            $stmt2->execute([$dateDebut, $dateFin]);
            $data['top_infractions'] = $stmt2->fetchAll();
        }

        // Détails par jour
        $stmt = $this->db->prepare(
            "SELECT DATE(date_reception) as jour, COUNT(*) as nb
             FROM pv WHERE date_reception BETWEEN ? AND ?
             GROUP BY DATE(date_reception) ORDER BY jour"
        );
        $stmt->execute([$dateDebut, $dateFin]);
        $data['pv_par_jour'] = $stmt->fetchAll();

        $data['date_debut']   = $dateDebut;
        $data['date_fin']     = $dateFin;
        $data['genere_le']    = date('Y-m-d H:i:s');

        return $data;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STATS (avec filtrage par rôle)
    // ─────────────────────────────────────────────────────────────────────────
    private function getStats(?array $user = null): array {
        $userId   = $user['id'] ?? null;
        $roleCode = $user['role_code'] ?? '';

        // Filtre pour substitut
        $pvFilter = ($roleCode === 'substitut_procureur' && $userId)
            ? "AND substitut_id = {$userId}" : '';
        $dossierFilter = ($roleCode === 'substitut_procureur' && $userId)
            ? "AND substitut_id = {$userId}" : '';

        // PVs reçus ce mois
        $pvMois = (int)$this->db->query(
            "SELECT COUNT(*) FROM pv
             WHERE YEAR(date_reception)=YEAR(CURDATE()) AND MONTH(date_reception)=MONTH(CURDATE()) $pvFilter"
        )->fetchColumn();

        // Dossiers en cours
        $dossiersEnCours = (int)$this->db->query(
            "SELECT COUNT(*) FROM dossiers WHERE statut NOT IN ('juge','classe') $dossierFilter"
        )->fetchColumn();

        // Audiences planifiées cette semaine
        $audiencesSemaine = (int)$this->db->query(
            "SELECT COUNT(*) FROM audiences WHERE statut='planifiee' AND YEARWEEK(date_audience,1)=YEARWEEK(CURDATE(),1)"
        )->fetchColumn();

        // Population carcérale
        $population = (int)$this->db->query(
            "SELECT COUNT(*) FROM detenus WHERE statut='incarcere'"
        )->fetchColumn();

        // PVs par statut
        $pvStatutsSQL = $pvFilter
            ? "SELECT statut, COUNT(*) as nb FROM pv WHERE " . ltrim($pvFilter, 'AND ') . " GROUP BY statut"
            : "SELECT statut, COUNT(*) as nb FROM pv GROUP BY statut";
        $pvStatuts = $this->db->query($pvStatutsSQL)->fetchAll();

        // Dossiers par type
        $dossierTypes = $this->db->query(
            "SELECT type_affaire, COUNT(*) as nb FROM dossiers GROUP BY type_affaire"
        )->fetchAll();

        // Population par type de détention
        $detentionTypes = $this->db->query(
            "SELECT type_detention, COUNT(*) as nb FROM detenus WHERE statut='incarcere' GROUP BY type_detention"
        )->fetchAll();

        // PVs par mois (12 derniers mois) — inclut antiterroriste et qualification substitut
        $pvParMoisSQL = "SELECT
                DATE_FORMAT(date_reception,'%Y-%m') as mois,
                type_affaire,
                COUNT(*) as nb,
                SUM(est_antiterroriste) as nb_antiterro,
                SUM(CASE WHEN qualification_substitut_id IS NOT NULL THEN 1 ELSE 0 END) as nb_qualifies
             FROM pv
             WHERE date_reception >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
        if ($pvFilter) {
            $pvParMoisSQL .= " AND " . ltrim($pvFilter, 'AND ');
        }
        $pvParMoisSQL .= " GROUP BY DATE_FORMAT(date_reception,'%Y-%m'), type_affaire ORDER BY mois";
        try {
            $pvParMois = $this->db->query($pvParMoisSQL)->fetchAll();
        } catch (\Exception $e) {
            $pvParMois = $this->db->query(
                "SELECT DATE_FORMAT(date_reception,'%Y-%m') as mois, type_affaire, COUNT(*) as nb
                 FROM pv WHERE date_reception >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                 GROUP BY DATE_FORMAT(date_reception,'%Y-%m'), type_affaire ORDER BY mois"
            )->fetchAll();
        }

        // Alertes non lues
        $nbAlertesTotal = (int)$this->db->query("SELECT COUNT(*) FROM alertes WHERE est_lue=0")->fetchColumn();

        // ── Nouveaux modules ──────────────────────────────────────────
        $nbAvocats = 0;
        try { $nbAvocats = (int)$this->db->query("SELECT COUNT(*) FROM avocats WHERE statut='actif'")->fetchColumn(); } catch (\Exception $e) {}

        $nbControlesActifs = 0;
        try { $nbControlesActifs = (int)$this->db->query("SELECT COUNT(*) FROM controles_judiciaires WHERE statut='actif'")->fetchColumn(); } catch (\Exception $e) {}

        $nbExpertisesEnCours = 0;
        try { $nbExpertisesEnCours = (int)$this->db->query("SELECT COUNT(*) FROM expertises_judiciaires WHERE statut IN ('ordonnee','en_cours')")->fetchColumn(); } catch (\Exception $e) {}

        $nbScelles = 0;
        try { $nbScelles = (int)$this->db->query("SELECT COUNT(*) FROM scelles WHERE statut IN ('depose','inventorie')")->fetchColumn(); } catch (\Exception $e) {}

        $nbVoiesRecours = 0;
        try { $nbVoiesRecours = (int)$this->db->query("SELECT COUNT(*) FROM voies_recours WHERE statut IN ('declare','instruit')")->fetchColumn(); } catch (\Exception $e) {}

        $nbOrdonnances = 0;
        try { $nbOrdonnances = (int)$this->db->query("SELECT COUNT(*) FROM ordonnances WHERE YEAR(date_ordonnance)=YEAR(CURDATE())")->fetchColumn(); } catch (\Exception $e) {}

        // Dossiers par statut
        $dossierStatuts = $this->db->query(
            "SELECT statut, COUNT(*) as nb FROM dossiers GROUP BY statut ORDER BY nb DESC"
        )->fetchAll();

        // Jugements ce mois
        $jugementsMois = 0;
        try {
            $jugementsMois = (int)$this->db->query(
                "SELECT COUNT(*) FROM jugements WHERE YEAR(date_jugement)=YEAR(CURDATE()) AND MONTH(date_jugement)=MONTH(CURDATE())"
            )->fetchColumn();
        } catch (\Exception $e) {}

        // Top 5 infractions (depuis pv_infractions ou infraction_id)
        $topInfractions = [];
        try {
            $topInfractions = $this->db->query(
                "SELECT i.libelle, COUNT(*) as nb
                 FROM pv_infractions pi JOIN infractions i ON i.id = pi.infraction_id
                 WHERE pi.type='unite'
                 GROUP BY i.id ORDER BY nb DESC LIMIT 5"
            )->fetchAll();
        } catch (\Exception $e) {
            try {
                $topInfractions = $this->db->query(
                    "SELECT i.libelle, COUNT(*) as nb FROM pv p JOIN infractions i ON i.id=p.infraction_id
                     WHERE p.infraction_id IS NOT NULL GROUP BY i.id ORDER BY nb DESC LIMIT 5"
                )->fetchAll();
            } catch (\Exception $e2) {}
        }

        // PVs antiterroristes par mois (pour graphique séparé)
        $pvParMoisAntiT = [];
        try {
            $pvParMoisAntiT = $this->db->query(
                "SELECT DATE_FORMAT(date_reception,'%Y-%m') as mois, COUNT(*) as nb
                 FROM pv WHERE est_antiterroriste=1 AND date_reception >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                 GROUP BY DATE_FORMAT(date_reception,'%Y-%m') ORDER BY mois"
            )->fetchAll();
        } catch (\Exception $e) {}

        return compact(
            'pvMois','dossiersEnCours','audiencesSemaine','population',
            'pvStatuts','dossierTypes','detentionTypes','pvParMois','pvParMoisAntiT',
            'nbAlertesTotal','nbAvocats','nbControlesActifs','nbExpertisesEnCours',
            'nbScelles','nbVoiesRecours','nbOrdonnances','dossierStatuts','jugementsMois',
            'topInfractions'
        );
    }

    public function apiStats(): void {
        Auth::requireLogin();
        $this->json($this->getStats(Auth::currentUser()));
    }
}
