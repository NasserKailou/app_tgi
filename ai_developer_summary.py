#!/usr/bin/env python3
"""
===========================================================================
TGI-NY — Résumé des exigences pour le développeur IA
Parquet du Tribunal de Grande Instance Hors Classe de Niamey
Version : 2.3  |  Date : 2026-04-25
Budget IA estimé : ≤ 2 000 crédits
===========================================================================

Ce script documente toutes les fonctionnalités à implémenter / vérifier
dans l'application web PHP TGI-NY (branche ak_main).
"""

from dataclasses import dataclass, field
from typing import List, Optional

# ---------------------------------------------------------------------------
# Structures de données
# ---------------------------------------------------------------------------

@dataclass
class Feature:
    """Représente une fonctionnalité à développer."""
    id: str
    titre: str
    priorite: str          # critique | haute | moyenne | basse
    statut: str            # fait | en_cours | todo
    description: str
    fichiers_concernes: List[str] = field(default_factory=list)
    migration_requise: bool = False
    credits_estimes: int = 0


@dataclass
class SecurityMeasure:
    """Mesure de sécurité à implémenter."""
    id: str
    description: str
    fichiers: List[str] = field(default_factory=list)
    implemente: bool = False


# ---------------------------------------------------------------------------
# Fonctionnalités
# ---------------------------------------------------------------------------

