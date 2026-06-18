-- =============================================================================
-- Migration v2.9 — Table mouvements_pv (audit trail des affectations / actions PV)
-- Auteur  : AI Developer  |  Date : 2026-06-18
-- =============================================================================
-- Exécuter UNE SEULE FOIS sur la base de production.
-- Utilise CREATE TABLE IF NOT EXISTS — idempotent.
-- =============================================================================
-- Utilisée par PVController::affecter() pour tracer les (ré)affectations de substitut.
-- Si la table n'existe pas, le contrôleur retombe sur mouvements_dossier (fallback).
-- =============================================================================

CREATE TABLE IF NOT EXISTS `mouvements_pv` (
    `id`                    INT(11)      NOT NULL AUTO_INCREMENT,
    `pv_id`                 INT(11)      NOT NULL,
    `user_id`               INT(11)      DEFAULT NULL   COMMENT 'Utilisateur ayant effectué l''action',
    `type_mouvement`        VARCHAR(100) NOT NULL        COMMENT 'Ex : affectation_substitut, reaffectation_substitut',
    `ancien_substitut_id`   INT(11)      DEFAULT NULL,
    `nouveau_substitut_id`  INT(11)      DEFAULT NULL,
    `description`           TEXT         DEFAULT NULL   COMMENT 'Ex : Substitut modifié : Moussa Adamou → Ali Issoufou',
    `created_at`            TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_mvtpv_pv`       (`pv_id`),
    KEY `idx_mvtpv_user`     (`user_id`),
    KEY `idx_mvtpv_anc_sub`  (`ancien_substitut_id`),
    KEY `idx_mvtpv_nouv_sub` (`nouveau_substitut_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Historique des actions sur les PV (affectations, réaffectations de substitut…)';

-- Contraintes FK optionnelles — décommenter si les tables parentes existent
-- ALTER TABLE `mouvements_pv`
--   ADD CONSTRAINT `fk_mvtpv_pv`       FOREIGN KEY (`pv_id`)                REFERENCES `pv`    (`id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `fk_mvtpv_user`     FOREIGN KEY (`user_id`)              REFERENCES `users` (`id`) ON DELETE SET NULL,
--   ADD CONSTRAINT `fk_mvtpv_anc_sub`  FOREIGN KEY (`ancien_substitut_id`)  REFERENCES `users` (`id`) ON DELETE SET NULL,
--   ADD CONSTRAINT `fk_mvtpv_nouv_sub` FOREIGN KEY (`nouveau_substitut_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
