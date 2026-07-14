<?php
// reset_passwords_substituts.php
// À placer dans C:\xamppSites\htdocs\app_tgi\app, exécuter une fois, puis SUPPRIMER.

require 'C:\xamppSites\htdocs\app_tgi\app\config\database.php';

$db = Database::getInstance()->getPDO();

// Récupère tous les Substituts du Procureur (role_id = 5)
$substituts = $db->query(
    "SELECT id, nom, prenom, email FROM users WHERE role_id = 5 ORDER BY id"
)->fetchAll(PDO::FETCH_ASSOC);

$update = $db->prepare("UPDATE users SET password = ? WHERE id = ?");

echo "=== Réinitialisation des mots de passe des Substituts ===\n\n";

foreach ($substituts as $s) {
    // Partie avant le @ dans l'email
    $local = strstr($s['email'], '@', true);   // ex: "zara.massi"
    $motDePasse = $local . '@tgi2026';          // ex: "zara.massi@tgi2026"

    $hash = password_hash($motDePasse, PASSWORD_BCRYPT, ['cost' => 12]);
    $update->execute([$hash, $s['id']]);

    printf(
        "ID %-3d | %-20s %-14s | %-40s | %s\n",
        $s['id'], $s['nom'], $s['prenom'], $s['email'], $motDePasse
    );
}

echo "\n=== Terminé : " . count($substituts) . " compte(s) réinitialisé(s). ===\n";
