-- ============================================================
-- Migration 002 — Mises en cause, Plaintes, Améliorations PV
-- Branche : nasser — Avril 2026
-- À appliquer après tribunal_tgi_ny_maj.sql
-- ============================================================

-- ────────────────────────────────────────────────────────────
-- 1. TABLE : mises_en_cause
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `mises_en_cause` (
  `id`                    int(11) NOT NULL AUTO_INCREMENT,
  `pv_id`                 int(11) NOT NULL COMMENT 'PV concerné',
  `nom`                   varchar(100) NOT NULL,
  `prenom`                varchar(100) DEFAULT NULL,
  `alias`                 varchar(100) DEFAULT NULL COMMENT 'Alias / surnom',
  `nom_mere`              varchar(150) DEFAULT NULL,
  `date_naissance`        date DEFAULT NULL,
  `lieu_naissance`        varchar(200) DEFAULT NULL,
  `nationalite`           varchar(100) DEFAULT 'Nigérienne',
  `sexe`                  enum('M','F','Inconnu') DEFAULT 'M',
  `profession`            varchar(150) DEFAULT NULL,
  `adresse`               text DEFAULT NULL,
  `telephone`             varchar(30) DEFAULT NULL,
  `statut`                enum('mise_en_cause','prevenu','temoin','autre') NOT NULL DEFAULT 'mise_en_cause',
  `statut_autre_detail`   varchar(200) DEFAULT NULL COMMENT 'Précision si statut=autre',
  `photo`                 varchar(255) DEFAULT NULL,
  `personne_contacter_nom`       varchar(200) DEFAULT NULL COMMENT 'Personne à contacter',
  `personne_contacter_tel`       varchar(30) DEFAULT NULL,
  `personne_contacter_lien`      varchar(100) DEFAULT NULL COMMENT 'Lien (père, mère, époux...)',
  `est_connu_archives`    tinyint(1) DEFAULT 0 COMMENT '1 = déjà connu dans les archives',
  `nb_affaires_precedentes` int(11) DEFAULT 0 COMMENT 'Nombre d affaires antérieures',
  `notes_antecedents`     text DEFAULT NULL,
  -- Décision du substitut sur la poursuite
  `decision_substitut`    enum('poursuivi','non_poursuivi','en_attente') DEFAULT 'en_attente',
  `motif_non_poursuite`   text DEFAULT NULL,
  `date_decision`         date DEFAULT NULL,
  `created_by`            int(11) DEFAULT NULL,
  `created_at`            timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at`            timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_mec_pv` (`pv_id`),
  KEY `idx_mec_statut` (`statut`),
  KEY `idx_mec_decision` (`decision_substitut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Mises en cause saisies au moment de l enregistrement du PV';

-- ────────────────────────────────────────────────────────────
-- 2. TABLE : plaintes
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `plaintes` (
  `id`                   int(11) NOT NULL AUTO_INCREMENT,
  `numero_plainte`       varchar(60) NOT NULL COMMENT 'Numéro de référence unique',
  `date_plainte`         date NOT NULL,
  `date_reception`       date NOT NULL DEFAULT (CURDATE()),
  -- Plaignant
  `plaignant_nom`        varchar(150) NOT NULL,
  `plaignant_prenom`     varchar(100) DEFAULT NULL,
  `plaignant_telephone`  varchar(30) DEFAULT NULL,
  `plaignant_adresse`    text DEFAULT NULL,
  `plaignant_email`      varchar(150) DEFAULT NULL,
  `plaignant_qualite`    enum('personne_physique','personne_morale','administration','autre') DEFAULT 'personne_physique',
  -- Mis en cause dans la plainte
  `mis_en_cause_nom`     varchar(200) DEFAULT NULL,
  `mis_en_cause_adresse` text DEFAULT NULL,
  -- Nature et faits
  `nature_plainte`       varchar(255) NOT NULL,
  `description_faits`    text DEFAULT NULL,
  `lieu_faits`           varchar(255) DEFAULT NULL,
  `date_faits`           date DEFAULT NULL,
  `pieces_jointes`       varchar(255) DEFAULT NULL COMMENT 'Chemin fichier joint',
  -- Traitement
  `statut`               enum('deposee','en_examen','transmise_pv','classee','irrecevable') NOT NULL DEFAULT 'deposee',
  `pv_id`                int(11) DEFAULT NULL COMMENT 'PV créé suite à la plainte',
  `motif_classement`     text DEFAULT NULL,
  `substitut_id`         int(11) DEFAULT NULL COMMENT 'Substitut chargé',
  `observations`         text DEFAULT NULL,
  `created_by`           int(11) DEFAULT NULL,
  `created_at`           timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at`           timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_numero_plainte` (`numero_plainte`),
  KEY `idx_plainte_statut` (`statut`),
  KEY `idx_plainte_date` (`date_plainte`),
  KEY `idx_plainte_pv` (`pv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Gestion des plaintes reçues au parquet';

-- ────────────────────────────────────────────────────────────
-- 3. MODIFICATION : table pv — nouveaux types d'affaire + champs RP manuel
-- ────────────────────────────────────────────────────────────
-- 3a. Ajouter les nouveaux types d'affaire (5 pôles)
ALTER TABLE `pv`
  MODIFY COLUMN `type_affaire`
    enum(
      'droit_commun_mineur',
      'droit_commun_majeur',
      'pole_antiterro_mineur',
      'pole_antiterro_majeur',
      'pole_economique',
      'civile',
      'penale',
      'commerciale'
    ) NOT NULL DEFAULT 'droit_commun_majeur';

-- 3b. Ajouter le numéro RP (registre du parquet) saisi manuellement + numéro d'ordre
ALTER TABLE `pv`
  ADD COLUMN IF NOT EXISTS `numero_rp`     varchar(60) DEFAULT NULL COMMENT 'Registre du Parquet (saisi manuellement)',
  ADD COLUMN IF NOT EXISTS `numero_ordre`  varchar(60) DEFAULT NULL COMMENT 'Numéro d ordre du PV (saisi manuellement)',
  ADD COLUMN IF NOT EXISTS `mode_poursuite` enum('RI','CD','FD','CRPC','autre') DEFAULT NULL COMMENT 'Mode de poursuite décidé par le substitut';

-- Contrainte d'unicité sur le RP dans la table PV
ALTER TABLE `pv`
  ADD UNIQUE KEY IF NOT EXISTS `uk_numero_rp` (`numero_rp`);

-- ────────────────────────────────────────────────────────────
-- 4. MODIFICATION : table dossiers — numéro RI unique + saisi au transfert
-- ────────────────────────────────────────────────────────────
-- Contrainte d'unicité sur numero_ri
ALTER TABLE `dossiers`
  ADD UNIQUE KEY IF NOT EXISTS `uk_numero_ri` (`numero_ri`),
  ADD UNIQUE KEY IF NOT EXISTS `uk_numero_rp_dossier` (`numero_rp`);

-- ────────────────────────────────────────────────────────────
-- 5. AJOUT MENUS manquants (droits) + fonctionnalités
-- ────────────────────────────────────────────────────────────
INSERT IGNORE INTO `menus` (`code`, `libelle`, `icone`, `url`, `parent_id`, `ordre`, `actif`) VALUES
('plaintes',             'Plaintes',                'bi-megaphone',        '/plaintes',              NULL, 25, 1),
('avocats',              'Barreau / Avocats',        'bi-person-badge',     '/avocats',               NULL, 26, 1),
('casier_judiciaire',    'Casier judiciaire',        'bi-person-vcard',     '/casier-judiciaire',     NULL, 27, 1),
('voies_recours',        'Voies de recours',         'bi-arrow-repeat',     '/voies-recours',         NULL, 28, 1),
('ordonnances',          'Ordonnances JI',           'bi-file-earmark-text','/ordonnances',           NULL, 29, 1),
('controles_judiciaires','Contrôles judiciaires',    'bi-shield-check',     '/controles-judiciaires', NULL, 30, 1),
('expertises',           'Expertises',               'bi-microscope',       '/expertises',            NULL, 31, 1),
('commissions_rogatoires','Commissions rogatoires',  'bi-send',             '/commissions-rogatoires',NULL, 32, 1),
('scelles',              'Scellés',                  'bi-archive',          '/scelles',               NULL, 33, 1);

-- Fonctionnalités supplémentaires
INSERT IGNORE INTO `fonctionnalites` (`code`, `libelle`, `menu_id`, `actif`) VALUES
('plainte_creer',        'Déposer une plainte',         (SELECT id FROM menus WHERE code='plaintes' LIMIT 1), 1),
('plainte_traiter',      'Traiter une plainte',         (SELECT id FROM menus WHERE code='plaintes' LIMIT 1), 1),
('mec_creer',            'Saisir une mise en cause',    (SELECT id FROM menus WHERE code='pv' LIMIT 1),      1),
('mec_decision',         'Décider poursuite/non poursuite', (SELECT id FROM menus WHERE code='pv' LIMIT 1), 1);

-- ────────────────────────────────────────────────────────────
-- 6. FOREIGN KEYS (ajout progressif, ignore si déjà existant)
-- ────────────────────────────────────────────────────────────
ALTER TABLE `mises_en_cause`
  ADD CONSTRAINT `fk_mec_pv`      FOREIGN KEY (`pv_id`)       REFERENCES `pv`    (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mec_user`    FOREIGN KEY (`created_by`)  REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `plaintes`
  ADD CONSTRAINT `fk_plainte_pv`   FOREIGN KEY (`pv_id`)        REFERENCES `pv`    (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_plainte_sub`  FOREIGN KEY (`substitut_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_plainte_user` FOREIGN KEY (`created_by`)   REFERENCES `users` (`id`) ON DELETE SET NULL;


-- ────────────────────────────────────────────────────────────
-- 7. AJOUT CHAMPS MANQUANTS : table avocats
-- (la table existante n'a pas ces champs, le contrôleur en a besoin)
-- ────────────────────────────────────────────────────────────
ALTER TABLE `avocats`
  ADD COLUMN IF NOT EXISTS `date_naissance`  date DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `lieu_naissance`  varchar(150) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `nationalite`     varchar(100) DEFAULT 'Nigérienne',
  ADD COLUMN IF NOT EXISTS `sexe`            enum('M','F') DEFAULT 'M',
  ADD COLUMN IF NOT EXISTS `specialite`      varchar(200) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `notes`           text DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `created_by`      int(11) DEFAULT NULL;

-- La table dossier_avocats dans le code != avocat_dossier dans la DB
-- Alias pour compatibilité : créer dossier_avocats comme vue ou renommer
-- On crée une nouvelle table dossier_avocats compatible avec le contrôleur
CREATE TABLE IF NOT EXISTS `dossier_avocats` (
  `id`           int(11) NOT NULL AUTO_INCREMENT,
  `dossier_id`   int(11) NOT NULL,
  `avocat_id`    int(11) NOT NULL,
  `partie_id`    int(11) DEFAULT NULL,
  `role_avocat`  varchar(100) DEFAULT 'défenseur',
  `date_mandat`  date DEFAULT NULL,
  `notes`        text DEFAULT NULL,
  `actif`        tinyint(1) DEFAULT 1,
  `created_at`   timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_dos_avo` (`dossier_id`, `avocat_id`),
  KEY `idx_da_avocat` (`avocat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ═══════════════════════════════════════════════════════════════════════════
-- CORRECTIF : Ajouter jugement_id dans detenus (si absent)
-- ═══════════════════════════════════════════════════════════════════════════
ALTER TABLE `detenus`
  ADD COLUMN IF NOT EXISTS `jugement_id` int(11) DEFAULT NULL AFTER `dossier_id`;

ALTER TABLE `detenus`
  ADD INDEX IF NOT EXISTS `idx_detenus_jugement` (`jugement_id`);

-- Ajout de la clé étrangère (optionnel, peut échouer si jugements n'existe pas encore)
-- ALTER TABLE `detenus` ADD CONSTRAINT `fk_detenus_jugement` FOREIGN KEY (`jugement_id`) REFERENCES `jugements`(`id`) ON DELETE SET NULL;
