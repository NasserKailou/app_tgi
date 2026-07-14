-- ============================================================
-- Migration v2.8 — Menus & Fonctionnalités : Statistiques,
--                  Tableau analytique, Situation PV, CRPC, Rapports
-- À exécuter UNE SEULE FOIS sur la base de production
-- Compatible : MariaDB 10.x / MySQL 5.7+
-- ============================================================

-- ── 1. Nouveaux menus ────────────────────────────────────────────────────────
-- On utilise INSERT IGNORE pour éviter les doublons si déjà exécuté

INSERT IGNORE INTO `menus`
    (`id`, `code`, `libelle`, `icone`, `url`, `parent_id`, `ordre`, `actif`)
VALUES
    (21, 'analytics',    'Tableau analytique',   'bi-graph-up-arrow',        '/analytics',    NULL, 40, 1),
    (22, 'situation_pv', 'Situation des PVs',    'bi-bar-chart-line',         '/situation/pv', NULL, 41, 1),
    (23, 'situation_crpc','Situation CRPC',       'bi-file-earmark-text',     '/situation/crpc',NULL,42, 1),
    (24, 'rapports',     'Rapports',             'bi-file-earmark-bar-graph', '/rapports',     NULL, 43, 1);


-- ── 2. Nouvelles fonctionnalités ─────────────────────────────────────────────

INSERT IGNORE INTO `fonctionnalites`
    (`id`, `code`, `libelle`, `menu_id`, `description`, `actif`)
VALUES
    -- Analytics (menu 21)
    (27, 'analytics_voir',       'Consulter le tableau analytique',    21, 'Accès lecture au tableau de bord analytique avancé (graphiques, KPIs, comparaisons)', 1),
    (28, 'analytics_api',        'API données analytiques (JSON)',     21, 'Appel à /api/analytics/data pour actualisation dynamique des graphiques', 1),

    -- Situation PV (menu 22)
    (29, 'situation_pv_voir',    'Consulter la situation des PVs',     22, 'Accès lecture à la page Situation des PVs avec filtres', 1),
    (30, 'situation_pv_export',  'Exporter la situation PV (CSV/PDF)', 22, 'Export CSV ou PDF de la situation des PVs', 1),

    -- Situation CRPC (menu 23)
    (31, 'situation_crpc_voir',  'Consulter la situation CRPC',        23, 'Accès lecture à la page Situation CRPC avec fiches imprimables', 1),
    (32, 'situation_crpc_export','Exporter la situation CRPC (CSV)',   23, 'Export CSV de la situation CRPC', 1),

    -- Rapports (menu 24)
    (33, 'rapports_voir',        'Consulter les rapports',             24, 'Accès à la liste et consultation des rapports générés', 1),
    (34, 'rapports_generer',     'Générer un rapport',                 24, 'Lancement de la génération de nouveaux rapports', 1);


-- ── 3. Vérification (optionnel — peut être commenté en prod) ─────────────────
-- SELECT id, code, libelle, ordre FROM menus WHERE id >= 21 ORDER BY ordre;
-- SELECT id, code, libelle, menu_id FROM fonctionnalites WHERE id >= 27 ORDER BY menu_id, id;
