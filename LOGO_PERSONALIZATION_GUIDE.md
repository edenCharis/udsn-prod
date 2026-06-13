# Guide de Personnalisation des Logos - UDSN

## Vue d'ensemble
Les logos de l'application UDSN sont maintenant entièrement personnalisables. Un système centralisé a été mis en place pour gérer les logos sur :
- La page de connexion (`login.php`)
- La page d'index (`index.php`)
- La page de connexion alternative (`connexion.php`)
- Toutes les pages authentifiées via `$_SESSION['logo_univ']`

## Architecture

### Fichiers principaux

1. **`config/logo_config.php`** - Classe de gestion des logos
   - Classe `LogoConfig` avec méthodes statiques
   - Permet de récupérer/modifier les logos
   - Utilise un système de cache pour les performances

2. **`php/lib.php`** - Fonctions helper
   - `getDefaultLogo()` - Retourne le chemin du logo par défaut
   - `getFavicon($use_session)` - Retourne le favicon
   - `getLogoFromSession()` - Retourne le logo depuis la session
   - `getlogo($id, $connexion)` - Récupère le logo pour un utilisateur

3. **`administrateur/parametre_logos.php`** - Interface d'administration
   - Permet de télécharger/modifier les logos des universités
   - Gère le stockage dans le dossier `administrateur/logo/`

## Utilisation

### Pages de connexion (avant authentification)

```php
<?php
session_start();
include_once 'config/logo_config.php';
$logoConfig = getLogoConfig();
$logo = $logoConfig->getDefaultLogo();
$favicon = $logoConfig->getDefaultFavicon();
?>

<!-- Dans le favicon -->
<link rel="shortcut icon" href="<?php echo htmlspecialchars($favicon); ?>">

<!-- Dans le HTML -->
<img src="<?php echo htmlspecialchars($logo); ?>" alt="Logo">
```

### Pages authentifiées

```php
<?php
include_once '../config/logo_config.php';
$logoConfig = getLogoConfig();
$logo = $logoConfig->getLogoFromSession();
// ou simplement utiliser $_SESSION['logo_univ'] qui est défini lors de la connexion
?>

<!-- Dans le favicon -->
<link rel="icon" type="image/png" sizes="16x16" href="../administrateur/<?php echo htmlspecialchars($_SESSION['logo_univ']); ?>">

<!-- Dans le HTML -->
<img src="../administrateur/<?php echo htmlspecialchars($_SESSION['logo_univ']); ?>" alt="Logo">
```

## Base de données

### Structure de la table `univ`

La table `univ` doit contenir les colonnes suivantes :

```sql
CREATE TABLE IF NOT EXISTS `univ` (
    `code` INT PRIMARY KEY AUTO_INCREMENT,
    `nom` VARCHAR(255) NOT NULL,
    `logo` VARCHAR(255) DEFAULT NULL,
    -- autres colonnes...
);
```

Le champ `logo` stocke le chemin relatif du logo (ex: `logo/logo_1_1623456789.png`).

## Processus d'upload

1. **Interface d'administration** (`administrateur/parametre_logos.php`)
   - Les administrateurs accèdent à la page de gestion des logos
   - Sélectionnent une université et un fichier image
   - Le système valide le fichier (type, taille)

2. **Stockage**
   - Les fichiers sont sauvegardés dans `administrateur/logo/`
   - Nommage: `logo_<univ_code>_<timestamp>.<extension>`

3. **Base de données**
   - Le chemin est enregistré dans la table `univ`
   - La session est mise à jour lors de la prochaine connexion

## Migration depuis l'ancien système

### Pour les pages existantes

Remplacez:
```php
<img src="images/univ.png" alt="Logo">
```

Par:
```php
<?php include_once 'config/logo_config.php'; $logoConfig = getLogoConfig(); ?>
<img src="<?php echo htmlspecialchars($logoConfig->getDefaultLogo()); ?>" alt="Logo">
```

Ou utilisez les fonctions helper depuis `php/lib.php`:
```php
<img src="<?php echo getDefaultLogo(); ?>" alt="Logo">
```

## Sécurité

1. **Validation des fichiers**
   - Types autorisés: PNG, JPEG, GIF, WebP
   - Vérification du MIME type réel (pas juste l'extension)
   - Taille maximale: 5MB

2. **Protection d'accès**
   - La page d'administration `parametre_logos.php` vérifie que l'utilisateur est administrateur
   - Les uploads utilisent `move_uploaded_file()` de manière sécurisée
   - Les chemins sont validés avec `htmlspecialchars()` dans les templates

3. **Injection XSS**
   - Tous les chemins de fichiers sont échappés avec `htmlspecialchars()`

## Fichiers modifiés

- `login.php` - Utilise LogoConfig
- `index.php` - Utilise LogoConfig
- `connexion.php` - Utilise LogoConfig
- `php/lib.php` - Ajout des fonctions helper
- `config/logo_config.php` - **NOUVEAU** - Classe centralisée
- `administrateur/parametre_logos.php` - **NOUVEAU** - Interface d'admin

## Dossiers créés

- `config/` - Configuration centralisée
- `administrateur/logo/` - Stockage des logos (créé à la première utilisation)

## Variables de session

Lors de la connexion, la variable `$_SESSION['logo_univ']` est définie avec le chemin du logo.
Elle est utilisée dans toutes les pages authentifiées.

## Exemples d'utilisation

### Ajouter un logo sur une nouvelle page

```php
<?php
session_start();
include_once '../config/logo_config.php';
$logoConfig = getLogoConfig();

if (isset($_SESSION['id_user'])) {
    // Page authentifiée
    $logo = 'administrateur/' . $_SESSION['logo_univ'];
} else {
    // Page non authentifiée
    $logo = $logoConfig->getDefaultLogo();
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="icon" href="<?php echo htmlspecialchars($logo); ?>">
</head>
<body>
    <img src="<?php echo htmlspecialchars($logo); ?>" alt="Logo">
</body>
</html>
```

## Support et maintenance

Pour ajouter un nouveau logo d'université :
1. Accédez à `administrateur/parametre_logos.php`
2. Recherchez l'université concernée
3. Cliquez sur "Télécharger un logo"
4. Sélectionnez l'image et validez

Le système met automatiquement à jour la base de données et les sessions futures utiliseront le nouveau logo.
