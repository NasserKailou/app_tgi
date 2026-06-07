-- ============================================================
-- MIGRATION 006 — Données initiales TGI-NY
-- Date    : 2026-04-28
-- Objet   : Insérer les substituts, unités d'enquête,
--          primo-intervenants et cabinets d'instruction réels.
-- Pré-requis : exécuter d'abord 005_reset_donnees_metier.sql
-- ============================================================

SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 1;

-- Mot de passe par défaut pour tous les substituts : Admin@2026
-- Hash bcrypt cost 12 (identique à celui des comptes existants)
SET @default_password = '$2y$12$QOBYKWWfAWXEae1fpkEUFOH/JJvtCOqA0nwH/FKzzSPs.84nmc5Ym';

-- ────────────────────────────────────────────────────────────
-- 1. SUBSTITUTS DU PROCUREUR
--    role_id = 5 (substitut_procureur)
--    fonction_parquet_id : 3 = Premier Substitut, 4+ = Substituts
--    Note : les fonctions_parquet existantes vont jusqu'à 23,
--           on réutilise les codes existants
-- ────────────────────────────────────────────────────────────

-- Premiers Substituts (3) — fonctions 3 à 5 (substitut_1, _2, _3 réinterprétés
-- comme "Premier substitut N°1 à N°3"). Si tu préfères des codes dédiés,
-- on peut créer des entrées dans fonctions_parquet — voir bloc commenté plus bas.

INSERT INTO `users`
    (`role_id`, `fonction_parquet_id`, `nom`, `prenom`, `email`, `password`, `matricule`, `actif`)
VALUES
-- ─── PREMIERS SUBSTITUTS DU PROCUREUR ───
(5, 3, 'MASSI',                 'Zara',                       'zara.massi@tgi-niamey.ne',                 @default_password, 'PSUB-001', 1),
(5, 4, 'CHINA AMADOU KOURGUENI','Ali',                        'ali.china@tgi-niamey.ne',                  @default_password, 'PSUB-002', 1),
(5, 5, 'MAMADOU SADOU',         'Fatouma',                    'fatouma.mamadou@tgi-niamey.ne',            @default_password, 'PSUB-003', 1),

-- ─── SUBSTITUTS DU PROCUREUR ───
(5,  6, 'YOUNOUSSI',             'Soumana',                   'soumana.younoussi@tgi-niamey.ne',          @default_password, 'SUB-001', 1),
(5,  7, 'AMADOU BADAMASSI',      'Issa',                      'issa.amadou@tgi-niamey.ne',                @default_password, 'SUB-002', 1),
(5,  8, 'BEIDOU DJAMILOU',       'Alou',                      'alou.beidou@tgi-niamey.ne',                @default_password, 'SUB-003', 1),
(5,  9, 'HAMANI HANTAROU',       'Hamadou',                   'hamadou.hamani@tgi-niamey.ne',             @default_password, 'SUB-004', 1),
(5, 10, 'ABDOURAHAMANE',         'Nafissa Youssouf',          'nafissa.abdourahamane@tgi-niamey.ne',      @default_password, 'SUB-005', 1),
(5, 11, 'OUSSEINI',              'Garba',                     'garba.ousseini@tgi-niamey.ne',             @default_password, 'SUB-006', 1),
(5, 12, 'MAHAMADOU',             'Inoussa',                   'inoussa.mahamadou@tgi-niamey.ne',          @default_password, 'SUB-007', 1),
(5, 13, 'ADAMOU OUMAROU',        'Mahamadou',                 'mahamadou.adamou@tgi-niamey.ne',           @default_password, 'SUB-008', 1),
(5, 14, 'LAOUALI DIT OUMA',      'Moussa',                    'moussa.laouali@tgi-niamey.ne',             @default_password, 'SUB-009', 1),
(5, 15, 'HAMANI MOUSTAPHA',      'Hassane',                   'hassane.hamani@tgi-niamey.ne',             @default_password, 'SUB-010', 1);

-- ────────────────────────────────────────────────────────────
-- 2. UNITÉS D'ENQUÊTE (Ville de Niamey)
-- ────────────────────────────────────────────────────────────
INSERT INTO `unites_enquete` (`nom`, `type`, `actif`) VALUES
('SCLCT/CTO',                                  'unite_speciale',  1),
('DPJ — Direction de la Police Judiciaire',    'brigade_police',  1),
('DST — Direction de la Surveillance du Territoire', 'unite_speciale', 1),
('OCRTIS — Office Central de Répression du Trafic Illicite des Stupéfiants', 'unite_speciale', 1),
('CCN — Commissariat Central de Niamey',       'commissariat',    1),
('DPMF/PN — Direction de la Police des Mœurs et des Mineurs', 'brigade_police', 1),
('CCPFM/GN — Compagnie de Circulation et de Police Routière', 'gendarmerie', 1),
('Section des Recherches / GN',                 'gendarmerie',     1),
('CP Rive Droite',                              'commissariat',    1),
('CP Niamey 2000',                              'commissariat',    1),
('Brigade des Pistes / GNN',                    'gendarmerie',     1),
('CP Boukoki',                                  'commissariat',    1),
('CP Koubia',                                   'commissariat',    1),
('CP Aéroport',                                 'commissariat',    1),
('CP Francophonie',                             'commissariat',    1),
('Brigade Fluviale / GN',                       'gendarmerie',     1),
('Brigade de Recherche Koira Tégui / GN',       'gendarmerie',     1),
('CP Route Kollo',                              'commissariat',    1),
('CP Kirkissoye',                               'commissariat',    1),
('CP Kalley Plateau',                           'commissariat',    1),
('CP Talladjé',                                 'commissariat',    1);

