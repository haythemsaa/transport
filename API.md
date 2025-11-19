# 📡 API Documentation - Teleroute Marketplace

Documentation complète des API REST de Teleroute Marketplace.

## Table des matières

- [Introduction](#introduction)
- [Authentification](#authentification)
- [Endpoints](#endpoints)
- [Codes de réponse](#codes-de-réponse)
- [Exemples](#exemples)
- [Rate Limiting](#rate-limiting)

---

## Introduction

L'API Teleroute Marketplace fournit un accès programmatique aux fonctionnalités de la plateforme.

### Base URL

```
https://teleroute-marketplace.com/api
```

### Format des réponses

Toutes les API retournent du JSON avec la structure suivante:

**Succès:**
```json
{
  "success": true,
  "data": { ... }
}
```

**Erreur:**
```json
{
  "success": false,
  "error": "Message d'erreur"
}
```

---

## Authentification

L'authentification se fait via les sessions PHP. L'utilisateur doit être connecté.

**Headers requis:**
```
Cookie: TELEROUTE_SESSION=xxx
```

Pour les requêtes modifiant des données (POST, PUT, DELETE), un token CSRF est requis:
```
X-CSRF-Token: token_value
```

---

## Endpoints

### 1. Favoris

#### GET `/api/favorites.php?action=list&type={type}`

Récupère la liste des favoris de l'utilisateur.

**Paramètres:**
- `action` (string) - Action à effectuer: `list`
- `type` (string, optionnel) - Type: `freight` ou `vehicle`

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "offer_type": "freight",
      "offer_id": 123,
      "created_at": "2024-11-18 10:30:00",
      "offer_details": {
        "loading_city": "Paris",
        "delivery_city": "Lyon",
        "weight": 15000,
        "price": 850
      }
    }
  ]
}
```

#### POST `/api/favorites.php`

Ajoute une offre aux favoris.

**Body:**
```json
{
  "action": "add",
  "offer_type": "freight",
  "offer_id": 123
}
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "favorite_id": 45,
    "message": "Offre ajoutée aux favoris"
  }
}
```

#### POST `/api/favorites.php` (Remove)

Retire une offre des favoris.

**Body:**
```json
{
  "action": "remove",
  "favorite_id": 45
}
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "message": "Favori supprimé"
  }
}
```

---

### 2. Notifications

#### GET `/api/notifications.php?action=count`

Compte les notifications non lues.

**Réponse:**
```json
{
  "count": 5
}
```

#### GET `/api/notifications.php?action=recent&limit=10`

Récupère les notifications récentes.

**Paramètres:**
- `action` (string) - `recent`
- `limit` (int, optionnel) - Nombre de notifications (défaut: 10)

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "message",
      "title": "Nouveau message",
      "message": "Vous avez reçu un message de Transport ABC",
      "is_read": false,
      "created_at": "2024-11-18 14:25:00"
    }
  ]
}
```

#### POST `/api/notifications.php`

Marque une notification comme lue.

**Body:**
```json
{
  "action": "mark_read",
  "notification_id": 123
}
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "message": "Notification marquée comme lue"
  }
}
```

#### POST `/api/notifications.php` (Mark all read)

Marque toutes les notifications comme lues.

**Body:**
```json
{
  "action": "mark_all_read"
}
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "count": 5,
    "message": "5 notifications marquées comme lues"
  }
}
```

#### POST `/api/notifications.php` (Delete)

Supprime une notification.

**Body:**
```json
{
  "action": "delete",
  "notification_id": 123
}
```

---

### 3. Messages

#### GET `/api/messages.php?conversation_id={id}&limit=50`

Récupère les messages d'une conversation.

