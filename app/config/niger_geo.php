<?php
/**
 * niger_geo.php — Référentiel géographique du Niger
 * Régions, départements et communes (266 communes officielles)
 *
 * Utilisé par : CarteController, exports, validations
 */

// ─── Régions ──────────────────────────────────────────────────────────────────
$regions_niger = [
    'Agadez',
    'Diffa',
    'Dosso',
    'Maradi',
    'Niamey',
    'Tahoua',
    'Tillaberi',
    'Zinder',
];

// ─── Départements par région ──────────────────────────────────────────────────
$departements_par_region = [
    'Agadez' => [
        'Aderbisanat',
        'Agadez Ville',
        'Arlit',
        'Bilma',
        'Iferouane',
        'Ingall',
        'Tassara',
        'Tchintabaraden',
        'Tchirozerine',
    ],
    'Diffa' => [
        'Bosso',
        'Diffa',
        'Goudoumaria',
        'Maine Soroa',
        "N'Gourti",
        "N'Guigmi",
    ],
    'Dosso' => [
        'Boboye',
        'Dioundiou',
        'Dogondoutchi',
        'Dosso',
        'Falmey',
        'Gaya',
        'Loga',
        'Tibiri',
    ],
    'Maradi' => [
        'Aguie',
        'Dakoro',
        'Gazaoua',
        'Guidan-Roumdji',
        'Madarounfa',
        'Mayahi',
        'Tessaoua',
        'Ville De Maradi',
    ],
    'Niamey' => [
        'Niamey',
    ],
    'Tahoua' => [
        'Abalak',
        'Bagaroua',
        "Birni N'Konni",
        'Bouza',
        'Illéla',
        'Keita',
        'Madaoua',
        'Malbaza',
        'Tahoua',
        'Takanamatt',
        'Tillia',
        'Ville de Tahoua',
    ],
    'Tillaberi' => [
        'Abala',
        'Ayorou',
        'Balleyara',
        'Banibangou',
        'Bankilare',
        'Filingue',
        'Gotheye',
        'Kollo',
        'Ouallam',
        'Say',
        'Tera',
        'Tillaberi',
        'Torodi',
    ],
    'Zinder' => [
        'Belbedji',
        'Bermo',
        'Damagaram Takaya',
        'Dungass',
        'Goure',
        'Kantche',
        'Magaria',
        'Mirriah',
        'Tanout',
        'Tesker',
        'Ville De Zinder',
    ],
];