-- ────────────────────────────────────────────────────────────
-- 3. PRIMO-INTERVENANTS
-- ────────────────────────────────────────────────────────────
INSERT INTO `primo_intervenants` (`nom`, `type`, `description`, `actif`) VALUES
('F.A.N',  'Armée',        'Forces Armées Nigériennes',           1),
('G.N',    'Gendarmerie',  'Gendarmerie Nationale',               1),
('G.N.N',  'Gendarmerie',  'Garde Nationale du Niger',            1),
('P.N',    'Police',       'Police Nationale',                    1),
('E&F',    'Environnement','Eaux et Forêts',                      1),
('D.N',    'Douanes',      'Direction Générale des Douanes',      1);

-- ────────────────────────────────────────────────────────────
-- 4. CABINETS D'INSTRUCTION
--    Organisés en 3 pôles : Droit Commun, Économique & Financier, Antiterroriste
-- ────────────────────────────────────────────────────────────
INSERT INTO `cabinets_instruction` (`numero`, `libelle`, `actif`) VALUES
-- ─── DROIT COMMUN (8 cabinets) ───
('DC-DOYEN', 'Cabinet Doyen Droit Commun',          1),
('DC-01',    '1er Cabinet Droit Commun',            1),
('DC-02',    '2e Cabinet Droit Commun',             1),
('DC-03',    '3e Cabinet Droit Commun',             1),
('DC-04',    '4e Cabinet Droit Commun',             1),
('DC-05',    '5e Cabinet Droit Commun',             1),
('DC-MIN-01','1er Cabinet Mineurs',                 1),
('DC-MIN-02','2e Cabinet Mineurs',                  1),

-- ─── PÔLE ÉCONOMIQUE ET FINANCIER (5 cabinets) ───
('ECO-DOYEN','Cabinet Doyen Économique et Financier',1),
('ECO-01',   '1er Cabinet Économique et Financier',  1),
('ECO-02',   '2e Cabinet Économique et Financier',   1),
('ECO-03',   '3e Cabinet Économique et Financier',   1),
('ECO-04',   '4e Cabinet Économique et Financier',   1),

-- ─── PÔLE ANTITERRORISTE (10 cabinets) ───
('PAT-DOYEN','Cabinet Doyen Pôle Antiterroriste',    1),
('PAT-01',   '1er Cabinet Pôle Antiterroriste',      1),
('PAT-02',   '2e Cabinet Pôle Antiterroriste',       1),
('PAT-03',   '3e Cabinet Pôle Antiterroriste',       1),
('PAT-04',   '4e Cabinet Pôle Antiterroriste',       1),
('PAT-05',   '5e Cabinet Pôle Antiterroriste',       1),
('PAT-06',   '6e Cabinet Pôle Antiterroriste',       1),
('PAT-07',   '7e Cabinet Pôle Antiterroriste',       1),
('PAT-08',   '8e Cabinet Pôle Antiterroriste',       1),
('PAT-MIN',  'Cabinet Mineurs Pôle Antiterroriste',  1);

-- ────────────────────────────────────────────────────────────
-- 5. (Optionnel) Création de fonctions_parquet dédiées
--    Décommente ce bloc si tu veux des libellés "Premier Substitut"
--    plutôt que "Substitut N°1/2/3" pour les 3 premiers
-- ────────────────────────────────────────────────────────────
-- INSERT IGNORE INTO `fonctions_parquet` (`code`, `libelle`, `type_role`, `ordre`, `actif`) VALUES
-- ('premier_substitut_1', 'Premier Substitut du Procureur N°1', 'substitut',  3, 1),
-- ('premier_substitut_2', 'Premier Substitut du Procureur N°2', 'substitut',  4, 1),
-- ('premier_substitut_3', 'Premier Substitut du Procureur N°3', 'substitut',  5, 1);

SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;

-- ────────────────────────────────────────────────────────────
-- Confirmation
-- ────────────────────────────────────────────────────────────
SELECT
    (SELECT COUNT(*) FROM users WHERE role_id = 5)         AS substituts,
    (SELECT COUNT(*) FROM unites_enquete)                  AS unites_enquete,
    (SELECT COUNT(*) FROM primo_intervenants)              AS primo_intervenants,
    (SELECT COUNT(*) FROM cabinets_instruction)            AS cabinets,
    'Migration 006 : données initiales chargées' AS statut;
