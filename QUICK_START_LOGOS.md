# 🎨 Système de Personnalisation des Logos - Guide Rapide

## ✅ Qu'est-ce qui a été fait

J'ai rendu **tous les logos de l'application personnalisables**. Voici ce qui a été mis en place :

### 📁 Fichiers créés/modifiés

| Fichier | Statut | Description |
|---------|--------|-------------|
| `config/logo_config.php` | ✅ NOUVEAU | Classe centralisée de gestion des logos |
| `administrateur/parametre_logos.php` | ✅ NOUVEAU | Interface d'administration pour uploader les logos |
| `login.php` | ✅ MODIFIÉ | Utilise le système de logos dynamiques |
| `index.php` | ✅ MODIFIÉ | Utilise le système de logos dynamiques |
| `connexion.php` | ✅ MODIFIÉ | Utilise le système de logos dynamiques |
| `php/lib.php` | ✅ MODIFIÉ | Ajout des fonctions helper |

---

## 🚀 Utilisation

### Pour les administrateurs : Modifier les logos

1. **Accédez à la page d'administration :**
   ```
   http://votre-domaine/administrateur/parametre_logos.php
   ```

2. **Pour chaque université :**
   - Cliquez sur "Télécharger un logo"
   - Sélectionnez une image (PNG, JPEG, GIF, WebP)
   - Le fichier est automatiquement enregistré et mis en base de données

3. **C'est fait !** 🎉
   - Le logo apparaîtra immédiatement sur les pages de connexion
   - Les utilisateurs authentifiés verront le logo lors de leur prochaine connexion

---

## 📱 Où apparaissent les logos

✅ **Pages de connexion (avant authentification) :**
- `login.php` - Formulaire de connexion
- `connexion.php` - Formulaire alternative
- `index.php` - Accueil

✅ **Pages authentifiées :**
- Toutes les pages du dashboard (scolarité, administrateur, etc.)
- Les logos sont chargés depuis `$_SESSION['logo_univ']`

✅ **Favicon :**
- Personnalisé sur toutes les pages

---

## 🔧 Pour les développeurs : Ajouter un logo sur une nouvelle page

### Option 1 : Utiliser la classe LogoConfig (recommandée)

```php
<?php
include_once 'config/logo_config.php';
$logoConfig = getLogoConfig();
$logo = $logoConfig->getDefaultLogo();
?>
<link rel="icon" href="<?php echo htmlspecialchars($logo); ?>">
<img src="<?php echo htmlspecialchars($logo); ?>" alt="Logo">
```

### Option 2 : Utiliser les fonctions helper

```php
<?php
include_once 'php/lib.php';
$logo = getDefaultLogo();
?>
<img src="<?php echo htmlspecialchars($logo); ?>" alt="Logo">
```

### Option 3 : Pour les pages authentifiées

```php
<?php
// Le logo est automatiquement disponible dans la session
$logo = 'administrateur/' . $_SESSION['logo_univ'];
?>
<img src="<?php echo htmlspecialchars($logo); ?>" alt="Logo">
```

---

## 📊 Structure de stockage

```
administrateur/
├── logo/
│   ├── logo_1_1623456789.png
│   ├── logo_2_1623456790.jpg
│   └── ...
├── parametre_logos.php (nouvelle page admin)
└── ...

config/
└── logo_config.php (classe centralisée)
```

---

## 🔒 Sécurité

✅ **Validation des fichiers :**
- Vérification du type MIME réel (pas juste l'extension)
- Taille maximale : 5MB
- Formats acceptés : PNG, JPEG, GIF, WebP

✅ **Protection d'accès :**
- Seuls les administrateurs peuvent modifier les logos
- Vérification de la session sur la page d'admin

✅ **Prévention XSS :**
- Tous les chemins sont échappés avec `htmlspecialchars()`

---

## 💡 Exemple complet

Voici comment modifier le logo dans une page :

**Avant :**
```html
<img class="img-fluid" src="images/univ.png" alt="Logo">
<link rel="shortcut icon" href="images/univ.png">
```

**Après :**
```php
<?php
include_once 'config/logo_config.php';
$logoConfig = getLogoConfig();
$logo = $logoConfig->getDefaultLogo();
?>
<img class="img-fluid" src="<?php echo htmlspecialchars($logo); ?>" alt="Logo">
<link rel="shortcut icon" href="<?php echo htmlspecialchars($logo); ?>">
```

---

## 📝 Notes importantes

1. **Le dossier `administrateur/logo/`** est créé automatiquement à la première utilisation
2. **La base de données** doit avoir la colonne `logo` dans la table `univ`
3. **Les fichiers anciens** restent dans le dossier pour l'historique
4. **La cache** est vidée automatiquement après chaque upload

---

## ❓ FAQ

**Q: Où sont stockés les logos ?**
R: Dans le dossier `administrateur/logo/` avec un nom unique : `logo_<code_univ>_<timestamp>.<extension>`

**Q: Puis-je réinitialiser au logo par défaut ?**
R: Oui, en supprimant le chemin du logo en base de données ou en créant un nouveau logo vide.

**Q: Comment ajouter un logo sur une page existante ?**
R: Utilisez la classe `LogoConfig` ou les fonctions helper de `php/lib.php` comme décrit ci-dessus.

**Q: La taille du logo est trop grande/petite ?**
R: Vous pouvez ajouter du CSS personnalisé avec `style="max-width: 100px;"` ou utiliser CSS classes.

---

## 🎯 Prochaines étapes

Pour améliorer encore le système, vous pouvez :

1. Ajouter une fonctionnalité de **crop/resize** avant l'upload
2. Implémenter un **historique des logos** avec la possibilité de revenir à une version antérieure
3. Ajouter des **logos différents par rôle** (administrateur, scolarité, enseignant, etc.)
4. Permettre des **logos temporaires** pour les événements spéciaux
5. Intégrer une **prévisualisation en temps réel** lors du upload

---

**✨ Système complètement opérationnel et prêt à l'emploi !**
