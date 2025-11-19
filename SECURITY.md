# 🔒 Politique de Sécurité

## Versions supportées

Nous fournissons des mises à jour de sécurité pour les versions suivantes:

| Version | Supportée          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Signaler une vulnérabilité

### ⚠️ NE PAS créer d'issue publique

Si vous découvrez une vulnérabilité de sécurité, **ne créez PAS d'issue publique** sur GitHub.

### 📧 Rapport privé

Envoyez un email à: **security@teleroute-marketplace.com**

**Incluez dans votre rapport:**
- Description de la vulnérabilité
- Étapes pour reproduire
- Impact potentiel
- Suggestions de correctif (optionnel)
- Votre nom/pseudo (pour crédit si vous le souhaitez)

### ⏱️ Temps de réponse

- **Accusé de réception**: sous 48 heures
- **Évaluation initiale**: sous 7 jours
- **Correctif**: selon gravité (voir ci-dessous)

### 🎯 Niveaux de gravité

| Niveau | Description | Temps de correctif |
|--------|-------------|-------------------|
| 🔴 **Critique** | Exploitation à distance sans authentification | 24-48h |
| 🟠 **Élevé** | Exploitation nécessitant une authentification | 7 jours |
| 🟡 **Moyen** | Exposition limitée de données | 30 jours |
| 🟢 **Faible** | Impact minimal | 90 jours |

### 🏆 Programme de Reconnaissance

Nous reconnaissons et créditons publiquement les chercheurs en sécurité qui:
- Signalent des vulnérabilités de manière responsable
- Suivent notre processus de divulgation
- Ne testent pas sur les systèmes de production sans autorisation

**Hall of Fame**: Les contributeurs seront listés dans notre SECURITY_HALL_OF_FAME.md

---

## 🛡️ Mesures de sécurité implémentées

### Authentification & Autorisation

✅ **Hashage des mots de passe**
- Bcrypt avec salt automatique
- Coût minimum: 10 rounds
- Ré-hashage automatique si nécessaire

✅ **Sessions sécurisées**
- Cookie HttpOnly
- Cookie SameSite (Lax/Strict)
- Régénération de session après login
- Timeout automatique (2 heures)

✅ **Protection contre le brute force**
- Limitation des tentatives de login (5 max)
- Lockout progressif (15 minutes)
- Logging des tentatives suspectes

### Injection

✅ **SQL Injection**
- 100% PDO avec prepared statements
- Pas de requêtes dynamiques directes
- Validation des types de données

✅ **XSS (Cross-Site Scripting)**
- `htmlspecialchars()` sur toutes les sorties
- Helper `h()` pour échappement facile
- Content Security Policy (CSP)

✅ **CSRF (Cross-Site Request Forgery)**
- Token sur tous les formulaires
- Validation côté serveur
- Token rotatif par session

### Headers de sécurité

```
X-Frame-Options: SAMEORIGIN
X-XSS-Protection: 1; mode=block
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: default-src 'self'; ...
Permissions-Policy: geolocation=(self), microphone=(), camera=()
```

### Upload de fichiers

✅ **Validation stricte**
- Vérification du type MIME
- Whitelist d'extensions (.jpg, .pdf, etc.)
- Limite de taille (10 MB)
- Scan antivirus recommandé

✅ **Isolation**
- Uploads hors du webroot si possible
- Désactivation de l'exécution PHP dans /uploads
- Noms de fichiers randomisés

### Base de données

✅ **Principe du moindre privilège**
- Compte DB dédié par environnement
- Permissions minimales nécessaires
- Pas d'accès root en production

✅ **Chiffrement**
- Connexions SSL/TLS recommandées
- Données sensibles hashées ou chiffrées
- Sauvegardes chiffrées

### Logging & Monitoring

✅ **Audit logging**
- Connexions/déconnexions
- Modifications de données sensibles
- Erreurs de sécurité
- Tentatives d'accès non autorisé

✅ **Alertes**
- Multiples échecs de login
- Modifications suspectes
- Erreurs 403/404 répétées

---

## 🔐 Bonnes pratiques pour les contributeurs

### Avant de commiter

- [ ] Pas de credentials en dur (API keys, passwords, tokens)
- [ ] Utiliser `.env` pour les secrets
- [ ] Vérifier `.gitignore` (exclure .env, logs, uploads)
- [ ] Scanner avec `git-secrets` ou `gitleaks`

