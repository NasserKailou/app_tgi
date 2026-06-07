-- ============================================================
-- Migration 003 — Données géographiques complètes du Niger
-- Source : RENALOC_COMMUNE_NIGER.xlsx (INS Niger)
-- Codification : Région (1 chiffre), Département (3 chiffres), Commune (4 chiffres)
-- ============================================================

-- Vider et repeupler les tables géographiques
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE communes;
TRUNCATE TABLE departements;
TRUNCATE TABLE regions;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- RÉGIONS (8 régions + Niamey CUN)
-- Code INS : 1=Agadez, 2=Diffa, 3=Dosso, 4=Maradi,
--            5=Tahoua, 6=Tillabéri, 7=Zinder, 8=Niamey
-- ============================================================
INSERT INTO `regions` (`id`, `nom`, `code`) VALUES
(1, 'Agadez',    '1'),
(2, 'Diffa',     '2'),
(3, 'Dosso',     '3'),
(4, 'Maradi',    '4'),
(5, 'Tahoua',    '5'),
(6, 'Tillabéri', '6'),
(7, 'Zinder',    '7'),
(8, 'Niamey',    '8');

-- ============================================================
-- DÉPARTEMENTS (63 départements + CUN Niamey)
-- Code INS : 3 chiffres — premier = code région
-- Source RENALOC : ADERBISSINAT=101, AGADEZ=102, ARLIT=103,
--   BILMA=104, IFEROUANE=105, INGALL=106, TASSARA=107,
--   TCHINTABARADEN=108, TCHIROZERINE=109
--   BOSSO=201, DIFFA=202, GOUDOUMARIA=203, MAINE SOROA=204,
--   N'GOURTI=205, N'GUIGMI=206
--   BOBOYE=301, DIOUNDIOU=302, DOGONDOUTCHI=303, DOSSO=304,
--   FALMEY=305, GAYA=306, LOGA=307, TIBIRI=308
--   AGUIE=401, DAKORO=402, GAZAOUA=403, GUIDAN ROUMDJI=404,
--   MADAROUNFA=405, MAYAHI=406, TESSAOUA=407, MARADI=408
--   ABALAK=501, BAGAROUA=502, BIRNI N'KONNI=503, BOUZA=504,
--   ILLELA=505, KEITA=506, MADAOUA=507, MALBAZA=508,
--   TAHOUA=509, TAKEITA=510, TILLIA=511, TAHOUA COMMUNE=512
--   ABALA=601, AYOROU=602, BALLEYARA=603, BANIBANGOU=604,
--   BANKILARE=605, FILINGUE=606, GOTHEYE=607, KOLLO=608,
--   OUALLAM=609, SAY=610, TERA=611, TILLABERI=612, TORODI=613
--   BELBEDJI=701, BERMO=702, DAMAGARAM TAKAYA=703, DUNGASS=704,
--   GOURE=705, KANTCHE=706, MAGARIA=707, MIRRIAH=708,
--   TANOUT=709, TESKER=710, ZINDER=711
--   NIAMEY=801
-- ============================================================
INSERT INTO `departements` (`id`, `region_id`, `nom`, `code`) VALUES
-- AGADEZ
(1,  1, 'Aderbissinat',      '101'),
(2,  1, 'Agadez',            '102'),
(3,  1, 'Arlit',             '103'),
(4,  1, 'Bilma',             '104'),
(5,  1, 'Iferouane',         '105'),
(6,  1, 'Ingall',            '106'),
(7,  1, 'Tassara',           '107'),
(8,  1, 'Tchintabaraden',    '108'),
(9,  1, 'Tchirozerine',      '109'),
-- DIFFA
(10, 2, 'Bosso',             '201'),
(11, 2, 'Diffa',             '202'),
(12, 2, 'Goudoumaria',       '203'),
(13, 2, 'Maïné Soroa',       '204'),
(14, 2, 'N''Gourti',         '205'),
(15, 2, 'N''Guigmi',         '206'),
-- DOSSO
(16, 3, 'Boboye',            '301'),
(17, 3, 'Dioundiou',         '302'),
(18, 3, 'Dogondoutchi',      '303'),
(19, 3, 'Dosso',             '304'),
(20, 3, 'Falmey',            '305'),
(21, 3, 'Gaya',              '306'),
(22, 3, 'Loga',              '307'),
(23, 3, 'Tibiri',            '308'),
-- MARADI
(24, 4, 'Aguié',             '401'),
(25, 4, 'Dakoro',            '402'),
(26, 4, 'Gazaoua',           '403'),
(27, 4, 'Guidan Roumdji',    '404'),
(28, 4, 'Madarounfa',        '405'),
(29, 4, 'Mayahi',            '406'),
(30, 4, 'Tessaoua',          '407'),
(31, 4, 'Maradi',            '408'),
-- TAHOUA
(32, 5, 'Abalak',            '501'),
(33, 5, 'Bagaroua',          '502'),
(34, 5, 'Birni N''Konni',    '503'),
(35, 5, 'Bouza',             '504'),
(36, 5, 'Illéla',            '505'),
(37, 5, 'Keita',             '506'),
(38, 5, 'Madaoua',           '507'),
(39, 5, 'Malbaza',           '508'),
(40, 5, 'Tahoua',            '509'),
(41, 5, 'Takanamatt',        '510'),
(42, 5, 'Tillia',            '511'),
(43, 5, 'Ville de Tahoua',   '512'),
-- TILLABÉRI
(44, 6, 'Abala',             '601'),
(45, 6, 'Ayorou',            '602'),
(46, 6, 'Balleyara',         '603'),
(47, 6, 'Banibangou',        '604'),
(48, 6, 'Bankilaré',         '605'),
(49, 6, 'Filingué',          '606'),
(50, 6, 'Gothèye',           '607'),
(51, 6, 'Kollo',             '608'),
(52, 6, 'Ouallam',           '609'),
(53, 6, 'Say',               '610'),
(54, 6, 'Téra',              '611'),
(55, 6, 'Tillabéri',         '612'),
(56, 6, 'Torodi',            '613'),
-- ZINDER
(57, 7, 'Belbédji',          '701'),
(58, 7, 'Bermo',             '702'),
(59, 7, 'Damagaram Takaya',  '703'),
(60, 7, 'Dungass',           '704'),
(61, 7, 'Gouré',             '705'),
(62, 7, 'Kantché',           '706'),
(63, 7, 'Magaria',           '707'),
(64, 7, 'Mirriah',           '708'),
(65, 7, 'Tanout',            '709'),
(66, 7, 'Tesker',            '710'),
(67, 7, 'Zinder',            '711'),
-- NIAMEY
(68, 8, 'Niamey',            '801');