**Paramètres:**
- `conversation_id` (int) - ID de la conversation
- `limit` (int, optionnel) - Nombre de messages (défaut: 50)

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "sender_id": 10,
      "sender_name": "Transport ABC",
      "receiver_id": 5,
      "message": "Bonjour, est-ce que l'offre est toujours disponible?",
      "is_read": true,
      "created_at": "2024-11-18 10:00:00"
    }
  ]
}
```

#### POST `/api/messages.php`

Envoie un message.

**Body:**
```json
{
  "receiver_id": 10,
  "message": "Oui, l'offre est toujours disponible"
}
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "message_id": 123,
    "conversation_id": 45,
    "created_at": "2024-11-18 14:30:00"
  }
}
```

---

### 4. Carte (Map Data)

#### GET `/api/map-data.php?type={type}`

Récupère les données pour la carte interactive.

**Paramètres:**
- `type` (string, optionnel) - `freight`, `vehicle`, ou `both` (défaut)

**Réponse:**
```json
{
  "freight": [
    {
      "id": 1,
      "loading_city": "Paris",
      "delivery_city": "Lyon",
      "loading_lat": 48.8566,
      "loading_lng": 2.3522,
      "delivery_lat": 45.7640,
      "delivery_lng": 4.8357,
      "weight": 15000,
      "price": 850,
      "user": {
        "company_name": "Transport ABC",
        "rating": 4.5
      }
    }
  ],
  "vehicles": [
    {
      "id": 1,
      "departure_city": "Lyon",
      "destination_city": "Marseille",
      "departure_lat": 45.7640,
      "departure_lng": 4.8357,
      "destination_lat": 43.2965,
      "destination_lng": 5.3698,
      "vehicle_type": "Semi-remorque",
      "capacity": 24000,
      "user": {
        "company_name": "Fret Express",
        "rating": 4.8
      }
    }
  ]
}
```

---

### 5. Suppression d'offres

#### POST `/api/delete-freight.php`

Supprime une offre de fret.

**Body:**
```json
{
  "id": 123,
  "csrf_token": "token_value"
}
```

**Réponse:**
```json
{
  "success": true,
  "message": "Offre supprimée avec succès"
}
```

#### POST `/api/delete-vehicle.php`

Supprime une offre de véhicule.

**Body:**
```json
{
  "id": 456,
  "csrf_token": "token_value"
}
```

**Réponse:**
```json
{
  "success": true,
  "message": "Offre supprimée avec succès"
}
```

---

## Codes de réponse

| Code | Description |
|------|-------------|
| 200 | Succès |
| 400 | Requête invalide |
| 401 | Non authentifié |
| 403 | Non autorisé |
| 404 | Ressource non trouvée |
| 422 | Données de validation invalides |
| 429 | Trop de requêtes (rate limit) |
| 500 | Erreur serveur |

---

## Exemples

### JavaScript (Fetch API)

```javascript
// Récupérer les favoris
fetch('/api/favorites.php?action=list&type=freight')
  .then(response => response.json())
  .then(data => {
    console.log('Favoris:', data);
  });

// Ajouter aux favoris
fetch('/api/favorites.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-Token': getCsrfToken()
  },
  body: JSON.stringify({
    action: 'add',
    offer_type: 'freight',
    offer_id: 123
  })
})
.then(response => response.json())
.then(data => {
  console.log('Résultat:', data);
});
```

### jQuery (AJAX)

```javascript
// Compter les notifications
$.getJSON('/api/notifications.php?action=count', function(data) {
  $('#notif-badge').text(data.count);
});

// Marquer comme lu
$.ajax({
  url: '/api/notifications.php',
  method: 'POST',
  contentType: 'application/json',
  data: JSON.stringify({
    action: 'mark_read',
    notification_id: 123
  }),
  success: function(data) {
    console.log('Notification lue');
  }
});
```

### cURL

```bash
# Récupérer les notifications récentes
curl -X GET 'https://teleroute-marketplace.com/api/notifications.php?action=recent&limit=5' \
  -H 'Cookie: TELEROUTE_SESSION=xxx'

# Envoyer un message
curl -X POST 'https://teleroute-marketplace.com/api/messages.php' \
  -H 'Content-Type: application/json' \
  -H 'Cookie: TELEROUTE_SESSION=xxx' \
  -H 'X-CSRF-Token: token_value' \
  -d '{
    "receiver_id": 10,
    "message": "Message de test"
  }'
```

### PHP

```php
// Utilisation interne
require_once 'api/favorites.php';

// Ou via HTTP
$ch = curl_init('https://teleroute-marketplace.com/api/favorites.php?action=list');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, 'TELEROUTE_SESSION=' . session_id());
$response = curl_exec($ch);
$data = json_decode($response, true);
```

---

## Rate Limiting

**Limites:**
- 60 requêtes par minute par utilisateur
- 1000 requêtes par heure par utilisateur

**Headers de réponse:**
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1637252400
```

**Dépassement de limite:**
```json
{
  "success": false,
  "error": "Rate limit exceeded. Try again in 30 seconds."
}
```

---

## Webhooks (À venir)

Les webhooks permettront de recevoir des notifications en temps réel sur:
- Nouvelles offres correspondant à des critères
- Nouveaux messages
- Changements de statut de transaction

Documentation à venir dans la prochaine version.

---

## Support

Pour toute question concernant l'API:
- Email: api@teleroute-marketplace.com
- Documentation: https://docs.teleroute-marketplace.com
- Issues GitHub: https://github.com/haythemsaa/transport/issues

---

**Version**: 1.0.0
**Dernière mise à jour**: 18 Novembre 2024