FEATURES: List[Feature] = [

    Feature(
        id="F01",
        titre="Infractions à deux niveaux",
        priorite="critique",
        statut="fait",
        description=(
            "Niveau 1 : type d'infraction saisi par l'unité d'enquête (multi-sélection, "
            "table pv_infractions). "
            "Niveau 2 : qualification retenue par le substitut (multi-sélection, "
            "table pv_infractions type='substitut') + champ qualification_details "
            "(complicité, circonstances aggravantes). "
            "Les deux niveaux sont distincts et saisis par des acteurs différents."
        ),
        fichiers_concernes=[
            "app/controllers/PVController.php",
            "app/views/pv/show.php",
            "app/views/pv/create.php",
            "migrations/004_nouvelles_fonctionnalites.sql",
        ],
        migration_requise=True,
        credits_estimes=150,
    ),

    Feature(
        id="F02",
        titre="Sélection d'infractions par mis en cause",
        priorite="critique",
        statut="fait",
        description=(
            "Chaque mise en cause peut avoir ses propres infractions associées "
            "(table mec_infractions). "
            "L'unité d'enquête saisit les infractions initiales (type='unite'). "
            "Le substitut peut qualifier chaque MEC séparément (type='substitut'). "
            "Interface : cases à cocher dans le formulaire MEC, groupées par catégorie."
        ),
        fichiers_concernes=[
            "app/controllers/MiseEnCauseController.php",
            "app/views/mises_en_cause/_form.php",
            "migrations/004_nouvelles_fonctionnalites.sql",
        ],
        migration_requise=True,
        credits_estimes=120,
    ),

    Feature(
        id="F03",
        titre="Champ 'Lois applicables' (remplace description des faits pour le substitut)",
        priorite="haute",
        statut="fait",
        description=(
            "Le champ description_faits reste pour la saisie initiale par le greffe. "
            "Le substitut peut renseigner lois_applicables (références légales : "
            "articles CPP, lois, décrets). "
            "Ce champ est en lecture seule pour le greffier dans la fiche PV. "
            "Colonne ajoutée : pv.lois_applicables TEXT."
        ),
        fichiers_concernes=[
            "app/controllers/PVController.php",
            "app/views/pv/show.php",
            "app/views/pv/create.php",
            "migrations/004_nouvelles_fonctionnalites.sql",
        ],
        migration_requise=True,
        credits_estimes=80,
    ),

    Feature(
        id="F04",
        titre="PVs multiples par suspect — jonction en dossier",
        priorite="critique",
        statut="fait",
        description=(
            "Un suspect peut faire l'objet de plusieurs PVs (même numéro RP). "
            "Lors du transfert, si un dossier existe déjà pour le même RP, "
            "le nouveau PV y est joint (table dossier_pvs) au lieu de créer un nouveau dossier. "
            "La fiche PV affiche tous les PVs liés (même RP). "
            "Fusion visible dans la fiche dossier (onglet PVs liés)."
        ),
        fichiers_concernes=[
            "app/controllers/PVController.php",
            "app/views/pv/show.php",
            "migrations/004_nouvelles_fonctionnalites.sql",
        ],
        migration_requise=True,
        credits_estimes=200,
    ),

    Feature(
        id="F05",
        titre="Pièces jointes par PV (substitut uniquement)",
        priorite="haute",
        statut="fait",
        description=(
            "Le substitut peut ajouter des documents (PDF, images, Word) à chaque PV. "
            "Upload via modal avec barre de progression. "
            "Fichiers stockés dans uploads/documents/pv_{id}/. "
            "Table documents (pv_id déjà existant) réutilisée. "
            "Accès vérifié : seul le substitut affecté ou admin/procureur peut uploader."
        ),
        fichiers_concernes=[
            "app/controllers/PVController.php",
            "app/views/pv/show.php",
            "public/index.php",
        ],
        migration_requise=False,
        credits_estimes=100,
    ),

    Feature(
        id="F06",
        titre="Filtrage par rôle (visibilité des données)",
        priorite="critique",
        statut="fait",
        description=(
            "Admin et Procureur : voient TOUT. "
            "Substitut : voit uniquement les PVs/dossiers qui lui sont affectés "
            "(substitut_id = user.id). "
            "Greffier et Président : voient tout (ils enregistrent pour tout le monde). "
            "Implémenté dans PVController::applyRoleFilter() et DashboardController::getStats(). "
            "À étendre à DossierController, JugementController, etc."
        ),
        fichiers_concernes=[
            "app/controllers/PVController.php",
            "app/controllers/DashboardController.php",
            "app/helpers/Auth.php",
        ],
        migration_requise=False,
        credits_estimes=180,
    ),

    Feature(
        id="F07",
        titre="Graphique PV par mois mis à jour",
        priorite="moyenne",
        statut="fait",
        description=(
            "Graphique sur 12 derniers mois avec colonnes : "
            "total PVs, dont antiterroristes, dont qualifiés par substitut. "
            "Données filtrées par rôle (substitut voit uniquement les siens). "
            "Requête SQL étendue pour inclure les nouvelles colonnes."
        ),
        fichiers_concernes=[
            "app/controllers/DashboardController.php",
            "app/views/dashboard/index.php",
        ],
        migration_requise=False,
        credits_estimes=60,
    ),

    Feature(
        id="F08",
        titre="Génération de rapports depuis le dashboard",
        priorite="moyenne",
        statut="fait",
        description=(
            "Formulaire de génération de rapport (quotidien, hebdomadaire, mensuel, annuel, personnalisé). "
            "Données stockées en JSON dans table rapports. "
            "Vue rapport HTML avec graphique, statistiques, top infractions. "
            "Bouton imprimer pour impression/PDF navigateur. "
            "Liste des 5 derniers rapports sur le dashboard."
        ),
        fichiers_concernes=[
            "app/controllers/DashboardController.php",
            "app/views/dashboard/index.php",
            "app/views/dashboard/rapport.php",
            "migrations/004_nouvelles_fonctionnalites.sql",
        ],
        migration_requise=True,
        credits_estimes=150,
    ),

    Feature(
        id="F09",
        titre="Scellés — option 'Autre' avec précision",
        priorite="basse",
        statut="fait",
        description=(
            "Ajout d'un champ categorie_autre_detail (varchar 200) affiché "
            "uniquement quand la catégorie 'Autre' est sélectionnée. "
            "Contrôleur mis à jour pour sauvegarder ce champ. "
            "Fallback si la colonne n'existe pas encore (migration 004 non appliquée)."
        ),
        fichiers_concernes=[
            "app/controllers/ScelleController.php",
            "app/views/scelles/create.php",
            "migrations/004_nouvelles_fonctionnalites.sql",
        ],
        migration_requise=True,
        credits_estimes=40,
    ),

    Feature(
        id="F10",
        titre="Sécurité renforcée — SQL injection & sessions",
        priorite="critique",
        statut="fait",
        description=(
            "Toutes les requêtes utilisent des requêtes préparées PDO (paramètres liés). "
            "Méthode sec() centralisée pour htmlspecialchars + strip_tags + trim. "
            "Table security_logs pour journaliser les actions sensibles. "
            "Vérification CSRF sur tous les formulaires POST (déjà présente). "
            "Validation du type MIME des fichiers uploadés (finfo). "
            "Limites de taille fichier (20 Mo documents, 2 Mo photos). "
            "À implémenter : rate limiting sur login, HTTPS forcé en production."
        ),
        fichiers_concernes=[
            "app/controllers/PVController.php",
            "app/controllers/MiseEnCauseController.php",
            "migrations/004_nouvelles_fonctionnalites.sql",
        ],
        migration_requise=True,
        credits_estimes=120,
    ),

    Feature(
        id="F11",
        titre="Adaptation du modèle de mandat",
        priorite="moyenne",
        statut="todo",
        description=(
            "Le PDF joint (mandat_depot.pdf) est un mandat de dépôt officiel du TGI-NY. "
            "La vue app/views/mandats/print.php doit être mise à jour pour correspondre "
            "exactement à la mise en page : en-tête avec armoiries Niger, références légales "
            "(art. 127 à 131 CPP), corps avec identité complète du mis en cause, "
            "infractions retenues, magistrat instructeur, date/lieu, signatures. "
            "PDF image seulement — texte à reconstituer manuellement."
        ),
        fichiers_concernes=[
            "app/views/mandats/print.php",
            "app/controllers/MandatController.php",
        ],
        migration_requise=False,
        credits_estimes=200,
    ),

    Feature(
        id="F12",
        titre="Extension du filtrage par rôle aux autres contrôleurs",
        priorite="haute",
        statut="todo",
        description=(
            "DossierController : substitut ne voit que ses dossiers. "
            "JugementController : idem. "
            "AudienceController : greffier/substitut voient selon leur périmètre. "
            "DetenusController : admin/procureur voient tout. "
            "MandatController : substitut voit ses mandats. "
            "Modèle : copier applyRoleFilter() de PVController."
        ),
        fichiers_concernes=[
            "app/controllers/DossierController.php",
            "app/controllers/JugementController.php",
            "app/controllers/AudienceController.php",
            "app/controllers/DetenusController.php",
            "app/controllers/MandatController.php",
        ],
        migration_requise=False,
        credits_estimes=300,
    ),

]

