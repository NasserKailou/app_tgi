-- ══════════════════════════════════════════════════════════════════════════════
-- Migration v2.13 — Correction ENUM type_affaire dans dossiers (production)
--
-- Problème : pv.type_affaire inclut des valeurs métier absentes de dossiers.type_affaire
--   pv.type_affaire ENUM = ('droit_commun_mineur','droit_commun_majeur',
--                           'pole_antiterro_mineur','pole_antiterro_majeur',
--                           'pole_economique','civile','penale','commerciale')
--   dossiers.type_affaire ENUM = ('civile','penale','commerciale')  ← trop restreint
--
-- La correction CODE dans PVController::transferer() mappe déjà correctement :
--   droit_commun_* → penale
--   pole_antiterro_* → penale
--   pole_economique → commerciale
--   civile, penale, commerciale → inchangé
--
-- Cette migration étend l'ENUM dossiers pour accepter les valeurs métier du PV
-- (défense en profondeur — même si le mapping PHP est en place, l'ENUM reste ouvert)
--
-- SAFE : opération DDL, ne modifie pas les données existantes.
-- ══════════════════════════════════════════════════════════════════════════════

-- A. Étendre dossiers.type_affaire pour inclure les valeurs métier du PV
--    (fallback : si une valeur non mappée arrive quand même, MySQL accepte)
ALTER TABLE `dossiers`
    MODIFY COLUMN `type_affaire`
        ENUM(
            'penale',
            'civile',
            'commerciale',
            'droit_commun_mineur',
            'droit_commun_majeur',
            'pole_antiterro_mineur',
            'pole_antiterro_majeur',
            'pole_economique'
        ) NOT NULL DEFAULT 'penale'
        COMMENT 'Type affaire : valeurs dossier (penale/civile/commerciale) + valeurs PV pour compatibilité';

-- B. S'assurer que dossiers.nature a bien toutes ses valeurs
--    (au cas où la colonne existerait avec un ENUM plus restreint)
ALTER TABLE `dossiers`
    MODIFY COLUMN `nature`
        ENUM('correctionnel','instructionnel','civil','commercial','criminel')
        DEFAULT 'correctionnel'
        COMMENT 'Nature de l''affaire : dérivée du type et du mode de poursuite';

-- C. Rappel v2.12 — à appliquer si pas encore fait :
-- ALTER TABLE `dossiers` MODIFY COLUMN `intitule` VARCHAR(255) NOT NULL DEFAULT '';
-- ALTER TABLE `dossiers` MODIFY COLUMN `mode_poursuite` ENUM('aucun','CD','FD','CRCP','CRPC','RI') DEFAULT 'aucun';

-- ══════════════════════════════════════════════════════════════════════════════
-- RÉSUMÉ DES MAPPINGS PHP (documentation — PVController::transferer())
-- ══════════════════════════════════════════════════════════════════════════════
-- type_affaire (pv → dossiers) :
--   civile                → civile
--   commerciale           → commerciale
--   pole_economique       → commerciale
--   penale                → penale
--   droit_commun_mineur   → penale
--   droit_commun_majeur   → penale
--   pole_antiterro_mineur → penale
--   pole_antiterro_majeur → penale
--
-- nature (déduite du type_affaire pv + mode_poursuite) :
--   pole_antiterro_*      → criminel
--   civile                → civil
--   commerciale / pole_economique → commercial
--   mode_poursuite = RI   → instructionnel
--   (tous autres)         → correctionnel
-- ══════════════════════════════════════════════════════════════════════════════
