ALTER TABLE `mandats`
    ADD COLUMN IF NOT EXISTS `nouveau_lieu_naissance` VARCHAR(150) NULL,
    ADD COLUMN IF NOT EXISTS `nouveau_pere`           VARCHAR(150) NULL,
    ADD COLUMN IF NOT EXISTS `nouveau_mere`           VARCHAR(150) NULL,
    ADD COLUMN IF NOT EXISTS `sexe`                   ENUM('M','F') NULL,
    ADD COLUMN IF NOT EXISTS `situation_famille`      VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS `service_militaire`      VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS `condamnations`          TEXT NULL,
    ADD COLUMN IF NOT EXISTS `flagrant_delit`         TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `date_exhibe`            DATE NULL,
    ADD COLUMN IF NOT EXISTS `updated_at`             TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP;


SELECT 'Migration 008 appliquée : champs étendus pour mandats officiels' AS statut;