# ---------------------------------------------------------------------------
# Mesures de sécurité
# ---------------------------------------------------------------------------

SECURITY_MEASURES: List[SecurityMeasure] = [
    SecurityMeasure(
        id="S01",
        description="Requêtes préparées PDO pour toutes les interactions DB (aucune concaténation SQL directe)",
        fichiers=["Tous les controllers"],
        implemente=True,
    ),
    SecurityMeasure(
        id="S02",
        description="Validation CSRF sur tous les formulaires POST (jeton _csrf vérifié par CSRF::check())",
        fichiers=["app/helpers/CSRF.php", "Tous les formulaires"],
        implemente=True,
    ),
    SecurityMeasure(
        id="S03",
        description="Validation type MIME fichiers uploadés via finfo (rejet des types non autorisés)",
        fichiers=["app/controllers/PVController.php", "app/controllers/DocumentController.php"],
        implemente=True,
    ),
    SecurityMeasure(
        id="S04",
        description="Journal de sécurité (table security_logs) pour actions sensibles",
        fichiers=["app/controllers/PVController.php", "migrations/004_nouvelles_fonctionnalites.sql"],
        implemente=True,
    ),
    SecurityMeasure(
        id="S05",
        description="Rate limiting sur le formulaire de connexion (à implémenter)",
        fichiers=["app/controllers/AuthController.php"],
        implemente=False,
    ),
    SecurityMeasure(
        id="S06",
        description="HTTPS forcé en production + HSTS header (à configurer au niveau serveur web)",
        fichiers=["public/.htaccess"],
        implemente=False,
    ),
    SecurityMeasure(
        id="S07",
        description="Session sécurisée : regenerate_id après login, timeout configurable",
        fichiers=["app/helpers/Auth.php"],
        implemente=True,
    ),
    SecurityMeasure(
        id="S08",
        description="XSS : htmlspecialchars() sur tout output utilisateur dans les vues PHP",
        fichiers=["Toutes les vues PHP"],
        implemente=True,
    ),
]

