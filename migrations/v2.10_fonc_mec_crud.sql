-- =============================================================================
-- Migration v2.10 — Fonctionnalités mec_modifier + mec_supprimer
-- Auteur  : AI Developer  |  Date : 2026-06-20
-- =============================================================================
-- Ajoute deux fonctionnalités pour le système de droits :
--   35 : mec_modifier  — Modifier une mise en cause
--   36 : mec_supprimer — Supprimer une mise en cause
-- Utilise INSERT IGNORE — idempotent.
-- menu_id = 2 (menu PV)
-- =============================================================================

INSERT IGNORE INTO `fonctionnalites` (`id`, `code`, `libelle`, `menu_id`, `description`, `actif`) VALUES
(35, 'mec_modifier',  'Modifier une mise en cause',   2, NULL, 1),
(36, 'mec_supprimer', 'Supprimer une mise en cause',  2, NULL, 1);
