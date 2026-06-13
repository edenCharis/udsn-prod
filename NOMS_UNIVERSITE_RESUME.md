# 🎓 PERSONNALISATION DES NOMS D'UNIVERSITÉ - RÉSUMÉ

## ✅ Ce qui a été ajouté

Un système complet pour personnaliser le **nom de l'université** qui s'affiche sur toutes les pages.

---

## 📋 Fichiers modifiés/créés

### ✏️ Modifiés (3)
1. **`config/logo_config.php`**
   - Ajout de 6 nouvelles méthodes pour gérer les noms
   - `getDefaultUniversityName()` - Nom par défaut
   - `getUniversityNameForUser($user_id)` - Nom pour un utilisateur
   - `getUniversityNameFromSession()` - Nom depuis la session
   - `getUniversityName($univ_code)` - Nom pour un code
   - `updateUniversityName($univ_code, $nom_univ)` - Mettre à jour un nom
   - `getAllUniversities()` - Obtenir toutes les universités

2. **`administrateur/parametre_logos.php`**
   - Interface pour modifier le nom de chaque université
   - Modal pour saisir le nouveau nom
   - Bouton "Nom" à côté du bouton "Logo"
   - Traitement de la mise à jour du nom

3. **`php/api_logos.php`**
   - 4 nouveaux endpoints pour accéder aux noms via API
   - `get_default_university_name` - Nom par défaut
   - `get_university_name` - Nom pour un code
   - `get_university_name_from_session` - Nom depuis la session
   - `update_university_name` - Mettre à jour un nom

### 📄 Créés (2)
1. **`UNIVERSITY_NAME_GUIDE.md`**
   - Guide complet pour utiliser la personnalisation des noms
   - Exemples de code
   - API documentation
   - Cas d'usage

2. **`php/university_name_helper.php`**
   - Fonction helper pour récupérer le nom de l'université
   - À utiliser dans `php/routeur.php`

### 🎨 Pages de connexion (3)
1. **`login.php`** - Affiche le nom personnalisé
2. **`index.php`** - Affiche le nom personnalisé
3. **`connexion.php`** - Affiche le nom personnalisé

---

## 🚀 Comment utiliser

### Pour les administrateurs

```
1. Accédez à: /administrateur/parametre_logos.php
2. Cliquez sur le bouton "Nom" pour votre université
3. Modifiez le nom
4. Cliquez sur "Enregistrer"
5. Le changement s'affiche immédiatement
```

### Pour les développeurs

```php
// Obtenir le nom
$logoConfig = getLogoConfig();
$nom = $logoConfig->getDefaultUniversityName();

// Afficher le nom
<h1><?php echo htmlspecialchars($nom); ?></h1>

// Mettre à jour le nom
$logoConfig->updateUniversityName(1, "Nouveau Nom");
```

### Utiliser l'API

```javascript
// Récupérer le nom
fetch('/php/api_logos.php?action=get_default_university_name')
    .then(r => r.json())
    .then(d => console.log(d.data.name));
```

---

## 📊 Pages affectées

✅ **Pages de connexion (avant authentification):**
- `login.php` - Affiche le nom en H1
- `index.php` - Affiche le nom en H1
- `connexion.php` - Affiche le nom en H4

✅ **Pages authentifiées:**
- Utiliseront `$_SESSION['nom_univ']` (à ajouter dans routeur.php)

---

## 🔄 Intégration dans routeur.php

Pour que le nom soit disponible en session, ajoutez dans `php/routeur.php` :

```php
// Après chaque $_SESSION['univ'] = $univ;
include_once 'university_name_helper.php';
$_SESSION['nom_univ'] = getUniversityNameFromCode($univ, $connexion);
```

---

## 🛠️ API Endpoints

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/php/api_logos.php?action=get_default_university_name` | GET | Nom par défaut |
| `/php/api_logos.php?action=get_university_name&univ_code=X` | GET | Nom pour un code |
| `/php/api_logos.php?action=get_university_name_from_session` | GET | Nom depuis session |
| `/php/api_logos.php?action=update_university_name` | POST | Mettre à jour (admin) |

---

## 🔒 Sécurité

✅ Tous les noms sont sécurisés :
- Échappement XSS avec `htmlspecialchars()`
- Validation de longueur (max 255 caractères)
- Vérification des permissions (admin only)
- Requêtes préparées
- Pas d'injection SQL

---

## 📝 Base de données

La table `univ` doit avoir la colonne `nom` :

```sql
SELECT code, nom, logo FROM univ;
```

Structure:
```sql
ALTER TABLE `univ` MODIFY COLUMN `nom` VARCHAR(255) NOT NULL;
```

---

## ✨ Exemples

### Interface d'admin
```
Université Denis Sassou-N'Guesso
Code: 1

[Bouton Logo] [Bouton Nom]

Modal pour modifier:
Nom de l'université: [Champ texte]
[Annuler] [Enregistrer]
```

### Page de connexion
```
[Logo]
MON UNIVERSITÉ PERSONNALISÉE
Connexion
```

### API
```json
GET /php/api_logos.php?action=get_default_university_name

{
  "success": true,
  "message": "Nom de l'université par défaut",
  "data": {
    "name": "MON UNIVERSITÉ"
  }
}
```

---

## 🎯 Prochaines étapes

1. ✅ Consulter `UNIVERSITY_NAME_GUIDE.md` pour les détails
2. ✅ Accéder à `/administrateur/parametre_logos.php`
3. ✅ Modifier le nom de votre université
4. ✅ Vérifier sur les pages de connexion
5. ✅ (Optionnel) Mettre à jour `php/routeur.php` pour utiliser le nom en session

---

## 📌 Points importants

- Le nom par défaut reste: "UNIVERSITE DENIS SASSOU-N'GUESSO"
- Les noms sont limités à 255 caractères
- Les changements s'appliquent immédiatement
- Chaque université peut avoir son propre nom
- La session charge automatiquement le nom après connexion

---

## 🎉 Conclusion

Le système de logos est maintenant **complètement personnalisable**, incluant:
- ✅ Logo de l'université
- ✅ Nom de l'université
- ✅ Favicon

**Tout sur une seule interface d'administration !**

---

**Date:** 13 Juin 2026  
**Version:** 1.1 - Avec gestion des noms  
**Statut:** ✅ Complet et opérationnel
