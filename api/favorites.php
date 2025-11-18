<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

$user = $_SESSION['user'];

try {
    $db = Database::getInstance()->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Add to favorites
        $offerType = $_POST['offer_type'] ?? '';
        $offerId = (int)($_POST['offer_id'] ?? 0);

        if (!in_array($offerType, ['freight', 'vehicle']) || $offerId <= 0) {
            throw new Exception('Paramètres invalides');
        }

        // Check if already in favorites
        $stmt = $db->prepare("
            SELECT id FROM favorites
            WHERE user_id = ? AND offer_type = ? AND offer_id = ?
        ");
        $stmt->execute([$user['id'], $offerType, $offerId]);

        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Déjà dans les favoris']);
            exit;
        }

        // Add to favorites
        $stmt = $db->prepare("
            INSERT INTO favorites (user_id, offer_type, offer_id, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$user['id'], $offerType, $offerId]);

        echo json_encode(['success' => true, 'message' => 'Ajouté aux favoris']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Remove from favorites
        parse_str(file_get_contents('php://input'), $_DELETE);
        $offerType = $_DELETE['offer_type'] ?? '';
        $offerId = (int)($_DELETE['offer_id'] ?? 0);

        if (!in_array($offerType, ['freight', 'vehicle']) || $offerId <= 0) {
            throw new Exception('Paramètres invalides');
        }

        $stmt = $db->prepare("
            DELETE FROM favorites
            WHERE user_id = ? AND offer_type = ? AND offer_id = ?
        ");
        $stmt->execute([$user['id'], $offerType, $offerId]);

        echo json_encode(['success' => true, 'message' => 'Retiré des favoris']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Check if in favorites
        $offerType = $_GET['offer_type'] ?? '';
        $offerId = (int)($_GET['offer_id'] ?? 0);

        if (!in_array($offerType, ['freight', 'vehicle']) || $offerId <= 0) {
            throw new Exception('Paramètres invalides');
        }

        $stmt = $db->prepare("
            SELECT id FROM favorites
            WHERE user_id = ? AND offer_type = ? AND offer_id = ?
        ");
        $stmt->execute([$user['id'], $offerType, $offerId]);

        $isFavorite = $stmt->fetch() !== false;

        echo json_encode(['success' => true, 'is_favorite' => $isFavorite]);

    } else {
        throw new Exception('Méthode non autorisée');
    }

} catch (Exception $e) {
    logError('Favorites API error', ['error' => $e->getMessage()]);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
