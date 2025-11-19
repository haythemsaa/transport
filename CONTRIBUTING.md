# 🤝 Guide de Contribution - Teleroute Marketplace

Merci de votre intérêt pour contribuer à Teleroute Marketplace ! Ce document explique comment participer au projet.

## 📋 Table des matières

- [Code de conduite](#code-de-conduite)
- [Comment contribuer](#comment-contribuer)
- [Standards de code](#standards-de-code)
- [Workflow Git](#workflow-git)
- [Tests](#tests)
- [Documentation](#documentation)
- [Questions](#questions)

---

## 🤗 Code de conduite

### Notre engagement

Nous nous engageons à faire de la participation à ce projet une expérience exempte de harcèlement pour tout le monde, quel que soit:
- L'âge
- La taille corporelle
- Le handicap
- L'ethnicité
- L'identité et l'expression de genre
- Le niveau d'expérience
- La nationalité
- L'apparence personnelle
- La race
- La religion
- L'identité et l'orientation sexuelles

### Comportements attendus

- Utiliser un langage accueillant et inclusif
- Respecter les points de vue et expériences différents
- Accepter gracieusement les critiques constructives
- Se concentrer sur ce qui est meilleuren pour la communauté
- Faire preuve d'empathie envers les autres membres

### Comportements inacceptables

- Langage ou imagerie sexualisés
- Trolling, commentaires insultants ou dérogatoires
- Harcèlement public ou privé
- Publication d'informations privées sans permission
- Autres conduites qui pourraient être jugées inappropriées

---

## 🚀 Comment contribuer

### Signaler un bug

Les bugs sont trackés via [GitHub Issues](https://github.com/haythemsaa/transport/issues).

**Avant de créer un rapport de bug:**
- Vérifiez qu'il n'existe pas déjà
- Collectez les informations nécessaires

**Pour un bon rapport de bug, incluez:**
- Un titre clair et descriptif
- Les étapes exactes pour reproduire le problème
- Le comportement attendu vs le comportement observé
- Captures d'écran si applicable
- Votre environnement (OS, PHP version, MySQL version)

**Template de bug report:**
```markdown
## Description
[Description claire du bug]

## Étapes pour reproduire
1. Aller sur '...'
2. Cliquer sur '...'
3. Voir l'erreur

## Comportement attendu
[Ce qui devrait se passer]

## Comportement actuel
[Ce qui se passe réellement]

## Screenshots
[Si applicable]

## Environnement
- OS: [e.g. Ubuntu 20.04]
- PHP: [e.g. 8.2]
- MySQL: [e.g. 8.0]
- Navigateur: [e.g. Chrome 118]
```

### Proposer une fonctionnalité

Les suggestions de fonctionnalités sont également trackées via Issues.

**Pour une bonne suggestion:**
- Titre clair décrivant la fonctionnalité
- Explication détaillée du problème résolu
- Exemples d'utilisation
- Alternatives considérées

**Template de feature request:**
```markdown
## Problème
[Quel problème cette fonctionnalité résout-elle?]

## Solution proposée
[Comment résoudre ce problème?]

## Alternatives
[Quelles autres solutions avez-vous considérées?]

## Contexte additionnel
[Screenshots, mockups, etc.]
```

### Pull Requests

1. **Fork** le repository
2. **Créer** une branche depuis `develop`:
   ```bash
   git checkout -b feature/ma-nouvelle-fonctionnalite
   ```
3. **Développer** votre fonctionnalité
4. **Tester** votre code
5. **Commiter** avec des messages clairs
6. **Pousser** vers votre fork
7. **Ouvrir** une Pull Request

**Checklist avant PR:**
- [ ] Code testé localement
- [ ] Pas de `console.log()` / `var_dump()` oubliés
- [ ] Documentation mise à jour si nécessaire
- [ ] CHANGELOG.md mis à jour
- [ ] Pas de credentials en dur
- [ ] Tests passent (si applicable)
- [ ] Code respecte les standards

---

## 💻 Standards de code

### PHP

**Style:** PSR-12

```php
<?php

namespace App\Models;

class ExampleClass
{
    private $property;

    public function exampleMethod($parameter)
    {
        if ($parameter === true) {
            return $this->property;
        }

        return null;
    }
}
```

**Bonnes pratiques:**
- Utilisez les type hints
- Documentez avec PHPDoc
- Toujours utiliser PDO avec prepared statements
- Échapper les données affichées avec `htmlspecialchars()` ou `h()`
- Valider toutes les entrées utilisateur
- Gérer les exceptions

**À éviter:**
- Variables globales
- `eval()`
- `extract()`
- Requêtes SQL directes
- `short_open_tag`

### JavaScript

**Style:** Standard JS

```javascript
// Bon
function calculateTotal(items) {
  return items.reduce((sum, item) => sum + item.price, 0)
}

// À éviter
function calculate_total(items) {
  var total = 0
  for (var i = 0; i < items.length; i++) {
    total = total + items[i].price
  }
  return total
}
```

**Bonnes pratiques:**
- Utiliser `const` et `let`, éviter `var`
- Noms de variables descriptifs
- Fonctions courtes et ciblées
- Commentaires pour logique complexe

### SQL

**Style:** snake_case

```sql
-- Bon
CREATE TABLE freight_offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loading_city VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- À éviter
CREATE TABLE FreightOffers (
    Id int,
    LoadingCity varchar(255)
);
```

### CSS

**Style:** BEM (Block Element Modifier)

```css
/* Block */
.card { }

/* Element */
.card__title { }
.card__content { }

/* Modifier */
.card--featured { }
.card__title--large { }
```

---

## 🌳 Workflow Git

### Branches

```
main              Production-ready code
  ├── develop     Development branch
  │   ├── feature/xxx    New features
  │   ├── bugfix/xxx     Bug fixes
  │   └── hotfix/xxx     Urgent production fixes
```

### Convention de nommage

```
feature/user-authentication
feature/advanced-search
bugfix/fix-login-error
hotfix/security-patch
docs/update-readme
refactor/optimize-queries
```

### Messages de commit

Format: `type(scope): description`

**Types:**
- `feat`: Nouvelle fonctionnalité
- `fix`: Correction de bug
- `docs`: Documentation
- `style`: Formatage, point-virgules manquants, etc.
- `refactor`: Refactorisation de code
- `test`: Ajout de tests
- `chore`: Maintenance, dépendances, etc.

**Exemples:**
```bash
feat(search): add advanced filters for freight search
fix(auth): resolve session timeout issue
docs(api): update API documentation for favorites endpoint
refactor(models): simplify User model queries
test(matching): add unit tests for matching algorithm
chore(deps): update Bootstrap to 5.3.2
```

**Description:**
- Utiliser l'impératif ("add" pas "added")
- Pas de majuscule au début
- Pas de point à la fin
- Maximum 72 caractères

**Body (optionnel):**
```
feat(search): add advanced filters for freight search

Add support for filtering by:
- Weight range
- Price range
- Vehicle type
- Specific dates

Closes #123
```

---

## 🧪 Tests

### Tests manuels

Avant de soumettre une PR, testez:

1. **Fonctionnalité principale**
   - La feature fonctionne comme prévu
   - Les cas limites sont gérés

2. **Régression**
   - Les fonctionnalités existantes fonctionnent toujours
   - Aucun effet de bord

3. **Sécurité**
   - Pas de failles XSS
   - Pas d'injection SQL
   - CSRF protection en place

4. **Performance**
   - Pas de requêtes N+1
   - Requêtes optimisées

### Tests automatisés (si applicable)

```bash
# Lint PHP
find . -name "*.php" -exec php -l {} \;

# Tests unitaires
php vendor/bin/phpunit

# Tests d'intégration
./scripts/test.sh
```

---

## 📚 Documentation

### Code

Documentez les fonctions complexes:

```php
/**
 * Calculate compatibility score between freight and vehicle
 *
 * @param array $freight Freight offer data
 * @param array $vehicle Vehicle offer data
 * @return int Score between 0 and 100
 */
function calculateCompatibility($freight, $vehicle) {
    // ...
}
```

### Fichiers README

Mettez à jour la documentation si vous:
- Ajoutez une nouvelle fonctionnalité
- Modifiez le comportement existant
- Ajoutez des dépendances
- Changez la configuration

### CHANGELOG

Ajoutez vos changements à `CHANGELOG.md`:

```markdown
## [Unreleased]

### Added
- Advanced search filters for freight (#123)

### Fixed
- Session timeout issue (#124)

### Changed
- Improved matching algorithm performance
```

---

## ❓ Questions

### Où poser des questions ?

- **Questions générales**: [GitHub Discussions](https://github.com/haythemsaa/transport/discussions)
- **Bugs**: [GitHub Issues](https://github.com/haythemsaa/transport/issues)
- **Email**: dev@teleroute-marketplace.com

### Processus de review

1. Un mainteneur reviewera votre PR
2. Des changements pourront être demandés
3. Une fois approuvée, la PR sera mergée
4. Votre contribution sera ajoutée au CHANGELOG

### Temps de réponse

- Issues: ~48h
- Pull Requests: ~72h
- Questions: ~24h

---

## 🎯 Priorités actuelles

Contributions particulièrement bienvenues sur:

- [ ] Tests unitaires et d'intégration
- [ ] Optimisations de performance
- [ ] Accessibilité (WCAG 2.1)
- [ ] Internationalisation (i18n)
- [ ] Documentation API
- [ ] Exemples d'utilisation

---

## 🏆 Contributeurs

Merci à tous ceux qui ont contribué ! Votre nom apparaîtra ici.

<!-- ALL-CONTRIBUTORS-LIST:START -->
<!-- ALL-CONTRIBUTORS-LIST:END -->

---

## 📜 Licence

En contribuant, vous acceptez que vos contributions soient licensées sous la licence MIT du projet.

---

## 🙏 Merci !

Votre contribution, quelle qu'elle soit, est appréciée. Chaque bug report, suggestion, documentation ou code aide à améliorer le projet.

**Happy coding! 🚀**

---

**Dernière mise à jour**: 18 Novembre 2024
