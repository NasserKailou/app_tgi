-- ══════════════════════════════════════════════════════════════════════════════
-- Migration v2.11 — Table de traçabilité des actions sur les mises en cause
-- Chaque création, modification ou suppression de MEC est enregistrée ici,
-- avec le contexte complet (statut du dossier/PV, utilisateur, données avant/après)
-- ══════════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `mec_historique` (
    `id`              INT(11)      NOT NULL AUTO_INCREMENT,
    `mec_id`          INT(11)      DEFAULT NULL   COMMENT 'NULL si suppression définitive',
    `pv_id`           INT(11)      NOT NULL,
    `dossier_id`      INT(11)      DEFAULT NULL   COMMENT 'Renseigné si l\'action vient d\'un dossier',
    `user_id`         INT(11)      DEFAULT NULL,
    `action`          VARCHAR(20)  NOT NULL       COMMENT 'create | update | delete | reconduire',
    `statut_dossier`  VARCHAR(50)  DEFAULT NULL   COMMENT 'Statut du dossier au moment de l\'action',
    `statut_pv`       VARCHAR(50)  DEFAULT NULL   COMMENT 'Statut du PV au moment de l\'action',
    `nom_mec`         VARCHAR(150) DEFAULT NULL   COMMENT 'Nom/prénom snapshot pour les suppressions',
    `prenom_mec`      VARCHAR(150) DEFAULT NULL,
    `data_avant`      TEXT         DEFAULT NULL   COMMENT 'JSON des données avant modification',
    `data_apres`      TEXT         DEFAULT NULL   COMMENT 'JSON des données après modification',
    `ip_address`      VARCHAR(45)  DEFAULT NULL,
    `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_mech_mec`     (`mec_id`),
    KEY `idx_mech_pv`      (`pv_id`),
    KEY `idx_mech_dossier` (`dossier_id`),
    KEY `idx_mech_user`    (`user_id`),
    KEY `idx_mech_action`  (`action`),
    KEY `idx_mech_date`    (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Audit trail de toutes les actions CRUD sur les mises en cause';