-- ============================================================
-- COMMUNES (265 communes)
-- Code INS : 4 chiffres — premiers 3 = code département
-- ============================================================
INSERT INTO `communes` (`id`, `departement_id`, `nom`, `code`) VALUES
-- AGADEZ / Aderbissinat (dep 1)
(1,  1, 'Aderbissinat',             '1011'),
-- AGADEZ / Agadez (dep 2)
(2,  2, 'Agadez',                   '1021'),
-- AGADEZ / Arlit (dep 3)
(3,  3, 'Arlit',                    '1031'),
(4,  3, 'Dannet',                   '1032'),
(5,  3, 'Gougaram',                 '1033'),
-- AGADEZ / Bilma (dep 4)
(6,  4, 'Bilma',                    '1041'),
(7,  4, 'Dirkou',                   '1042'),
(8,  4, 'Djado',                    '1043'),
(9,  4, 'Fachi',                    '1044'),
-- AGADEZ / Iferouane (dep 5)
(10, 5, 'Iferouane',                '1051'),
(11, 5, 'Timia',                    '1052'),
-- AGADEZ / Ingall (dep 6)
(12, 6, 'Ingall',                   '1061'),
-- AGADEZ / Tassara (dep 7)
(13, 7, 'Tassara',                  '1071'),
-- AGADEZ / Tchintabaraden (dep 8)
(14, 8, 'Kao',                      '1081'),
(15, 8, 'Tchintabaraden',           '1082'),
-- AGADEZ / Tchirozerine (dep 9)
(16, 9, 'Dabaga',                   '1091'),
(17, 9, 'Tabelot',                  '1092'),
(18, 9, 'Tchirozerine',             '1093'),
-- DIFFA / Bosso (dep 10)
(19, 10, 'Bosso',                   '2011'),
(20, 10, 'Toumour',                 '2012'),
-- DIFFA / Diffa (dep 11)
(21, 11, 'Chétimari',               '2021'),
(22, 11, 'Diffa',                   '2022'),
(23, 11, 'Gueskérou',               '2023'),
-- DIFFA / Goudoumaria (dep 12)
(24, 12, 'Goudoumaria',             '2031'),
-- DIFFA / Maïné Soroa (dep 13)
(25, 13, 'Foulatari',               '2041'),
(26, 13, 'Maïné Soroa',             '2042'),
(27, 13, 'N''Gelbeyli',             '2043'),
-- DIFFA / N'Gourti (dep 14)
(28, 14, 'N''Gourti',               '2051'),
-- DIFFA / N'Guigmi (dep 15)
(29, 15, 'Kabelawa',                '2061'),
(30, 15, 'N''Guigmi',               '2062'),
-- DOSSO / Boboye (dep 16)
(31, 16, 'Birni N''Gaouré',         '3011'),
(32, 16, 'Fabidji',                 '3012'),
(33, 16, 'Fakara',                  '3013'),
(34, 16, 'Harika',                  '3014'),
(35, 16, 'Kankandi',                '3015'),
(36, 16, 'Kiota',                   '3016'),
(37, 16, 'Koygolo',                 '3017'),
(38, 16, 'N''Gonga',                '3018'),
-- DOSSO / Dioundiou (dep 17)
(39, 17, 'Dioundiou',               '3021'),
(40, 17, 'Kara Kara',               '3022'),
(41, 17, 'Zabori',                  '3023'),
-- DOSSO / Dogondoutchi (dep 18)
(42, 18, 'Dan Kassari',             '3031'),
(43, 18, 'Dogon Kiria',             '3032'),
(44, 18, 'Dogondoutchi',            '3033'),
(45, 18, 'Kiéché',                  '3034'),
(46, 18, 'Matankari',               '3035'),
(47, 18, 'Soucoucoutane',           '3036'),
-- DOSSO / Dosso (dep 19)
(48, 19, 'Dosso',                   '3041'),
(49, 19, 'Farrey',                  '3042'),
(50, 19, 'Garankedeye',             '3043'),
(51, 19, 'Goberi',                  '3044'),
(52, 19, 'Gorouol Bangou',          '3045'),
(53, 19, 'Kargui Bangou',           '3046'),
(54, 19, 'Mokko',                   '3047'),
(55, 19, 'Sakadamna',               '3048'),
(56, 19, 'Sambera',                 '3049'),
(57, 19, 'Tessa',                   '3050'),
(58, 19, 'Tombo Koarey',            '3051'),
-- DOSSO / Falmey (dep 20)
(59, 20, 'Falmey',                  '3051'),
(60, 20, 'Guilladjé',               '3052'),
-- DOSSO / Gaya (dep 21)
(61, 21, 'Bana',                    '3061'),
(62, 21, 'Bengou',                  '3062'),
(63, 21, 'Gaya',                    '3063'),
(64, 21, 'Tanda',                   '3064'),
(65, 21, 'Tounouga',                '3065'),
(66, 21, 'Yélou',                   '3066'),
-- DOSSO / Loga (dep 22)
(67, 22, 'Falwel',                  '3071'),
(68, 22, 'Loga',                    '3072'),
(69, 22, 'Sokorbe',                 '3073'),
-- DOSSO / Tibiri (dep 23)
(70, 23, 'Doumega',                 '3081'),
(71, 23, 'Guéchémé',                '3082'),
(72, 23, 'Koré Maïroua',            '3083'),
(73, 23, 'Tibiri',                  '3084'),
-- MARADI / Aguié (dep 24)
(74, 24, 'Aguié',                   '4011'),
(75, 24, 'Tchadoua',                '4012'),
-- MARADI / Dakoro (dep 25)
(76, 25, 'Adjékoria',               '4021'),
(77, 25, 'Azagor',                  '4022'),
(78, 25, 'Bader Goula',             '4023'),
(79, 25, 'Birnin Lallé',            '4024'),
(80, 25, 'Dakoro',                  '4025'),
(81, 25, 'Dan Goulbi',              '4026'),
(82, 25, 'Korahanné',               '4027'),
(83, 25, 'Kornaka',                 '4028'),
(84, 25, 'Maïyara',                 '4029'),
(85, 25, 'Roumbou',                 '4030'),
(86, 25, 'Sabon Machi',             '4031'),
(87, 25, 'Tagriss',                 '4032'),
-- MARADI / Gazaoua (dep 26)
(88, 26, 'Gangara',                 '4031'),
(89, 26, 'Gazaoua',                 '4032'),
-- MARADI / Guidan Roumdji (dep 27)
(90, 27, 'Chadakori',               '4041'),
(91, 27, 'Guidan Roumdji',          '4042'),
(92, 27, 'Guidan Sori',             '4043'),
(93, 27, 'Saé Saboua',              '4044'),
(94, 27, 'Tibiri',                  '4045'),
-- MARADI / Madarounfa (dep 28)
(95, 28, 'Dan Issa',                '4051'),
(96, 28, 'Djirataoua',              '4052'),
(97, 28, 'Gabi',                    '4053'),
(98, 28, 'Madarounfa',              '4054'),
(99, 28, 'Safo',                    '4055'),
(100,28, 'Serkin Yamma',            '4056'),
-- MARADI / Mayahi (dep 29)
(101,29, 'Attantané',               '4061'),
(102,29, 'El Allassane Maïrerey',   '4062'),
(103,29, 'Guidan Amoumoune',        '4063'),
(104,29, 'Issawane',                '4064'),
(105,29, 'Kanembakaché',            '4065'),
(106,29, 'Mayahi',                  '4066'),
(107,29, 'Serkin Haoussa',          '4067'),
(108,29, 'Tchaké',                  '4068'),
-- MARADI / Tessaoua (dep 30)
(109,30, 'Baoudéta',                '4071'),
(110,30, 'Hawandawaki',             '4072'),
(111,30, 'Koona',                   '4073'),
(112,30, 'Korgom',                  '4074'),
(113,30, 'Maïjirgui',               '4075'),
(114,30, 'Ourafane',                '4076'),
(115,30, 'Tessaoua',                '4077'),
-- MARADI / Maradi (dep 31)
(116,31, 'Maradi 1',                '4081'),
(117,31, 'Maradi 2',                '4082'),
(118,31, 'Maradi 3',                '4083'),
-- TAHOUA / Abalak (dep 32)
(119,32, 'Abalak',                  '5011'),
(120,32, 'Akoubounou',              '5012'),
(121,32, 'Azèye',                   '5013'),
(122,32, 'Tabalak',                 '5014'),
(123,32, 'Tamaya',                  '5015'),
-- TAHOUA / Bagaroua (dep 33)
(124,33, 'Bagaroua',                '5021'),
-- TAHOUA / Birni N'Konni (dep 34)
(125,34, 'Allela',                  '5031'),
(126,34, 'Bazaga',                  '5032'),
(127,34, 'Birni N''Konni',          '5033'),
(128,34, 'Tsernaoua',               '5034'),
-- TAHOUA / Bouza (dep 35)
(129,35, 'Allakeye',                '5041'),
(130,35, 'Baban Katami',            '5042'),
(131,35, 'Bouza',                   '5043'),
(132,35, 'Déoulé',                  '5044'),
(133,35, 'Karofane',                '5045'),
(134,35, 'Tabotaki',                '5046'),
(135,35, 'Tama',                    '5047'),
-- TAHOUA / Illéla (dep 36)
(136,36, 'Badaguichiri',            '5051'),
(137,36, 'Illéla',                  '5052'),
(138,36, 'Tajaé',                   '5053'),
-- TAHOUA / Keita (dep 37)
(139,37, 'Garhanga',                '5061'),
(140,37, 'Ibohamane',               '5062'),
(141,37, 'Keita',                   '5063'),
(142,37, 'Tamaské',                 '5064'),
-- TAHOUA / Madaoua (dep 38)
(143,38, 'Azarori',                 '5071'),
(144,38, 'Bangui',                  '5072'),
(145,38, 'Galma Koudawatché',       '5073'),
(146,38, 'Madaoua',                 '5074'),
(147,38, 'Ourno',                   '5075'),
(148,38, 'Sabon Guida',             '5076'),
-- TAHOUA / Malbaza (dep 39)
(149,39, 'Dogueraoua',              '5081'),
(150,39, 'Malbaza',                 '5082'),
-- TAHOUA / Tahoua (dep 40)
(151,40, 'Afala',                   '5091'),
(152,40, 'Bambeye',                 '5092'),
(153,40, 'Barmou',                  '5093'),
(154,40, 'Kalfou',                  '5094'),
(155,40, 'Takanamatt',              '5095'),
(156,40, 'Tébaram',                 '5096'),
-- TAHOUA / Takanamatt (dep 41)
(157,41, 'Dakoussa',                '5101'),
(158,41, 'Garagoumsa',              '5102'),
(159,41, 'Tirmini',                 '5103'),
-- TAHOUA / Tillia (dep 42)
(160,42, 'Tillia',                  '5111'),
-- TAHOUA / Ville de Tahoua (dep 43)
(161,43, 'Tahoua Commune 1',        '5121'),
(162,43, 'Tahoua Commune 2',        '5122'),
-- TILLABÉRI / Abala (dep 44)
(163,44, 'Abala',                   '6011'),
(164,44, 'Sanam',                   '6012'),
-- TILLABÉRI / Ayorou (dep 45)
(165,45, 'Ayorou',                  '6021'),
(166,45, 'Inatès',                  '6022'),
-- TILLABÉRI / Balleyara (dep 46)
(167,46, 'Tagazar',                 '6031'),
-- TILLABÉRI / Banibangou (dep 47)
(168,47, 'Banibangou',              '6041'),
-- TILLABÉRI / Bankilaré (dep 48)
(169,48, 'Bankilaré',               '6051'),
-- TILLABÉRI / Filingué (dep 49)
(170,49, 'Damana',                  '6061'),
(171,49, 'Filingué',                '6062'),
(172,49, 'Imanan',                  '6063'),
(173,49, 'Kourféye Centre',         '6064'),
-- TILLABÉRI / Gothèye (dep 50)
(174,50, 'Dargol',                  '6071'),
(175,50, 'Gothèye',                 '6072'),
-- TILLABÉRI / Kollo (dep 51)
(176,51, 'Bitinkodji',              '6081'),
(177,51, 'Dantchandou',             '6082'),
(178,51, 'Hamdallaye',              '6083'),
(179,51, 'Karma',                   '6084'),
(180,51, 'Kirtachi',                '6085'),
(181,51, 'Kollo',                   '6086'),
(182,51, 'Kouré',                   '6087'),
(183,51, 'Liboré',                  '6088'),
(184,51, 'N''Dounga',               '6089'),
(185,51, 'Namaro',                  '6090'),
(186,51, 'Youri',                   '6091'),
-- TILLABÉRI / Ouallam (dep 52)
(187,52, 'Dingazi Banda',           '6091'),
(188,52, 'Ouallam',                 '6092'),
(189,52, 'Simiri',                  '6093'),
(190,52, 'Tondikiwindi',            '6094'),
-- TILLABÉRI / Say (dep 53)
(191,53, 'Ouro Gueladio',           '6101'),
(192,53, 'Say',                     '6102'),
(193,53, 'Tamou',                   '6103'),
-- TILLABÉRI / Téra (dep 54)
(194,54, 'Diagourou',               '6111'),
(195,54, 'Goroual',                 '6112'),
(196,54, 'Kokorou',                 '6113'),
(197,54, 'Méhana',                  '6114'),
(198,54, 'Téra',                    '6115'),
-- TILLABÉRI / Tillabéri (dep 55)
(199,55, 'Anzourou',                '6121'),
(200,55, 'Bibiyergou',              '6122'),
(201,55, 'Dessa',                   '6123'),
(202,55, 'Kourteye',                '6124'),
(203,55, 'Sakoïra',                 '6125'),
(204,55, 'Sindar',                  '6126'),
(205,55, 'Tillabéri',               '6127'),
-- TILLABÉRI / Torodi (dep 56)
(206,56, 'Makalondi',               '6131'),
(207,56, 'Torodi',                  '6132'),
-- ZINDER / Belbédji (dep 57)
(208,57, 'Tarka',                   '7011'),
-- ZINDER / Bermo (dep 58)
(209,58, 'Bermo',                   '7021'),
(210,58, 'Gadabédji',               '7022'),
-- ZINDER / Damagaram Takaya (dep 59)
(211,59, 'Alberkaram',              '7031'),
(212,59, 'Damagaram Takaya',        '7032'),
(213,59, 'Guidimouni',              '7033'),
(214,59, 'Kagna Wame',              '7034'),
(215,59, 'Mazamni',                 '7035'),
(216,59, 'Moa',                     '7036'),
-- ZINDER / Dungass (dep 60)
(217,60, 'Dogo Dogo',               '7041'),
(218,60, 'Dungass',                 '7042'),
(219,60, 'Gouchi',                  '7043'),
(220,60, 'Mallaoua',                '7044'),
-- ZINDER / Gouré (dep 61)
(221,61, 'Alakos',                  '7051'),
(222,61, 'Bouné',                   '7052'),
(223,61, 'Gamou',                   '7053'),
(224,61, 'Gouré',                   '7054'),
(225,61, 'Guidiguir',               '7055'),
(226,61, 'Kellé',                   '7056'),
-- ZINDER / Kantché (dep 62)
(227,62, 'Dan Barto',               '7061'),
(228,62, 'Daoutché',                '7062'),
(229,62, 'Doungou',                 '7063'),
(230,62, 'Ichernaoua',              '7064'),
(231,62, 'Kantché',                 '7065'),
(232,62, 'Kourni',                  '7066'),
(233,62, 'Matamèye',                '7067'),
(234,62, 'Tsouni',                  '7068'),
(235,62, 'Yaouri',                  '7069'),
-- ZINDER / Magaria (dep 63)
(236,63, 'Bandé',                   '7071'),
(237,63, 'Dan Tchio',               '7072'),
(238,63, 'Kouaya',                  '7073'),
(239,63, 'Magaria',                 '7074'),
(240,63, 'Sassoumdoum',             '7075'),
(241,63, 'Wacha',                   '7076'),
(242,63, 'Yékoua',                  '7077'),
-- ZINDER / Mirriah (dep 64)
(243,64, 'Dala Koleram',            '7081'),
(244,64, 'Dogo',                    '7082'),
(245,64, 'Droum',                   '7083'),
(246,64, 'Gaffati',                 '7084'),
(247,64, 'Gouna',                   '7085'),
(248,64, 'Hamdara',                 '7086'),
(249,64, 'Mirriah',                 '7087'),
(250,64, 'Zermou',                  '7088'),
-- ZINDER / Tanout (dep 65)
(251,65, 'Falenko',                 '7091'),
(252,65, 'Gangara',                 '7092'),
(253,65, 'Olléléwa',                '7093'),
(254,65, 'Tanout',                  '7094'),
(255,65, 'Tenhya',                  '7095'),
-- ZINDER / Tesker (dep 66)
(256,66, 'Tesker',                  '7101'),
-- ZINDER / Zinder (dep 67)
(257,67, 'Zinder 1',                '7111'),
(258,67, 'Zinder 2',                '7112'),
(259,67, 'Zinder 3',                '7113'),
(260,67, 'Zinder 4',                '7114'),
(261,67, 'Zinder 5',                '7115'),
-- NIAMEY / Niamey (dep 68)
(262,68, 'Niamey 1',                '8011'),
(263,68, 'Niamey 2',                '8012'),
(264,68, 'Niamey 3',                '8013'),
(265,68, 'Niamey 4',                '8014'),
(266,68, 'Niamey 5',                '8015');

SELECT CONCAT('Migration 003 OK — ', COUNT(*), ' communes chargées') AS status FROM communes;
