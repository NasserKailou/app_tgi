-- ============================================================
-- MIGRATION 004 — Nouvelles fonctionnalités v2.3 (CORRIGÉE)
-- Date    : 2026-04-25
-- Auteur  : ak_main / AI Developer
-- Notes   : - Compatible MySQL 5.7+ / MariaDB 10.x
--          - Idempotente (réexécutable sans erreur)
--          - Les FK sont posées via une procédure car
--            "ADD CONSTRAINT IF NOT EXISTS" n'est pas fiable.
-- ============================================================

SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 1;

-- ────────────────────────────────────────────────────────────
-- HELPERS : procédures idempotentes pour FK
-- ────────────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS add_fk_if_not_exists;
DROP PROCEDURE IF EXISTS drop_fk_if_exists;

DELIMITER $$

CREATE PROCEDURE add_fk_if_not_exists(
    IN p_table       VARCHAR(64),
    IN p_constraint  VARCHAR(64),
    IN p_definition  TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME        = p_table
          AND CONSTRAINT_NAME   = p_constraint
          AND CONSTRAINT_TYPE   = 'FOREIGN KEY'
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', p_table, '` ADD CONSTRAINT `',
                          p_constraint, '` ', p_definition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

CREATE PROCEDURE drop_fk_if_exists(
    IN p_table       VARCHAR(64),
    IN p_constraint  VARCHAR(64)
)
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME        = p_table
          AND CONSTRAINT_NAME   = p_constraint
          AND CONSTRAINT_TYPE   = 'FOREIGN KEY'
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', p_table, '` DROP FOREIGN KEY `', p_constraint, '`');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- ────────────────────────────────────────────────────────────
-- 1. Qualification d'infractions à deux niveaux
-- ────────────────────────────────────────────────────────────
ALTER TABLE `pv`
    ADD COLUMN IF NOT EXISTS `qualification_substitut_id` int(11) DEFAULT NULL
        COMMENT 'Qualification retenue par le substitut' AFTER `infraction_id`,
    ADD COLUMN IF NOT EXISTS `qualification_details` text DEFAULT NULL
        COMMENT 'Précisions sur la qualification (complicité, circonstances aggravantes…)' AFTER `qualification_substitut_id`,
    ADD COLUMN IF NOT EXISTS `lois_applicables` text DEFAULT NULL
        COMMENT 'Références légales applicables, saisies uniquement par le substitut' AFTER `qualification_details`;

-- Nettoyage préventif : éviter les valeurs orphelines avant pose FK
UPDATE `pv` p
   LEFT JOIN `infractions` i ON i.id = p.qualification_substitut_id
   SET p.qualification_substitut_id = NULL
 WHERE p.qualification_substitut_id IS NOT NULL
   AND i.id IS NULL;

-- FK
CALL add_fk_if_not_exists(
    'pv',
    'fk_pv_qualification_substitut',
    'FOREIGN KEY (`qualification_substitut_id`) REFERENCES `infractions` (`id`) ON DELETE SET NULL'
);

-- ────────────────────────────────────────────────────────────
-- 2. Infractions multiples par PV (unité d'enquête)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `pv_infractions` (
    `id`             int(11) NOT NULL AUTO_INCREMENT,
    `pv_id`          int(11) NOT NULL,
    `infraction_id`  int(11) NOT NULL,
    `type`           enum('unite','substitut') NOT NULL DEFAULT 'unite'
                     COMMENT 'unite=saisie unité enquête, substitut=qualification substitut',
    `est_complicite` tinyint(1) DEFAULT 0 COMMENT '1 = complicité',
    `notes`          varchar(500) DEFAULT NULL,
    `created_at`     timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_pv_infr_type` (`pv_id`, `infraction_id`, `type`),
    KEY `idx_pv_infractions_pv`   (`pv_id`),
    KEY `idx_pv_infractions_infr` (`infraction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Infractions multiples par PV (deux niveaux : unité + substitut)';

-- ────────────────────────────────────────────────────────────
-- 3. Infractions par mise en cause
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `mec_infractions` (
    `id`             int(11) NOT NULL AUTO_INCREMENT,
    `mec_id`         int(11) NOT NULL COMMENT 'mises_en_cause.id',
    `infraction_id`  int(11) NOT NULL,
    `type`           enum('unite','substitut') NOT NULL DEFAULT 'unite',
    `est_complicite` tinyint(1) DEFAULT 0,
    `notes`          varchar(500) DEFAULT NULL,
    `created_at`     timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_mec_infr_type` (`mec_id`, `infraction_id`, `type`),
    KEY `idx_mec_infractions_mec`  (`mec_id`),
    KEY `idx_mec_infractions_infr` (`infraction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Infractions retenues par mis en cause';

-- ────────────────────────────────────────────────────────────
-- 4. Jonction PV multiples → Dossier
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `dossier_pvs` (
    `dossier_id`    int(11) NOT NULL,
    `pv_id`         int(11) NOT NULL,
    `date_jonction` timestamp NOT NULL DEFAULT current_timestamp(),
    `joint_par`     int(11) DEFAULT NULL COMMENT 'user_id du substitut ayant fait la jonction',
    PRIMARY KEY (`dossier_id`, `pv_id`),
    KEY `idx_dossier_pvs_dossier` (`dossier_id`),
    KEY `idx_dossier_pvs_pv`      (`pv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Jonction plusieurs PVs → un dossier (fusion par le substitut)';

-- Migration des associations existantes (uniquement celles dont le PV existe encore)
INSERT IGNORE INTO `dossier_pvs` (`dossier_id`, `pv_id`)
    SELECT d.`id`, d.`pv_id`
      FROM `dossiers` d
      INNER JOIN `pv` p ON p.id = d.pv_id
     WHERE d.`pv_id` IS NOT NULL;

-- ────────────────────────────────────────────────────────────
-- 5. Scellés : précision pour catégorie "autre"
-- ────────────────────────────────────────────────────────────
ALTER TABLE `scelles`
    ADD COLUMN IF NOT EXISTS `categorie_autre_detail` varchar(200) DEFAULT NULL
        COMMENT 'Précision si catégorie = autre' AFTER `categorie`;

-- ────────────────────────────────────────────────────────────
-- 6. Documents PV : rôle de l'uploadeur
-- ────────────────────────────────────────────────────────────
ALTER TABLE `documents`
    ADD COLUMN IF NOT EXISTS `uploaded_by_role` varchar(50) DEFAULT NULL
        COMMENT 'Role de l utilisateur qui a uploadé' AFTER `uploaded_by`;

-- ────────────────────────────────────────────────────────────
-- 7. Rapports générés (journal de bord / fin de journée)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `rapports` (
    `id`           int(11) NOT NULL AUTO_INCREMENT,
    `type`         enum('quotidien','hebdomadaire','mensuel','annuel','personnalise') NOT NULL DEFAULT 'quotidien',
    `titre`        varchar(255) NOT NULL,
    `date_debut`   date NOT NULL,
    `date_fin`     date NOT NULL,
    `contenu_json` longtext DEFAULT NULL COMMENT 'Données du rapport en JSON',
    `fichier`      varchar(255) DEFAULT NULL COMMENT 'Chemin du fichier PDF généré',
    `genere_par`   int(11) DEFAULT NULL,
    `created_at`   timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_rapports_type` (`type`),
    KEY `idx_rapports_date` (`date_debut`),
    KEY `idx_rapports_user` (`genere_par`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Rapports de fin de journée / périodiques générés depuis le dashboard';

-- ────────────────────────────────────────────────────────────
-- 8. Visibilité : substitut_id sur mises_en_cause
-- ────────────────────────────────────────────────────────────
ALTER TABLE `mises_en_cause`
    ADD COLUMN IF NOT EXISTS `substitut_id` int(11) DEFAULT NULL
        COMMENT 'Substitut chargé de ce mis en cause (hérite du PV)' AFTER `created_by`;

-- ────────────────────────────────────────────────────────────
-- 9. Index de performance
-- ────────────────────────────────────────────────────────────
ALTER TABLE `pv`
    ADD INDEX IF NOT EXISTS `idx_pv_created_by`     (`created_by`),
    ADD INDEX IF NOT EXISTS `idx_pv_substitut`      (`substitut_id`),
    ADD INDEX IF NOT EXISTS `idx_pv_date_reception` (`date_reception`);

ALTER TABLE `dossiers`
    ADD INDEX IF NOT EXISTS `idx_dossiers_created_by` (`created_by`),
    ADD INDEX IF NOT EXISTS `idx_dossiers_substitut`  (`substitut_id`);

ALTER TABLE `mises_en_cause`
    ADD INDEX IF NOT EXISTS `idx_mec_created_by` (`created_by`),
    ADD INDEX IF NOT EXISTS `idx_mec_substitut`  (`substitut_id`);

-- ────────────────────────────────────────────────────────────
-- 10. Paramètres sécurité / session
-- ────────────────────────────────────────────────────────────
ALTER TABLE `parametres_tribunal`
    ADD COLUMN IF NOT EXISTS `session_timeout_minutes` int(11) DEFAULT 60
        COMMENT 'Durée de session en minutes' AFTER `id`,
    ADD COLUMN IF NOT EXISTS `max_login_attempts` int(11) DEFAULT 5
        COMMENT 'Tentatives de connexion max avant blocage' AFTER `session_timeout_minutes`,
    ADD COLUMN IF NOT EXISTS `lockout_minutes` int(11) DEFAULT 15
        COMMENT 'Durée de blocage en minutes' AFTER `max_login_attempts`;

-- ────────────────────────────────────────────────────────────
-- 11. Journalisation de sécurité (logs d'accès)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `security_logs` (
    `id`         int(11) NOT NULL AUTO_INCREMENT,
    `user_id`    int(11) DEFAULT NULL,
    `action`     varchar(100) NOT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` varchar(500) DEFAULT NULL,
    `details`    text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_seclog_user`    (`user_id`),
    KEY `idx_seclog_action`  (`action`),
    KEY `idx_seclog_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Journal de sécurité : connexions, tentatives échouées, actions sensibles';

-- ────────────────────────────────────────────────────────────
-- 12. Mise à jour des contraintes d'intégrité (FK idempotentes)
-- ────────────────────────────────────────────────────────────

-- Nettoyage préventif des orphelins (sécurise la pose des FK)
DELETE pi FROM `pv_infractions` pi
   LEFT JOIN `pv`          p ON p.id = pi.pv_id
   LEFT JOIN `infractions` i ON i.id = pi.infraction_id
 WHERE p.id IS NULL OR i.id IS NULL;

DELETE mi FROM `mec_infractions` mi
   LEFT JOIN `mises_en_cause` m ON m.id = mi.mec_id
   LEFT JOIN `infractions`    i ON i.id = mi.infraction_id
 WHERE m.id IS NULL OR i.id IS NULL;

DELETE dp FROM `dossier_pvs` dp
   LEFT JOIN `dossiers` d ON d.id = dp.dossier_id
   LEFT JOIN `pv`       p ON p.id = dp.pv_id
 WHERE d.id IS NULL OR p.id IS NULL;

-- FK sur pv_infractions
CALL add_fk_if_not_exists(
    'pv_infractions', 'fk_pvinf_pv',
    'FOREIGN KEY (`pv_id`) REFERENCES `pv` (`id`) ON DELETE CASCADE'
);
CALL add_fk_if_not_exists(
    'pv_infractions', 'fk_pvinf_infr',
    'FOREIGN KEY (`infraction_id`) REFERENCES `infractions` (`id`) ON DELETE CASCADE'
);

-- FK sur mec_infractions
CALL add_fk_if_not_exists(
    'mec_infractions', 'fk_mecinf_mec',
    'FOREIGN KEY (`mec_id`) REFERENCES `mises_en_cause` (`id`) ON DELETE CASCADE'
);
CALL add_fk_if_not_exists(
    'mec_infractions', 'fk_mecinf_infr',
    'FOREIGN KEY (`infraction_id`) REFERENCES `infractions` (`id`) ON DELETE CASCADE'
);

-- FK sur dossier_pvs
CALL add_fk_if_not_exists(
    'dossier_pvs', 'fk_dossierpvs_dossier',
    'FOREIGN KEY (`dossier_id`) REFERENCES `dossiers` (`id`) ON DELETE CASCADE'
);
CALL add_fk_if_not_exists(
    'dossier_pvs', 'fk_dossierpvs_pv',
    'FOREIGN KEY (`pv_id`) REFERENCES `pv` (`id`) ON DELETE CASCADE'
);

-- ────────────────────────────────────────────────────────────
-- Nettoyage des helpers
-- ────────────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS add_fk_if_not_exists;
DROP PROCEDURE IF EXISTS drop_fk_if_exists;

SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;

-- ────────────────────────────────────────────────────────────
-- Confirmation
-- ────────────────────────────────────────────────────────────
SELECT 'Migration 004 appliquée avec succès' AS statut;
