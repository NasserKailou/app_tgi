<?php
/**
 * AnalyticsController — Tableau de bord analytique avancé
 * Routes :
 *   GET /analytics          → index()   (page principale)
 *   GET /api/analytics/data → apiData() (données JSON pour graphiques)
 */
class AnalyticsController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        Auth::requireRole(['admin','procureur','substitut_procureur','president','greffier']);
        $user  = Auth::currentUser();
        $flash = $this->getFlash();

        // Année courante par défaut + filtre
        $annee     = (int)($_GET['annee']     ?? date('Y'));
        $moisDebut = (int)($_GET['mois_debut'] ?? 1);
        $moisFin   = (int)($_GET['mois_fin']   ?? 12);

        $dd = sprintf('%04d-%02d-01', $annee, $moisDebut);
        $df = sprintf('%04d-%02d-%02d', $annee, $moisFin,
            cal_days_in_month(CAL_GREGORIAN, $moisFin, $annee));

        // ── 1. Totaux généraux ────────────────────────────────────────────
        $totPV = (int)$this->db->query("SELECT COUNT(*) FROM pv")->fetchColumn();
        $totPVPeriode = (int)$this->db->prepare(
            "SELECT COUNT(*) FROM pv WHERE date_reception BETWEEN ? AND ?"
        )->execute([$dd,$df]) ? (int)$this->db->prepare(
            "SELECT COUNT(*) FROM pv WHERE date_reception BETWEEN ? AND ?"
        )->execute([$dd,$df]) : 0;

        // Recalcul propre
        $stTot = $this->db->prepare("SELECT COUNT(*) FROM pv WHERE date_reception BETWEEN ? AND ?");
        $stTot->execute([$dd, $df]);
        $totPVPeriode = (int)$stTot->fetchColumn();

        $stClass = $this->db->prepare("SELECT COUNT(*) FROM pv WHERE statut='classe' AND date_reception BETWEEN ? AND ?");
        $stClass->execute([$dd, $df]);
        $totClasse = (int)$stClass->fetchColumn();

        $stTrans = $this->db->prepare("SELECT COUNT(*) FROM pv WHERE statut IN ('transfere','transfere_instruction','transfere_jugement_direct') AND date_reception BETWEEN ? AND ?");
        $stTrans->execute([$dd, $df]);
        $totTransfere = (int)$stTrans->fetchColumn();

        $stPend = $this->db->prepare("SELECT COUNT(*) FROM pv WHERE statut IN ('recu','en_traitement') AND date_reception BETWEEN ? AND ?");
        $stPend->execute([$dd, $df]);
        $totEnCours = (int)$stPend->fetchColumn();

        // ── 2. PVs par mois (courbe d'évolution) ─────────────────────────
        $pvParMois = [];
        for ($m = 1; $m <= 12; $m++) {
            $dm = sprintf('%04d-%02d-01', $annee, $m);
            $fm = sprintf('%04d-%02d-%02d', $annee, $m, cal_days_in_month(CAL_GREGORIAN, $m, $annee));
            $st = $this->db->prepare("SELECT COUNT(*) FROM pv WHERE date_reception BETWEEN ? AND ?");
            $st->execute([$dm, $fm]);
            $pvParMois[$m] = (int)$st->fetchColumn();
        }

        // ── 3. Par statut ────────────────────────────────────────────────
        $st = $this->db->prepare(
            "SELECT statut, COUNT(*) n FROM pv WHERE date_reception BETWEEN ? AND ? GROUP BY statut ORDER BY n DESC"
        );
        $st->execute([$dd, $df]);
        $pvParStatut = $st->fetchAll(PDO::FETCH_KEY_PAIR);

        // ── 4. Par mode de poursuite ─────────────────────────────────────
        $st = $this->db->prepare(
            "SELECT COALESCE(mode_poursuite,'Non défini') mp, COUNT(*) n
             FROM pv WHERE date_reception BETWEEN ? AND ?
             GROUP BY mp ORDER BY n DESC"
        );
        $st->execute([$dd, $df]);
        $pvParMode = $st->fetchAll(PDO::FETCH_KEY_PAIR);

        // ── 5. Top infractions ───────────────────────────────────────────
        $st = $this->db->prepare(
            "SELECT inf.code, inf.libelle, COUNT(*) n
             FROM pv p
             JOIN infractions inf ON p.infraction_id = inf.id
             WHERE p.date_reception BETWEEN ? AND ?
             GROUP BY inf.id ORDER BY n DESC LIMIT 10"
        );
        $st->execute([$dd, $df]);
        $topInfractions = $st->fetchAll();

        // Infractions via pv_infractions (multi)
        $st = $this->db->prepare(
            "SELECT inf.code, inf.libelle, COUNT(*) n
             FROM pv_infractions pi
             JOIN infractions inf ON pi.infraction_id = inf.id
             JOIN pv p ON pi.pv_id = p.id
             WHERE p.date_reception BETWEEN ? AND ?
             GROUP BY inf.id ORDER BY n DESC LIMIT 10"
        );
        $st->execute([$dd, $df]);
        $topInfractionsMulti = $st->fetchAll();

        // ── 6. Charge par substitut ──────────────────────────────────────
        $st = $this->db->prepare(
            "SELECT CONCAT(u.prenom,' ',u.nom) sub,
                    COUNT(*) total,
                    SUM(p.statut IN ('transfere','transfere_instruction','transfere_jugement_direct')) transfere,
                    SUM(p.statut='classe') classe,
                    SUM(p.statut IN ('recu','en_traitement')) en_cours
             FROM pv p
             JOIN users u ON p.substitut_id = u.id
             WHERE p.date_reception BETWEEN ? AND ?
             GROUP BY p.substitut_id ORDER BY total DESC"
        );
        $st->execute([$dd, $df]);
        $chargeSubstituts = $st->fetchAll();

        // ── 7. Charge par unité d'enquête ────────────────────────────────
        $st = $this->db->prepare(
            "SELECT ue.nom unite, COUNT(*) n
             FROM pv p JOIN unites_enquete ue ON p.unite_enquete_id = ue.id
             WHERE p.date_reception BETWEEN ? AND ?
             GROUP BY ue.id ORDER BY n DESC LIMIT 15"
        );
        $st->execute([$dd, $df]);
        $pvParUnite = $st->fetchAll();

        // ── 8. Délai moyen affectation par substitut ─────────────────────
        $st = $this->db->prepare(
            "SELECT CONCAT(u.prenom,' ',u.nom) sub,
                    ROUND(AVG(DATEDIFF(p.date_affectation_substitut, p.date_reception)),1) delai_moy
             FROM pv p
             JOIN users u ON p.substitut_id = u.id
             WHERE p.date_affectation_substitut IS NOT NULL
               AND p.date_reception BETWEEN ? AND ?
             GROUP BY p.substitut_id
             ORDER BY delai_moy"
        );
        $st->execute([$dd, $df]);
        $delaisSubstituts = $st->fetchAll();

        // ── 9. Répartition antiterroriste vs droit commun ────────────────
        $st = $this->db->prepare(
            "SELECT est_antiterroriste, COUNT(*) n
             FROM pv WHERE date_reception BETWEEN ? AND ?
             GROUP BY est_antiterroriste"
        );
        $st->execute([$dd, $df]);
        $antiterro = ['standard' => 0, 'antiterro' => 0];
        foreach ($st->fetchAll() as $r) {
            if ($r['est_antiterroriste']) $antiterro['antiterro'] = (int)$r['n'];
            else $antiterro['standard'] = (int)$r['n'];
        }

        // ── 10. Taux de résolution mensuel ───────────────────────────────
        $tauxResolution = [];
        for ($m = 1; $m <= 12; $m++) {
            $dm = sprintf('%04d-%02d-01', $annee, $m);
            $fm = sprintf('%04d-%02d-%02d', $annee, $m, cal_days_in_month(CAL_GREGORIAN, $m, $annee));
            $stA = $this->db->prepare("SELECT COUNT(*) FROM pv WHERE date_reception BETWEEN ? AND ?");
            $stA->execute([$dm, $fm]);
            $tot = (int)$stA->fetchColumn();
            $stR = $this->db->prepare(
                "SELECT COUNT(*) FROM pv WHERE statut IN ('transfere','transfere_instruction','transfere_jugement_direct','classe') AND date_reception BETWEEN ? AND ?"
            );
            $stR->execute([$dm, $fm]);
            $res = (int)$stR->fetchColumn();
            $tauxResolution[$m] = $tot > 0 ? round($res/$tot*100, 1) : 0;
        }

        // ── 11. CRPC — statistiques ──────────────────────────────────────
        $crpcStats = ['total' => 0, 'homologuee' => 0, 'refusee' => 0, 'en_cours' => 0];
        try {
            $st = $this->db->prepare(
                "SELECT c.statut, COUNT(*) n FROM crpc_dossiers c
                 JOIN pv p ON c.pv_id = p.id
                 WHERE p.date_reception BETWEEN ? AND ?
                 GROUP BY c.statut"
            );
            $st->execute([$dd, $df]);
            foreach ($st->fetchAll() as $r) {
                $crpcStats[$r['statut']] = (int)$r['n'];
                $crpcStats['total'] += (int)$r['n'];
            }
        } catch (\Exception $e) {}

        // ── 12. Comparaison N vs N-1 ─────────────────────────────────────
        $anneeN1 = $annee - 1;
        $ddN1    = sprintf('%04d-%02d-01', $anneeN1, $moisDebut);
        $dfN1    = sprintf('%04d-%02d-%02d', $anneeN1, $moisFin,
            cal_days_in_month(CAL_GREGORIAN, $moisFin, $anneeN1));
        $stN1 = $this->db->prepare("SELECT COUNT(*) FROM pv WHERE date_reception BETWEEN ? AND ?");
        $stN1->execute([$ddN1, $dfN1]);
        $totN1 = (int)$stN1->fetchColumn();
        $evolution = $totN1 > 0 ? round(($totPVPeriode - $totN1) / $totN1 * 100, 1) : null;

        $this->view('analytics/index', compact(
            'user','flash',
            'annee','moisDebut','moisFin','dd','df',
            'totPV','totPVPeriode','totClasse','totTransfere','totEnCours',
            'pvParMois','pvParStatut','pvParMode',
            'topInfractions','topInfractionsMulti',
            'chargeSubstituts','pvParUnite',
            'delaisSubstituts','antiterro','tauxResolution',
            'crpcStats','totN1','evolution','anneeN1'
        ));
    }

    /**
     * API JSON pour actualisation dynamique des graphiques
     */
    public function apiData(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json; charset=utf-8');

        $annee = (int)($_GET['annee'] ?? date('Y'));
        $dd    = sprintf('%04d-01-01', $annee);
        $df    = sprintf('%04d-12-31', $annee);

        // PVs par mois (toute l'année)
        $pvMois = [];
        for ($m = 1; $m <= 12; $m++) {
            $dm = sprintf('%04d-%02d-01', $annee, $m);
            $fm = sprintf('%04d-%02d-%02d', $annee, $m, cal_days_in_month(CAL_GREGORIAN, $m, $annee));
            $st = $this->db->prepare("SELECT COUNT(*) FROM pv WHERE date_reception BETWEEN ? AND ?");
            $st->execute([$dm, $fm]);
            $pvMois[] = (int)$st->fetchColumn();
        }

        echo json_encode(['success' => true, 'pvMois' => $pvMois, 'annee' => $annee]);
    }
}