# ---------------------------------------------------------------------------
# Résumé et affichage
# ---------------------------------------------------------------------------

def print_summary():
    print("=" * 70)
    print("  TGI-NY — RÉSUMÉ FONCTIONNALITÉS v2.3")
    print("  Branche : ak_main | Date : 2026-04-25")
    print("=" * 70)

    statuts = {"fait": [], "en_cours": [], "todo": []}
    total_credits = 0

    for f in FEATURES:
        statuts[f.statut].append(f)
        total_credits += f.credits_estimes

    print(f"\n📊 BILAN : {len(FEATURES)} fonctionnalités")
    print(f"  ✅ Fait       : {len(statuts['fait'])}")
    print(f"  🔄 En cours   : {len(statuts['en_cours'])}")
    print(f"  📋 À faire    : {len(statuts['todo'])}")
    print(f"  💳 Crédits IA estimés : {total_credits} / 2000")

    print("\n" + "─" * 70)
    print("FONCTIONNALITÉS IMPLÉMENTÉES :")
    print("─" * 70)
    for f in statuts["fait"]:
        print(f"\n[{f.id}] {f.titre}")
        print(f"  Priorité : {f.priorite.upper()}")
        print(f"  {f.description[:120]}...")
        print(f"  Fichiers : {', '.join(f.fichiers_concernes[:2])}")
        if f.migration_requise:
            print(f"  ⚠️  Migration DB requise (migrations/004_nouvelles_fonctionnalites.sql)")

    print("\n" + "─" * 70)
    print("FONCTIONNALITÉS À IMPLÉMENTER :")
    print("─" * 70)
    for f in statuts["todo"]:
        print(f"\n[{f.id}] {f.titre}")
        print(f"  Priorité : {f.priorite.upper()}")
        print(f"  {f.description[:150]}...")
        print(f"  Crédits estimés : {f.credits_estimes}")

    print("\n" + "─" * 70)
    print("SÉCURITÉ :")
    print("─" * 70)
    for s in SECURITY_MEASURES:
        status = "✅" if s.implemente else "❌"
        print(f"  {status} [{s.id}] {s.description}")

    print("\n" + "=" * 70)
    print("GUIDE D'APPLICATION DE LA MIGRATION :")
    print("=" * 70)
    print("""
  1. Se connecter à MySQL :
     mysql -u root tribunal_tgi_ny_maj

  2. Appliquer la migration complète :
     SOURCE /chemin/vers/migrations/global.sql;

     OU uniquement les nouvelles fonctionnalités :
     SOURCE /chemin/vers/migrations/004_nouvelles_fonctionnalites.sql;

  3. Vérifier l'application de la migration :
     SHOW TABLES LIKE 'pv_infractions';
     SHOW TABLES LIKE 'mec_infractions';
     SHOW TABLES LIKE 'dossier_pvs';
     SHOW TABLES LIKE 'rapports';
     SHOW TABLES LIKE 'security_logs';
     DESCRIBE pv;  -- vérifier colonnes qualification_substitut_id, lois_applicables
""")

    print("NOUVELLES ROUTES HTTP :")
    print("─" * 70)
    routes = [
        ("POST", "/pv/upload/{pvId}",            "Upload pièce jointe PV (substitut)"),
        ("GET",  "/api/pv/documents/{pvId}",     "Liste documents PV (API JSON)"),
        ("POST", "/dashboard/rapport",           "Générer un rapport"),
        ("GET",  "/dashboard/rapport/{id}",      "Voir un rapport généré"),
    ]
    for method, route, desc in routes:
        print(f"  {method:4s} {route:35s} → {desc}")

    print("\n" + "=" * 70)
    print("FIN DU RÉSUMÉ")
    print("=" * 70)


if __name__ == "__main__":
    print_summary()
