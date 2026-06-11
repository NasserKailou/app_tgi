-- ============================================================
-- Migration v2.7 — Table crpc_dossiers
-- CRPC : Comparution sur Reconnaissance Préalable de Culpabilité
-- À appliquer sur la base app_tgi
-- ============================================================

CREATE TABLE IF NOT EXISTS `crpc_dossiers` (
  `id`                          int(11)       NOT NULL AUTO_INCREMENT,

  -- ── Identification du dossier ────────────────────────────────
  `pv_id`                       int(11)       NOT NULL COMMENT 'PV source',
  `dossier_id`                  int(11)       DEFAULT NULL COMMENT 'Dossier généré après transfert',
  `substitut_id`                int(11)       DEFAULT NULL COMMENT 'Substitut ayant conduit la procédure',
  `date_mise_en_oeuvre`         date          DEFAULT NULL COMMENT 'Date de mise en œuvre de la CRPC',

  -- ── Infraction(s) poursuivie(s) ──────────────────────────────
  `qualification_faits`         text          DEFAULT NULL COMMENT 'Qualification des faits',
  `date_faits`                  date          DEFAULT NULL COMMENT 'Date des faits',
  `texte_applicable`            text          DEFAULT NULL COMMENT 'Texte légal applicable (articles, lois)',
  `peine_prevue`                varchar(500)  DEFAULT NULL COMMENT 'Peine prévue par les textes',

  -- ── Choix du conseil ─────────────────────────────────────────
  `assistance_avocat`           tinyint(1)    DEFAULT 0  COMMENT '1 = assistance d'un avocat Oui',
  `renonciation_avocat`         tinyint(1)    DEFAULT 0  COMMENT '1 = renonciation expresse à un avocat',
  `nom_avocat`                  varchar(255)  DEFAULT NULL COMMENT 'Nom de l'avocat si assistance',

  -- ── Peine proposée par le substitut ─────────────────────────
  `peine_emprisonnement`        varchar(255)  DEFAULT NULL COMMENT 'Peine d'emprisonnement proposée',
  `sursis_substitut`            tinyint(1)    DEFAULT 0  COMMENT 'Sursis proposé par le substitut',
  `amende_proposee`             decimal(15,2) DEFAULT NULL COMMENT 'Montant amende proposée (FCFA)',

  -- ── Homologation du Président du Tribunal ───────────────────
  `date_audience_homologation`  date          DEFAULT NULL COMMENT 'Date audience d'homologation',
  `homologation`                tinyint(1)    DEFAULT NULL COMMENT '1=Oui, 0=Non, NULL=En attente',
  `peine_emprisonnement_homo`   varchar(500)  DEFAULT NULL COMMENT 'Peine d'emprisonnement homologuée',
  `sursis_homologue`            tinyint(1)    DEFAULT 0  COMMENT 'Sursis homologué',
  `amende_homologuee`           decimal(15,2) DEFAULT NULL COMMENT 'Amende homologuée (FCFA)',
  `motif_refus_homologation`    text          DEFAULT NULL COMMENT 'Motif du refus si homologation = Non',

  -- ── Statut CRPC ──────────────────────────────────────────────
  `statut`                      enum('en_cours','homologuee','refusee','abandonnee')
                                              NOT NULL DEFAULT 'en_cours',

  -- ── Metadata ─────────────────────────────────────────────────
  `notes`                       text          DEFAULT NULL,
  `created_by`                  int(11)       DEFAULT NULL,
  `created_at`                  timestamp     NOT NULL DEFAULT current_timestamp(),
  `updated_at`                  timestamp     NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),

  PRIMARY KEY (`id`),
  KEY `idx_crpc_pv`      (`pv_id`),
  KEY `idx_crpc_dossier` (`dossier_id`),
  KEY `idx_crpc_sub`     (`substitut_id`),
  CONSTRAINT `crpc_pv_fk`       FOREIGN KEY (`pv_id`)       REFERENCES `pv`      (`id`) ON DELETE CASCADE,
  CONSTRAINT `crpc_dossier_fk`  FOREIGN KEY (`dossier_id`)  REFERENCES `dossiers`(`id`) ON DELETE SET NULL,
  CONSTRAINT `crpc_sub_fk`      FOREIGN KEY (`substitut_id`) REFERENCES `users`  (`id`) ON DELETE SET NULL,
  CONSTRAINT `crpc_creator_fk`  FOREIGN KEY (`created_by`)  REFERENCES `users`   (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Dossiers CRPC — Comparution sur Reconnaissance Préalable de Culpabilité';

-- Table pour les personnes poursuivies d'un dossier CRPC (section II de la fiche)
CREATE TABLE IF NOT EXISTS `crpc_personnes` (
  `id`            int(11)      NOT NULL AUTO_INCREMENT,
  `crpc_id`       int(11)      NOT NULL,
  `mec_id`        int(11)      DEFAULT NULL COMMENT 'Lien vers mises_en_cause si existant',
  `numero_ordre`  tinyint(3)   DEFAULT NULL COMMENT 'N° ordre (1,2,3…)',
  `nom_prenom`    varchar(255) NOT NULL,
  `sexe`          enum('M','F','Autre') DEFAULT NULL,
  `age`           tinyint(3)   DEFAULT NULL,
  `nationalite`   varchar(100) DEFAULT 'Nigérienne',
  `profession`    varchar(200) DEFAULT NULL,
  `quartier`      varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_crpcp_crpc` (`crpc_id`),
  CONSTRAINT `crpcp_crpc_fk` FOREIGN KEY (`crpc_id`) REFERENCES `crpc_dossiers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Personnes poursuivies dans un dossier CRPC';
