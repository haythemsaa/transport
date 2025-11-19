# 🧪 Tests - Teleroute Marketplace

Suite de tests automatisés pour assurer la qualité et la stabilité de l'application.

## 📋 Table des matières

- [Installation](#installation)
- [Exécution des tests](#exécution-des-tests)
- [Structure des tests](#structure-des-tests)
- [Couverture de code](#couverture-de-code)
- [Écrire des tests](#écrire-des-tests)
- [CI/CD Integration](#cicd-integration)

---

## 🚀 Installation

### Prérequis

- PHP 8.2+
- PHPUnit 10.0+
- Base de données MySQL pour les tests

### Installer PHPUnit

**Via Composer (recommandé) :**

```bash
composer require --dev phpunit/phpunit
```

**Via PHAR :**

```bash
wget https://phar.phpunit.de/phpunit-10.phar
chmod +x phpunit-10.phar
sudo mv phpunit-10.phar /usr/local/bin/phpunit
```

### Configuration de la base de données de test

1. Créer une base de données dédiée aux tests :

```sql
CREATE DATABASE teleroute_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'teleroute_test'@'localhost' IDENTIFIED BY 'test_password';
GRANT ALL PRIVILEGES ON teleroute_test.* TO 'teleroute_test'@'localhost';
FLUSH PRIVILEGES;
```

2. Importer le schéma :

```bash
mysql -u teleroute_test -p teleroute_test < database/schema.sql
```

3. Configurer les variables d'environnement (`.env.testing`) :

```env
APP_ENV=testing
DB_HOST=localhost
DB_NAME=teleroute_test
DB_USER=teleroute_test
DB_PASS=test_password
```

---

## ▶️ Exécution des tests

### Tous les tests

```bash
# Avec Composer
./vendor/bin/phpunit

# Avec PHAR
phpunit
```

### Test spécifique

```bash
phpunit tests/DatabaseTest.php
```

### Avec couverture de code

```bash
phpunit --coverage-html coverage/
```

Ouvrir ensuite `coverage/index.html` dans le navigateur.

### Mode verbeux

```bash
phpunit --verbose
```

### Arrêter au premier échec

```bash
phpunit --stop-on-failure
```

---

## 📁 Structure des tests

```
tests/
├── bootstrap.php          # Initialisation de l'environnement de test
├── README.md             # Cette documentation
├── DatabaseTest.php      # Tests de connexion et opérations DB
├── ValidatorTest.php     # Tests de validation des entrées
└── AuthTest.php          # Tests d'authentification
```

### Tests disponibles

#### 🗄️ **DatabaseTest**
- ✅ Connexion à la base de données
- ✅ Exécution de requêtes
- ✅ Prepared statements
- ✅ Existence des tables requises
- ✅ Transactions et rollback

#### ✔️ **ValidatorTest**
- ✅ Validation email
- ✅ Validation téléphone
- ✅ Validation SIRET
- ✅ Validation mot de passe
- ✅ Validation date
- ✅ Validation URL
- ✅ Sanitization XSS

#### 🔐 **AuthTest**
- ✅ Hashage de mot de passe (bcrypt)
- ✅ Vérification de mot de passe
- ✅ Génération de token CSRF
- ✅ Vérification de token CSRF
- ✅ Validation des types d'utilisateur
- ✅ Rate limiting

---

## 📊 Couverture de code

### Générer un rapport de couverture

```bash
phpunit --coverage-html coverage/html
```

### Formats disponibles

```bash
# HTML (interactif)
phpunit --coverage-html coverage/html

# Texte (terminal)
phpunit --coverage-text

# Clover XML (pour CI/CD)
phpunit --coverage-clover coverage/clover.xml
```

### Objectif de couverture

- **Minimum acceptable** : 70%
- **Recommandé** : 80%+
- **Excellent** : 90%+

---

## ✍️ Écrire des tests

### Template de base

```php
<?php
use PHPUnit\Framework\TestCase;

class MyFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        // Configuration avant chaque test
        parent::setUp();
    }

    public function testSomething()
    {
        // Arrange (Préparer)
        $value = 42;

        // Act (Agir)
        $result = someFunction($value);

        // Assert (Vérifier)
        $this->assertEquals(84, $result);
    }

    protected function tearDown(): void
    {
        // Nettoyage après chaque test
        parent::tearDown();
    }
}
```

### Assertions courantes

```php
// Égalité
$this->assertEquals($expected, $actual);
$this->assertNotEquals($expected, $actual);

// Identité stricte
$this->assertSame($expected, $actual);
$this->assertNotSame($expected, $actual);

// Booléens
$this->assertTrue($condition);
$this->assertFalse($condition);

// Null
$this->assertNull($value);
$this->assertNotNull($value);

// Tableaux
$this->assertArrayHasKey('key', $array);
$this->assertContains($needle, $haystack);

// Exceptions
$this->expectException(Exception::class);

// Chaînes de caractères
$this->assertStringContains('needle', $haystack);
$this->assertMatchesRegularExpression('/pattern/', $string);
```

### Bonnes pratiques

1. **Un test = une assertion principale**
2. **Noms descriptifs** : `testUserCanLoginWithValidCredentials()`
3. **Arrange-Act-Assert** : Structure claire
4. **Tests isolés** : Pas de dépendances entre tests
5. **Données de test** : Utiliser des fixtures
6. **Cleanup** : Nettoyer après chaque test

---

## 🔄 CI/CD Integration

### GitHub Actions

Le workflow CI (`.github/workflows/ci.yml`) exécute automatiquement les tests :

```yaml
- name: Run PHPUnit tests
  run: vendor/bin/phpunit --coverage-text
```

### GitLab CI

```yaml
test:
  script:
    - composer install
    - phpunit --coverage-text
```

### Jenkins

```groovy
stage('Test') {
    steps {
        sh 'composer install'
        sh 'phpunit --coverage-clover coverage.xml'
        publishHTML([reportDir: 'coverage', reportFiles: 'index.html'])
    }
}
```

---

## 🐛 Débogage des tests

### Afficher les sorties

```bash
phpunit --debug
phpunit --verbose
```

### Logs détaillés

```bash
phpunit --testdox
```

### Exécuter un seul test

```bash
phpunit --filter testDatabaseConnection
```

---

## 📚 Ressources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Test-Driven Development (TDD)](https://en.wikipedia.org/wiki/Test-driven_development)
- [PHP Testing Best Practices](https://phpunit.de/documentation.html)

---

## 🤝 Contribution

Avant de soumettre une PR, assurez-vous que :

- ✅ Tous les tests passent : `phpunit`
- ✅ La couverture ne diminue pas
- ✅ Les nouveaux features ont des tests
- ✅ Les tests sont documentés

---

**Dernière mise à jour** : 18 Novembre 2024
