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
        'Niamey Ville',
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
        'Tahoua Departement',
        'Takeita',
        'Tillia',
        'Ville De Tahoua',
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
    'Niamey Ville'       => ['Niamey I', 'Niamey II', 'Niamey III', 'Niamey IV', 'Niamey V'],

    // TAHOUA
    'Abalak'             => ['Abalak', 'Akoubounou', 'Azeye', 'Tabalak', 'Tamaya'],
    'Bagaroua'           => ['Bagaroua'],
    "Birni N'Konni"      => ['Allela', 'Bazaga', "Birni N'Konni", 'Tsernaoua'],
    'Bouza'              => ['Allakeye', 'Baban Katami', 'Bouza', 'Deoule', 'Karofane', 'Tabotaki', 'Tama'],
    'Illéla'             => ['Badaguichiri', 'Illela', 'Tajae'],
    'Keita'              => ['Garhanga', 'Ibohamane', 'Keita', 'Tamaske'],
    'Madaoua'            => ['Azarori', 'Bangui', 'Galma Koudawatche', 'Madaoua', 'Ourno', 'Sabon Guida'],
    'Malbaza'            => ['Dogueraoua', 'Malbaza'],
    'Tahoua Departement' => ['Afala', 'Bambeye', 'Barmou', 'Kalfou', 'Takanamatt', 'Tebaram'],
    'Takeita'            => ['Dakoussa', 'Garagoumsa', 'Tirmini'],
    'Tillia'             => ['Tillia'],
    'Ville De Tahoua'    => ['Tahoua Commune 1', 'Tahoua Commune 2'],

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
// Format INS : code 1 chiffre (ex. 1=Agadez, 2=Diffa, …, 8=Zinder)
$region_codes = [
    '1' => 'Agadez',
    '2' => 'Diffa',
    '3' => 'Dosso',
    '4' => 'Maradi',
    '5' => 'Niamey',
    '6' => 'Tahoua',
    '7' => 'Tillaberi',
    '8' => 'Zinder',
];
// Inverse : nom → code
$region_codes_inv = array_flip($region_codes);

// ─── Codes officiels INS des départements (2 chiffres) ───────────────────────
// Premier chiffre = code région, deuxième = numéro dans la région
$departement_codes = [
    // Agadez (1x)
    '11' => 'Aderbisanat',       '12' => 'Agadez Ville',    '13' => 'Arlit',
    '14' => 'Bilma',             '15' => 'Iferouane',       '16' => 'Ingall',
    '17' => 'Tassara',           '18' => 'Tchintabaraden',  '19' => 'Tchirozerine',
    // Diffa (2x)
    '21' => 'Bosso',             '22' => 'Diffa',           '23' => 'Goudoumaria',
    '24' => 'Maine Soroa',       '25' => "N'Gourti",        '26' => "N'Guigmi",
    // Dosso (3x)
    '31' => 'Boboye',            '32' => 'Dioundiou',       '33' => 'Dogondoutchi',
    '34' => 'Dosso',             '35' => 'Falmey',          '36' => 'Gaya',
    '37' => 'Loga',              '38' => 'Tibiri',
    // Maradi (4x)
    '41' => 'Aguie',             '42' => 'Dakoro',          '43' => 'Gazaoua',
    '44' => 'Guidan-Roumdji',    '45' => 'Madarounfa',      '46' => 'Mayahi',
    '47' => 'Tessaoua',          '48' => 'Ville De Maradi',
    // Niamey (5x)
    '51' => 'Niamey Ville',
    // Tahoua (6x)
    '61' => 'Abalak',            '62' => 'Bagaroua',        '63' => "Birni N'Konni",
    '64' => 'Bouza',             '65' => 'Illéla',          '66' => 'Keita',
    '67' => 'Madaoua',           '68' => 'Malbaza',         '69' => 'Tahoua Departement',
    '610'=> 'Takeita',           '611'=> 'Tillia',          '612'=> 'Ville De Tahoua',
    // Tillaberi (7x)
    '71' => 'Abala',             '72' => 'Ayorou',          '73' => 'Balleyara',
    '74' => 'Banibangou',        '75' => 'Bankilare',       '76' => 'Filingue',
    '77' => 'Gotheye',           '78' => 'Kollo',           '79' => 'Ouallam',
    '710'=> 'Say',               '711'=> 'Tera',            '712'=> 'Tillaberi',
    '713'=> 'Torodi',
    // Zinder (8x)
    '81' => 'Belbedji',          '82' => 'Bermo',           '83' => 'Damagaram Takaya',
    '84' => 'Dungass',           '85' => 'Goure',           '86' => 'Kantche',
    '87' => 'Magaria',           '88' => 'Mirriah',         '89' => 'Tanout',
    '810'=> 'Tesker',            '811'=> 'Ville De Zinder',
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