// ─── Communes par département ─────────────────────────────────────────────────
$communes_par_departement = [
    // AGADEZ
    'Aderbisanat'        => ['Adebissanat'],
    'Agadez Ville'       => ['Agadez Commune'],
    'Arlit'              => ['Arlit', 'Dannet', 'Gougaram'],
    'Bilma'              => ['Bilma', 'Dirkou', 'Djado', 'Fachi'],
    'Iferouane'          => ['Iferouane', 'Tmia'],
    'Ingall'             => ['Ingall'],
    'Tassara'            => ['Tassara'],
    'Tchintabaraden'     => ['Kao', 'Tchintabaraden'],
    'Tchirozerine'       => ['Dabaga', 'Tabelot', 'Tchirozerine'],

    // DIFFA
    'Bosso'              => ['Bosso', 'Toumour'],
    'Diffa'              => ['Chetimari', 'Diffa Commune', 'Gueskerou'],
    'Goudoumaria'        => ['Goudoumaria'],
    'Maine Soroa'        => ['Foulateri', 'Maine Soroa', "N'Guelbeyli"],
    "N'Gourti"           => ["N'Gourti"],
    "N'Guigmi"           => ['Kabelewa', "N'Guigmi"],

    // DOSSO
    'Boboye'             => ["Birni N'Gaoure", 'Fabidji', 'Fakara', 'Harika-Nassou', 'Kankandi', 'Kiota', 'Koygolo', "N'Gonga"],
    'Dioundiou'          => ['Dioundiou', 'Kara Kara', 'Zabori'],
    'Dogondoutchi'       => ['Dan Kassari', 'Dogon Kiria', 'Dogondoutchi', 'Kieche', 'Matankari', 'Soucoucoutane'],
    'Dosso'              => ['Dosso Commune', 'Farrey', 'Garankedeye', 'Golle', 'Gorouban Kassam', 'Kargui Bangou', 'Mokko', 'Sakadamna', 'Sambera', 'Tessa', 'Tombo Koarey'],
    'Falmey'             => ['Falmey', 'Guilladje'],
    'Gaya'               => ['Bana', 'Bengou', 'Gaya', 'Tanda', 'Tounouga', 'Yelou'],
    'Loga'               => ['Falwel', 'Loga', 'Sokorbe'],
    'Tibiri'             => ['Doumega', 'Guecheme', 'Kore Mairoua', 'Tibiri (Dogondoutchi)'],

    // MARADI
    'Aguie'              => ['Aguie', 'Tchadoua'],
    'Dakoro'             => ['Adjiekoria', 'Azagor', 'Bader Goula', 'Birnin Lalle', 'Dakoro', 'Dan Goulbi', 'Korahane', 'Kornaka', 'Maiyara', 'Roumbou', 'Sabonmachi', 'Tagriss'],
    'Gazaoua'            => ['Gangara', 'Gazaoua'],
    'Guidan-Roumdji'     => ['Chadakori', 'Guidan Roumdji', 'Guidan Sori', 'Sae Saboua', 'Tibiri (Maradi)'],
    'Madarounfa'         => ['Dan Issa', 'Djirataoua', 'Gabi', 'Madarounfa', 'Safo', 'Serki Yama'],
    'Mayahi'             => ['Attantane', 'El Allassan Mairerey', 'Guidan Amoumoune', 'Issawane', 'Kanambakache', 'Mayahi', 'Serkin Haoussa', 'Tchake'],
    'Tessaoua'           => ['Baoudeta', 'Hawandawaki', 'Koona', 'Korgom', 'Maijirgui', 'Ourafane', 'Tessaoua'],
    'Ville De Maradi'    => ['Maradi 1', 'Maradi 2', 'Maradi 3'],

    // NIAMEY
    'Niamey'             => ['Niamey 1', 'Niamey 2', 'Niamey 3', 'Niamey 4', 'Niamey 5'],

    // TAHOUA
    'Abalak'             => ['Abalak', 'Akoubounou', 'Azèye', 'Tabalak', 'Tamaya'],
    'Bagaroua'           => ['Bagaroua'],
    "Birni N'Konni"      => ['Allela', 'Bazaga', "Birni N'Konni", 'Tsernaoua'],
    'Bouza'              => ['Allakeye', 'Baban Katami', 'Bouza', 'Déoulé', 'Karofane', 'Tabotaki', 'Tama'],
    'Illéla'             => ['Badaguichiri', 'Illéla', 'Tajaé'],
    'Keita'              => ['Garhanga', 'Ibohamane', 'Keita', 'Tamaské'],
    'Madaoua'            => ['Azarori', 'Bangui', 'Galma Koudawatché', 'Madaoua', 'Ourno', 'Sabon Guida'],
    'Malbaza'            => ['Dogueraoua', 'Malbaza'],
    'Tahoua'             => ['Afala', 'Bambeye', 'Barmou', 'Kalfou', 'Takanamatt', 'Tébaram'],
    'Takanamatt'         => ['Dakoussa', 'Garagoumsa', 'Tirmini'],
    'Tillia'             => ['Tillia'],
    'Ville de Tahoua'    => ['Tahoua Commune 1', 'Tahoua Commune 2'],

    // TILLABERI
    'Abala'              => ['Abala', 'Sanam'],
    'Ayorou'             => ['Ayorou', 'Inattes'],
    'Balleyara'          => ['Tagazar'],
    'Banibangou'         => ['Banibangou'],
    'Bankilare'          => ['Bankilare'],
    'Filingue'           => ['Damana', 'Filingue', 'Imanan', 'Kourfeye Centre'],
    'Gotheye'            => ['Dargol', 'Gotheye'],
    'Kollo'              => ['Bitinkodji', 'Dantchandou', 'Hamdallaye', 'Karma', 'Kirtachi', 'Kollo', 'Koure', 'Libore', "N'Dounga", 'Namaro', 'Youri'],
    'Ouallam'            => ['Dingazi Banda', 'Ouallam', 'Simiri', 'Tondikiwindi'],
    'Say'                => ['Ouro Gueladio', 'Say', 'Tamou'],
    'Tera'               => ['Diagourou', 'Goroual', 'Kokorou', 'Mehana', 'Tera'],
    'Tillaberi'          => ['Anzourou', 'Bibiyergou', 'Dessa', 'Kourteye', 'Sakoira', 'Sinder', 'Tillaberi'],
    'Torodi'             => ['Makalondi', 'Torodi'],

    // ZINDER
    'Belbedji'           => ['Tarka'],
    'Bermo'              => ['Bermo', 'Gadabedji'],
    'Damagaram Takaya'   => ['Alberkaram', 'Damagaram Takaya', 'Guidimouni', 'Kagna Wame', 'Mazamni', 'Moa'],
    'Dungass'            => ['Dogo Dogo', 'Dungass', 'Gouchi', 'Mallaoua'],
    'Goure'              => ['Alakos', 'Boune', 'Gamou', 'Goure', 'Guidiguir', 'Kelle'],
    'Kantche'            => ['Dan Barto', 'Daoutche', 'Doungou', 'Ichernaoua', 'Kantche', 'Kourni', 'Matameye', 'Tsouni', 'Yaouri'],
    'Magaria'            => ['Bande', 'Dan Tchio', 'Kouaya', 'Magaria', 'Sassoumbroum', 'Wacha', 'Yekoua'],
    'Mirriah'            => ['Dala Koleram', 'Dogo', 'Droum', 'Gaffati', 'Gouna', 'Hamdara', 'Mirriah', 'Zermou'],
    'Tanout'             => ['Falenco', 'Gangara', 'Ollelewa', 'Tanout', 'Tenhya'],
    'Tesker'             => ['Tesker'],
    'Ville De Zinder'    => ['Zinder I', 'Zinder II', 'Zinder III', 'Zinder IV', 'Zinder V'],
];

