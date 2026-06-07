-- Migration 007 — v2.4 — 2026-04-29 — ak_main
-- Ajoute la valeur 'autres' à l'ENUM infractions.categorie

ALTER TABLE `infractions`
    MODIFY COLUMN `categorie`
    ENUM('criminelle','correctionnelle','contraventionnelle','autres')
    NOT NULL DEFAULT 'correctionnelle';

SELECT 'Migration 007 appliquée : catégorie "autres" disponible' AS statut;
