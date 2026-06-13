<?php
/**
 * README - Page d'Administration des Logos
 * 
 * Cette page d'administration se trouve à :
 * /administrateur/parametre_logos.php
 * 
 * Accès : http://votre-domaine/administrateur/parametre_logos.php
 * 
 * Permissions requises : Administrateur (vérifié par $_SESSION['role'])
 */

echo "<!-- 
╔════════════════════════════════════════════════════════════════════╗
║                 GESTION DES LOGOS - INSTRUCTIONS                   ║
╚════════════════════════════════════════════════════════════════════╝

📍 LOCALISATION
===============
Fichier: /administrateur/parametre_logos.php
Accès:   http://votre-domaine/administrateur/parametre_logos.php

🔐 SÉCURITÉ
===========
- Seuls les administrateurs peuvent accéder à cette page
- Vérification du rôle: $_SESSION['role'] === 'administrateur'
- Vérification de la session active

📋 FONCTIONNALITÉS
==================

1. VISUALISER LES LOGOS ACTUELS
   - Affiche toutes les universités
   - Montre le logo actuel (s'il existe)
   - Affiche le chemin du fichier
   - Aperçu visuel du logo

2. TÉLÉCHARGER UN NOUVEAU LOGO
   - Cliquez sur le bouton 'Télécharger un logo'
   - Sélectionnez une image depuis votre ordinateur
   - Validez l'upload
   - Le logo est automatiquement enregistré

3. TYPES DE FICHIERS ACCEPTÉS
   - PNG (recommandé)
   - JPEG/JPG
   - GIF
   - WebP
   - Taille maximale: 5MB

📁 STOCKAGE DES FICHIERS
========================
Emplacement: /administrateur/logo/
Nommage: logo_<code_universite>_<timestamp>.<extension>

Exemple: logo_1_1623456789.png

🗄️ BASE DE DONNÉES
==================
Table: univ
Colonne: logo
Type: VARCHAR(255)

La colonne 'logo' contient le chemin relatif du fichier:
Exemple: logo/logo_1_1623456789.png

✨ PROCESSUS COMPLET
====================
1. Administrateur upload une image
2. Système valide le fichier
3. Fichier sauvegardé dans /administrateur/logo/
4. Chemin enregistré en base de données
5. Cache vidé
6. Sessions futures utiliseront le nouveau logo

🔍 VALIDATION
=============
Côté client (JavaScript):
- Vérification de l'extension
- Vérification de la taille
- Message d'erreur immédiat

Côté serveur (PHP):
- Vérification du MIME type réel
- Vérification de la taille
- Sécurisation du stockage

⚙️ CONFIGURATION
================
Fichiers impliqués:
- config/logo_config.php        (Classe LogoConfig)
- administrateur/parametre_logos.php  (Interface)
- php/lib.php                   (Fonctions helper)
- php/routeur.php               (Set $_SESSION['logo_univ'])

🎯 CAS D'UTILISATION
====================
1. Nouvelle université
   → Ajoutez-la en base
   → Utilisez cette page pour ajouter son logo

2. Changement de logo
   → Uploadez le nouveau logo
   → L'ancien est conservé dans l'historique
   → Les nouvelles sessions utiliseront le nouveau

3. Réinitialisation
   → Uploadez un logo par défaut
   → Ou supprimez la valeur en base de données

📞 SUPPORT
==========
Pour plus d'informations:
- Consultez LOGO_PERSONALIZATION_GUIDE.md
- Consultez QUICK_START_LOGOS.md
- Contactez l'équipe technique

-->
";
?>
