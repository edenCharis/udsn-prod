# 🎓 Personnalisation du Nom de l'Université - Guide

## 📝 Vue d'ensemble

En plus des logos, vous pouvez maintenant personnaliser le **nom de l'université** qui s'affiche sur toutes les pages de connexion :

- Page login.php
- Page index.php
- Page connexion.php
- Toutes les pages avec affichage du nom

---

## 🚀 Utilisation

### Pour les administrateurs

#### 1. Accéder à la page d'administration
```
/administrateur/parametre_logos.php
```

#### 2. Modifier le nom de l'université
- Cliquez sur le bouton **"Nom"** (bouton info) pour chaque université
- Entrez le nouveau nom
- Cliquez sur "Enregistrer"
- Le changement s'applique immédiatement

#### 3. Vérifier les changements
- La page de connexion affichera le nouveau nom
- Les sessions futures utiliseront le nouveau nom

---

## 💻 Pour les développeurs

### Pages sans authentification

```php
<?php
include_once 'config/logo_config.php';
$logoConfig = getLogoConfig();
$universite_nom = $logoConfig->getDefaultUniversityName();
?>

<h1><?php echo htmlspecialchars($universite_nom); ?></h1>
```

### Pages authentifiées

```php
<?php
// Utiliser la session (set lors de la connexion)
$nom_univ = $_SESSION['nom_univ'] ?? 'UNIVERSITE DENIS SASSOU-N\'GUESSO';
?>

<h1><?php echo htmlspecialchars($nom_univ); ?></h1>
```

### Utiliser l'API

```javascript
// Obtenir le nom par défaut
fetch('/php/api_logos.php?action=get_default_university_name')
    .then(r => r.json())
    .then(d => console.log(d.data.name));

// Obtenir le nom pour une université spécifique
fetch('/php/api_logos.php?action=get_university_name&univ_code=1')
    .then(r => r.json())
    .then(d => console.log(d.data.name));

// Obtenir le nom depuis la session
fetch('/php/api_logos.php?action=get_university_name_from_session')
    .then(r => r.json())
    .then(d => console.log(d.data.name));
```

---

## 🛠️ Méthodes de la classe LogoConfig

### getDefaultUniversityName()
Retourne le nom par défaut : "UNIVERSITE DENIS SASSOU-N'GUESSO"

```php
$name = $logoConfig->getDefaultUniversityName();
// Retourne: "UNIVERSITE DENIS SASSOU-N'GUESSO"
```

### getUniversityNameForUser($user_id)
Retourne le nom de l'université pour un utilisateur spécifique

```php
$name = $logoConfig->getUniversityNameForUser(5);
// Retourne le nom de l'université de l'utilisateur 5
```

### getUniversityNameFromSession()
Retourne le nom depuis la session courante

```php
$name = $logoConfig->getUniversityNameFromSession();
// Retourne $_SESSION['nom_univ'] ou le nom par défaut
```

### getUniversityName($univ_code)
Retourne le nom pour un code d'université spécifique

```php
$name = $logoConfig->getUniversityName(1);
// Retourne le nom de l'université avec le code 1
```

### updateUniversityName($univ_code, $nom_univ)
Met à jour le nom d'une université

```php
$success = $logoConfig->updateUniversityName(1, "Ma Nouvelle Université");
// true si succès, false sinon
```

---

## 📡 Endpoints API

### GET /php/api_logos.php?action=get_default_university_name
**Réponse:**
```json
{
  "success": true,
  "message": "Nom de l'université par défaut",
  "data": {
    "name": "UNIVERSITE DENIS SASSOU-N'GUESSO"
  }
}
```

### GET /php/api_logos.php?action=get_university_name&univ_code=1
**Réponse:**
```json
{
  "success": true,
  "message": "Nom de l'université",
  "data": {
    "name": "Mon Université",
    "univ_code": 1
  }
}
```

### GET /php/api_logos.php?action=get_university_name_from_session
**Réponse:**
```json
{
  "success": true,
  "message": "Nom de l'université",
  "data": {
    "name": "Mon Université"
  }
}
```

### POST /php/api_logos.php?action=update_university_name
**Paramètres:**
- `univ_code`: Code de l'université (integer)
- `nom_univ`: Nouveau nom (string, max 255 caractères)

**Réponse:**
```json
{
  "success": true,
  "message": "Nom mis à jour avec succès",
  "data": {
    "univ_code": 1,
    "name": "Nouveau Nom"
  }
}
```