// ─── Centres approximatifs des régions (longitude, latitude) ─────────────────
$region_centers = [
    'Agadez'    => ['lon' =>  8.0,  'lat' => 20.0],
    'Diffa'     => ['lon' => 13.3,  'lat' => 13.3],
    'Dosso'     => ['lon' =>  3.2,  'lat' => 13.0],
    'Maradi'    => ['lon' =>  7.1,  'lat' => 13.5],
    'Niamey'    => ['lon' =>  2.1,  'lat' => 13.5],
    'Tahoua'    => ['lon' =>  5.3,  'lat' => 14.9],
    'Tillaberi' => ['lon' =>  2.5,  'lat' => 14.2],
    'Zinder'    => ['lon' =>  8.9,  'lat' => 13.8],
];

// ─── Codes officiels INS des régions ─────────────────────────────────────────
// Format INS : code 1 chiffre (source RENALOC : 1=Agadez, 2=Diffa, …, 8=Niamey)
$region_codes = [
    '1' => 'Agadez',
    '2' => 'Diffa',
    '3' => 'Dosso',
    '4' => 'Maradi',
    '5' => 'Tahoua',
    '6' => 'Tillaberi',
    '7' => 'Zinder',
    '8' => 'Niamey',
];
// Inverse : nom → code
$region_codes_inv = array_flip($region_codes);

// ─── Codes officiels INS des départements (3 chiffres, source RENALOC) ───────
// Premier chiffre = code région
$departement_codes = [
    // Agadez (1xx)
    '101' => 'Aderbissinat',     '102' => 'Agadez',         '103' => 'Arlit',
    '104' => 'Bilma',            '105' => 'Iferouane',      '106' => 'Ingall',
    '107' => 'Tassara',          '108' => 'Tchintabaraden', '109' => 'Tchirozerine',
    // Diffa (2xx)
    '201' => 'Bosso',            '202' => 'Diffa',          '203' => 'Goudoumaria',
    '204' => "Maïné Soroa",      '205' => "N'Gourti",       '206' => "N'Guigmi",
    // Dosso (3xx)
    '301' => 'Boboye',           '302' => 'Dioundiou',      '303' => 'Dogondoutchi',
    '304' => 'Dosso',            '305' => 'Falmey',         '306' => 'Gaya',
    '307' => 'Loga',             '308' => 'Tibiri',
    // Maradi (4xx)
    '401' => 'Aguié',            '402' => 'Dakoro',         '403' => 'Gazaoua',
    '404' => 'Guidan Roumdji',   '405' => 'Madarounfa',     '406' => 'Mayahi',
    '407' => 'Tessaoua',         '408' => 'Maradi',
    // Tahoua (5xx)
    '501' => 'Abalak',           '502' => 'Bagaroua',       '503' => "Birni N'Konni",
    '504' => 'Bouza',            '505' => 'Illéla',         '506' => 'Keita',
    '507' => 'Madaoua',          '508' => 'Malbaza',        '509' => 'Tahoua',
    '510' => 'Takanamatt',       '511' => 'Tillia',         '512' => 'Ville de Tahoua',
    // Tillabéri (6xx)
    '601' => 'Abala',            '602' => 'Ayorou',         '603' => 'Balleyara',
    '604' => 'Banibangou',       '605' => 'Bankilaré',      '606' => 'Filingué',
    '607' => 'Gothèye',          '608' => 'Kollo',          '609' => 'Ouallam',
    '610' => 'Say',              '611' => 'Téra',           '612' => 'Tillabéri',
    '613' => 'Torodi',
    // Zinder (7xx)
    '701' => 'Belbédji',         '702' => 'Bermo',          '703' => 'Damagaram Takaya',
    '704' => 'Dungass',          '705' => 'Gouré',          '706' => 'Kantché',
    '707' => 'Magaria',          '708' => 'Mirriah',        '709' => 'Tanout',
    '710' => 'Tesker',           '711' => 'Zinder',
    // Niamey (8xx)
    '801' => 'Niamey',
];
$departement_codes_inv = array_flip($departement_codes);

