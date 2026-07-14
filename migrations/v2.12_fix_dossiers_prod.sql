-- ══════════════════════════════════════════════════════════════════════════════
-- Migration v2.12 — Correctifs table dossiers pour compatibilité production
--
-- Problèmes constatés en production (tgihc.ca-niamey.ne) :
--   1. dossiers.intitule : VARCHAR(255) NOT NULL sans DEFAULT → bloque INSERT
--   2. dossiers.mode_poursuite ENUM ne contient pas 'CRPC' (seulement 'CRCP')
--
-- Cette migration :
--   A. Ajoute DEFAULT '' à intitule (évite l'erreur "Field doesn't have a default value")
--   B. Étend l'ENUM mode_poursuite pour accepter 'CRPC' en plus de 'CRCP'
--
-- SAFE : idempotent, ne modifie pas les données existantes
-- ══════════════════════════════════════════════════════════════════════════════

-- A. Rendre intitule nullable avec une valeur vide par défaut
ALTER TABLE `dossiers`
    MODIFY COLUMN `intitule` VARCHAR(255) NOT NULL DEFAULT ''
    COMMENT 'Intitulé du dossier (noms des mis en cause ou description)';

-- B. Étendre l'ENUM mode_poursuite pour inclure 'CRPC' (la table pv utilise CRPC,
--    la table dossiers historiquement CRCP — on accepte les deux)
ALTER TABLE `dossiers`
    MODIFY COLUMN `mode_poursuite`
        ENUM('aucun','CD','FD','CRCP','CRPC','RI') DEFAULT 'aucun'
        COMMENT 'Mode de poursuite : AUCUN, Citation Directe, Flagrant délit, CRPC/CRCP, Réquisitoire Introductif';

-- C. S'assurer que la colonne updated_at existe (certaines installations l'auraient absente)
ALTER TABLE `dossiers`
    MODIFY COLUMN `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