---

## 📊 Base de données

La colonne `nom` dans la table `univ` stocke le nom de l'université :

```sql
SELECT code, nom, logo FROM univ;
```

Structure recommandée :
```sql
CREATE TABLE `univ` (
    `code` INT PRIMARY KEY AUTO_INCREMENT,
    `nom` VARCHAR(255) NOT NULL,
    `logo` VARCHAR(255) DEFAULT NULL,
    -- autres colonnes...
);
```

---

## 🔄 Mise à jour de la session lors de la connexion

Le fichier `php/routeur.php` doit inclure la ligne suivante pour que le nom soit disponible en session :

```php
$_SESSION['nom_univ'] = getUniversityName($univ_code, $connexion);
// Ou en utilisant LogoConfig:
$_SESSION['nom_univ'] = $logoConfig->getUniversityName($univ_code);
```

---

## ⚙️ Intégration dans les pages de connexion

### login.php
```php
<?php
$universite_nom = $logoConfig->getDefaultUniversityName();
?>
<h1><?php echo htmlspecialchars($universite_nom); ?></h1>
```

### index.php
```php
<?php
$universite_nom = $logoConfig->getDefaultUniversityName();
?>
<h1><?php echo htmlspecialchars($universite_nom); ?></h1>
```

### connexion.php
```php
<?php
$universite_nom = $logoConfig->getDefaultUniversityName();
?>
<h4><?php echo htmlspecialchars($universite_nom); ?></h4>
```

---

## 🔒 Sécurité

✅ **Tous les noms sont sécurisés :**
- Échappement XSS avec `htmlspecialchars()`
- Validation de la longueur (max 255 caractères)
- Vérification des permissions (admin only) pour les mises à jour
- Requêtes préparées en BD
- Pas d'injection SQL

---

## 💡 Cas d'usage

### Multi-universités
Si votre système gère plusieurs universités, chacune peut avoir son propre nom personnalisé.

### Branding personnalisé
Adaptez le nom pour différents contextes ou langues.

### Événements temporaires
Modifiez le nom pour afficher des messages spéciaux.

---

## 🔄 Flux complet

```
Administrateur modifie le nom
↓
Page: /administrateur/parametre_logos.php
↓
Soumission du formulaire avec nouveau nom
↓
Vérification des permissions (admin)
↓
Mise à jour en base de données
↓
Confirmation de succès
↓
Utilisateur se connecte
↓
php/routeur.php charge le nom dans $_SESSION['nom_univ']
↓
Pages affichent le nouveau nom
```

---

## 📋 Exemple complet

### Page de connexion personnalisée

```php
<?php
session_start();
include_once 'config/logo_config.php';
$logoConfig = getLogoConfig();

// Récupérer les informations d'affichage
$logo = $logoConfig->getDefaultLogo();
$favicon = $logoConfig->getDefaultFavicon();
$universite_nom = $logoConfig->getDefaultUniversityName();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($universite_nom); ?></title>
    <link rel="icon" href="<?php echo htmlspecialchars($favicon); ?>">
</head>
<body>
    <div class="login-container">
        <img src="<?php echo htmlspecialchars($logo); ?>" alt="Logo">
        <h1><?php echo htmlspecialchars($universite_nom); ?></h1>
        <h2>Connexion</h2>
        <form method="POST" action="php/routeur.php">
            <input type="text" name="username" placeholder="Login" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Connexion</button>
        </form>
    </div>
</body>
</html>
```

---

## ✅ Checklist

- [ ] Le nom s'affiche correctement sur login.php
- [ ] Le nom s'affiche correctement sur connexion.php
- [ ] Le nom s'affiche correctement sur index.php
- [ ] Vous pouvez modifier le nom via l'admin panel
- [ ] Les changements s'appliquent immédiatement
- [ ] L'API retourne le bon nom
- [ ] La sécurité XSS est respectée (htmlspecialchars)

---

## 🎯 Prochaines étapes

1. Accédez à `/administrateur/parametre_logos.php`
2. Cliquez sur le bouton "Nom" pour modifier le nom de votre université
3. Vérifiez que les changements s'affichent sur les pages de connexion
4. Utilisez l'API pour récupérer le nom dynamiquement si besoin

---

**Date:** 13 Juin 2026  
**Version:** 1.1 - Avec gestion des noms  
**Statut:** ✅ Complet