// ─── Noms normalisés / alias de communes (unifification) ─────────────────────
// Permet de retrouver la commune officielle à partir de variantes orthographiques
$commune_aliases = [
    // Konni
    "Birni N'Konni"          => "Birni N'Konni",
    'Konni'                  => "Birni N'Konni",
    "Birni'N Konni"          => "Birni N'Konni",
    "Birnin Konni"           => "Birni N'Konni",
    // Niamey arrondissements
    'Niamey 1'               => 'Niamey I',
    'Niamey 2'               => 'Niamey II',
    'Niamey 3'               => 'Niamey III',
    'Niamey 4'               => 'Niamey IV',
    'Niamey 5'               => 'Niamey V',
    // Maradi arrondissements
    'Maradi Commune 1'       => 'Maradi 1',
    'Maradi Commune 2'       => 'Maradi 2',
    'Maradi Commune 3'       => 'Maradi 3',
    // Zinder arrondissements
    'Zinder Commune 1'       => 'Zinder I',
    'Zinder Commune 2'       => 'Zinder II',
    'Zinder Commune 3'       => 'Zinder III',
    'Zinder Commune 4'       => 'Zinder IV',
    'Zinder Commune 5'       => 'Zinder V',
    // Variantes orthographiques diverses
    'Adebissanat'            => 'Aderbisanat',
    'Aderbissanat'           => 'Aderbisanat',
    'Tchadoua'               => 'Tchadoua',
    'Birni N Gaoure'         => "Birni N'Gaoure",
    "Birni N'Gaoure"         => "Birni N'Gaoure",
    'Filingue'               => 'Filingue',
    'N\'Dounga'              => "N'Dounga",
    'Ndounga'                => "N'Dounga",
    'N\'Gourti'              => "N'Gourti",
    'N\'Guigmi'              => "N'Guigmi",
    'N\'Guelbeyli'           => "N'Guelbeyli",
    'N\'Gonga'               => "N'Gonga",
    'Illela'                 => 'Illela',
    'Tillabery'              => 'Tillaberi',
    'Tahaoua'                => 'Tahoua Commune 1',
    'Tahoua'                 => 'Tahoua Commune 1',
    'Dosso Ville'            => 'Dosso Commune',
    'Diffa Ville'            => 'Diffa Commune',
    'Agadez Ville'           => 'Agadez Commune',
];

/**
 * Normalise un nom de commune en retrouvant l'orthographe officielle.
 *
 * @param string $input
 * @return string
 */
function normaliserCommune(string $input): string {
    global $commune_aliases;
    $trimmed = trim($input);
    return $commune_aliases[$trimmed] ?? $trimmed;
}

/**
 * Retourne la liste plate de toutes les communes du Niger.
 *
 * @return array
 */
function toutesLesCommunes(): array {
    global $communes_par_departement;
    $all = [];
    foreach ($communes_par_departement as $communes) {
        foreach ($communes as $c) {
            $all[] = $c;
        }
    }
    sort($all);
    return $all;
}

/**
 * Retourne le département d'une commune (ou null si introuvable).
 *
 * @param string $commune
 * @return string|null
 */
function departementDeCommune(string $commune): ?string {
    global $communes_par_departement;
    $commune = normaliserCommune($commune);
    foreach ($communes_par_departement as $dep => $communes) {
        if (in_array($commune, $communes)) return $dep;
    }
    return null;
}

/**
 * Retourne la région d'une commune.
 *
 * @param string $commune
 * @return string|null
 */
function regionDeCommune(string $commune): ?string {
    global $departements_par_region;
    $dep = departementDeCommune($commune);
    if (!$dep) return null;
    foreach ($departements_par_region as $region => $deps) {
        if (in_array($dep, $deps)) return $region;
    }
    return null;
}