### Développement

```php
// ✅ BON
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// ❌ MAUVAIS
$result = $pdo->query("SELECT * FROM users WHERE email = '$email'");
```

```php
// ✅ BON
echo htmlspecialchars($user_input);
echo h($user_input); // Helper function

// ❌ MAUVAIS
echo $user_input;
```

```php
// ✅ BON
if (verifyCsrfToken($_POST['csrf_token'])) {
    // Process form
}

// ❌ MAUVAIS
// Process form directly
```

### Dépendances

- Maintenir à jour les dépendances
- Scanner avec `composer audit` (PHP)
- Vérifier les CVEs connues
- Préférer les packages bien maintenus

---

## 🚨 Incidents de sécurité passés

Aucun incident de sécurité n'a été signalé à ce jour.

Les futurs incidents seront documentés ici avec:
- Date de découverte
- Nature de la vulnérabilité
- Impact
- Mesures de remédiation
- Crédit au chercheur (si applicable)

---

## 📋 Checklist de sécurité

### Pour les administrateurs

- [ ] Changer les credentials par défaut
- [ ] Activer HTTPS (Let's Encrypt)
- [ ] Configurer le firewall (UFW, iptables)
- [ ] Désactiver l'affichage des erreurs en production
- [ ] Limiter les permissions fichiers (755/644)
- [ ] Configurer les backups automatiques
- [ ] Activer les logs de sécurité
- [ ] Installer fail2ban ou équivalent
- [ ] Mettre à jour régulièrement le système
- [ ] Surveiller les logs d'accès

### Pour les développeurs

- [ ] Valider toutes les entrées utilisateur
- [ ] Échapper toutes les sorties
- [ ] Utiliser des prepared statements
- [ ] Implémenter la protection CSRF
- [ ] Hasher les mots de passe avec bcrypt
- [ ] Utiliser HTTPS pour les cookies sensibles
- [ ] Limiter les tentatives de login
- [ ] Logger les événements de sécurité
- [ ] Tester les vulnérabilités communes (OWASP Top 10)
- [ ] Code review avant merge

---

## 🔗 Ressources

### OWASP Top 10 (2021)

1. Broken Access Control
2. Cryptographic Failures
3. Injection
4. Insecure Design
5. Security Misconfiguration
6. Vulnerable and Outdated Components
7. Identification and Authentication Failures
8. Software and Data Integrity Failures
9. Security Logging and Monitoring Failures
10. Server-Side Request Forgery (SSRF)

### Outils recommandés

**Scan de vulnérabilités:**
- [OWASP ZAP](https://www.zaproxy.org/)
- [Burp Suite](https://portswigger.net/burp)
- [SQLMap](https://sqlmap.org/)

**Analyse statique:**
- [PHPStan](https://phpstan.org/)
- [Psalm](https://psalm.dev/)
- [SonarQube](https://www.sonarqube.org/)

**Scan de dépendances:**
- `composer audit`
- [Snyk](https://snyk.io/)
- [Dependabot](https://github.com/dependabot)

**Secrets:**
- [git-secrets](https://github.com/awslabs/git-secrets)
- [gitleaks](https://github.com/zricethezav/gitleaks)
- [TruffleHog](https://github.com/trufflesecurity/trufflehog)

### Liens utiles

- [OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [Content Security Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)
- [RGPD](https://www.cnil.fr/)

---

## 📞 Contact

- **Email sécurité**: security@teleroute-marketplace.com
- **PGP Key**: [À venir]
- **HackerOne**: [À venir]

---

## 📜 Politique de divulgation

Nous suivons une **divulgation coordonnée**:

1. Vous signalez la vulnérabilité en privé
2. Nous confirmons et évaluons
3. Nous développons un correctif
4. Nous testons le correctif
5. Nous déployons le correctif
6. Nous publions un advisory de sécurité
7. Vous pouvez publier vos recherches (après accord)

**Délai de divulgation**: 90 jours après le rapport initial, sauf accord contraire.

---

## 🏅 Remerciements

Nous remercions les chercheurs en sécurité qui nous aident à maintenir la plateforme sécurisée.

<!-- Liste des contributeurs sécurité -->

---

**Dernière mise à jour**: 18 Novembre 2024

*Cette politique peut être modifiée à tout moment. Les changements seront communiqués via notre blog et par email.*
