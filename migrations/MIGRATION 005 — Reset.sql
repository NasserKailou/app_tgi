-- ============================================================
-- MIGRATION 005 — Reset des données métier après déploiement
-- Date    : 2026-04-28
-- Objet   : Vider la base des données opérationnelles tout en
--          conservant : users, roles, menus, droits, paramètres,
--          géographie, salles, maisons d'arrêt, infractions,
--          fonctions parquet, rôles.
-- ATTENTION : OPÉRATION IRRÉVERSIBLE. Faire un dump avant !
-- ============================================================

SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;

-- ────────────────────────────────────────────────────────────
-- 1. Tables filles (références vers PV/dossiers/MEC/détenus…)
-- ────────────────────────────────────────────────────────────

-- Casier judiciaire
TRUNCATE TABLE `casier_judiciaire_condamnations`;
TRUNCATE TABLE `casier_judiciaire_personnes`;

-- Voies de recours
TRUNCATE TABLE `voies_recours`;

-- Expertises, commissions rogatoires, contrôles judiciaires, ordonnances, scellés
TRUNCATE TABLE `expertises_judiciaires`;
TRUNCATE TABLE `commissions_rogatoires`;
TRUNCATE TABLE `controles_judiciaires`;
TRUNCATE TABLE `ordonnances`;
TRUNCATE TABLE `scelles`;

-- Mandats, détenus
TRUNCATE TABLE `mandats`;
TRUNCATE TABLE `detenus`;

-- Audiences et jugements
TRUNCATE TABLE `membres_audience`;
TRUNCATE TABLE `jugements`;
TRUNCATE TABLE `audiences`;

-- Documents et alertes
TRUNCATE TABLE `documents`;
TRUNCATE TABLE `alertes`;
TRUNCATE TABLE `mouvements_dossier`;

-- Avocats / lien dossiers
TRUNCATE TABLE `avocat_dossier`;
TRUNCATE TABLE `dossier_avocats`;
TRUNCATE TABLE `avocats`;

-- Plaintes
TRUNCATE TABLE `plaintes`;

-- Tables ajoutées par migration 004
TRUNCATE TABLE `mec_infractions`;
TRUNCATE TABLE `pv_infractions`;
TRUNCATE TABLE `dossier_pvs`;
TRUNCATE TABLE `rapports`;
TRUNCATE TABLE `security_logs`;

-- Mises en cause, parties
TRUNCATE TABLE `mises_en_cause`;
TRUNCATE TABLE `parties`;

-- Liens PV ↔ primo-intervenants
TRUNCATE TABLE `pv_primo_intervenants`;

-- ────────────────────────────────────────────────────────────
-- 2. Tables principales : dossiers et PV
-- ────────────────────────────────────────────────────────────
TRUNCATE TABLE `dossiers`;
TRUNCATE TABLE `pv`;

-- ────────────────────────────────────────────────────────────
-- 3. Tables de configuration métier à réinitialiser
--    (seront repeuplées par le script 006)
-- ────────────────────────────────────────────────────────────
TRUNCATE TABLE `unites_enquete`;
TRUNCATE TABLE `primo_intervenants`;
TRUNCATE TABLE `cabinets_instruction`;

-- Substituts : on supprime UNIQUEMENT les utilisateurs avec role_id=5
-- pour ne pas toucher à l'admin, président, procureur, juges, greffiers
DELETE FROM `users` WHERE role_id = 5;

-- ────────────────────────────────────────────────────────────
-- 4. Réinitialisation des compteurs AUTO_INCREMENT
-- ────────────────────────────────────────────────────────────
ALTER TABLE `pv`                              AUTO_INCREMENT = 1;
ALTER TABLE `dossiers`                        AUTO_INCREMENT = 1;
ALTER TABLE `mises_en_cause`                  AUTO_INCREMENT = 1;
ALTER TABLE `parties`                         AUTO_INCREMENT = 1;
ALTER TABLE `detenus`                         AUTO_INCREMENT = 1;
ALTER TABLE `documents`                       AUTO_INCREMENT = 1;
ALTER TABLE `mandats`                         AUTO_INCREMENT = 1;
ALTER TABLE `audiences`                       AUTO_INCREMENT = 1;
ALTER TABLE `jugements`                       AUTO_INCREMENT = 1;
ALTER TABLE `membres_audience`                AUTO_INCREMENT = 1;
ALTER TABLE `ordonnances`                     AUTO_INCREMENT = 1;
ALTER TABLE `scelles`                         AUTO_INCREMENT = 1;
ALTER TABLE `expertises_judiciaires`          AUTO_INCREMENT = 1;
ALTER TABLE `commissions_rogatoires`          AUTO_INCREMENT = 1;
ALTER TABLE `controles_judiciaires`           AUTO_INCREMENT = 1;
ALTER TABLE `voies_recours`                   AUTO_INCREMENT = 1;
ALTER TABLE `casier_judiciaire_personnes`     AUTO_INCREMENT = 1;
ALTER TABLE `casier_judiciaire_condamnations` AUTO_INCREMENT = 1;
ALTER TABLE `plaintes`                        AUTO_INCREMENT = 1;
ALTER TABLE `avocats`                         AUTO_INCREMENT = 1;
ALTER TABLE `avocat_dossier`                  AUTO_INCREMENT = 1;
ALTER TABLE `dossier_avocats`                 AUTO_INCREMENT = 1;
ALTER TABLE `alertes`                         AUTO_INCREMENT = 1;
ALTER TABLE `mouvements_dossier`              AUTO_INCREMENT = 1;
ALTER TABLE `pv_infractions`                  AUTO_INCREMENT = 1;
ALTER TABLE `mec_infractions`                 AUTO_INCREMENT = 1;
ALTER TABLE `rapports`                        AUTO_INCREMENT = 1;
ALTER TABLE `security_logs`                   AUTO_INCREMENT = 1;
ALTER TABLE `unites_enquete`                  AUTO_INCREMENT = 1;
ALTER TABLE `primo_intervenants`              AUTO_INCREMENT = 1;
ALTER TABLE `cabinets_instruction`            AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;

SELECT 'Reset 005 effectué : données métier vidées, configuration préservée' AS statut;
