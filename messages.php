<?php
require_once 'config/config.php';
require_once 'config/database.php';

if (!isLoggedIn()) {
    setFlash('warning', 'Veuillez vous connecter pour accéder à vos messages');
    redirect('/login.php');
}

$pageTitle = 'Messagerie';
$userId = $_SESSION['user_id'];

$messageModel = new Message();

// Traiter l'envoi d'un nouveau message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $conversationId = (int)$_POST['conversation_id'];
        $message = trim($_POST['message']);

        if (!empty($message)) {
            $result = $messageModel->sendMessage($conversationId, $userId, $message);
            if (!$result['success']) {
                setFlash('danger', 'Erreur lors de l\'envoi du message');
            }
        }
    }
}

// Créer ou récupérer une conversation
$activeConversationId = null;
if (isset($_GET['offer_type']) && isset($_GET['offer_id']) && isset($_GET['user_id'])) {
    $conversation = $messageModel->getOrCreateConversation(
        $userId,
        (int)$_GET['user_id'],
        $_GET['offer_type'],
        (int)$_GET['offer_id']
    );
    if ($conversation) {
        $activeConversationId = $conversation['id'];
    }
} elseif (isset($_GET['conversation_id'])) {
    $activeConversationId = (int)$_GET['conversation_id'];
}

// Récupérer toutes les conversations
$conversations = $messageModel->getUserConversations($userId);

// Récupérer les messages de la conversation active
$messages = [];
if ($activeConversationId) {
    $messages = $messageModel->getMessages($activeConversationId);
    $messageModel->markAsRead($activeConversationId, $userId);
}

include 'includes/header.php';
?>

<div class="container-fluid my-4">
    <div class="row">
        <!-- Liste des conversations -->
        <div class="col-lg-4 col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-chat-dots"></i> Conversations
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 70vh; overflow-y: auto;">
                        <?php if (!empty($conversations)): ?>
                            <?php foreach ($conversations as $conv): ?>
                                <a href="/messages.php?conversation_id=<?= $conv['id'] ?>"
                                   class="list-group-item list-group-item-action <?= $conv['id'] == $activeConversationId ? 'active' : '' ?> <?= $conv['unread_count'] > 0 ? 'unread' : '' ?>">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-1">
                                                <?php if ($conv['other_user_image']): ?>
                                                    <img src="<?= h($conv['other_user_image']) ?>" class="rounded-circle me-2" width="40" height="40">
                                                <?php else: ?>
                                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                        <i class="bi bi-building"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="mb-0"><?= h($conv['other_user_name']) ?></h6>
                                                    <small class="text-muted">
                                                        <?php
                                                            $offerTypes = [
                                                                'freight' => 'Offre de fret',
                                                                'vehicle' => 'Offre de véhicule',
                                                                'general' => 'Discussion générale'
                                                            ];
                                                            echo $offerTypes[$conv['offer_type']] ?? '';
                                                        ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <?php if ($conv['last_message']): ?>
                                                <p class="mb-0 small text-truncate">
                                                    <?= h(substr($conv['last_message'], 0, 50)) ?>...
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-end ms-2">
                                            <small class="text-muted d-block">
                                                <?= timeAgo($conv['last_message_at']) ?>
                                            </small>
                                            <?php if ($conv['unread_count'] > 0): ?>
                                                <span class="badge bg-danger rounded-pill">
                                                    <?= $conv['unread_count'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                <p>Aucune conversation</p>
                                <small>Contactez un transporteur ou chargeur pour démarrer une conversation</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Zone de conversation -->
        <div class="col-lg-8 col-md-7">
            <?php if ($activeConversationId && !empty($messages)): ?>
                <?php
                    // Récupérer les infos de l'autre utilisateur
                    $activeConv = array_values(array_filter($conversations, fn($c) => $c['id'] == $activeConversationId))[0] ?? null;
                ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <?php if ($activeConv['other_user_image']): ?>
                                    <img src="<?= h($activeConv['other_user_image']) ?>" class="rounded-circle me-2" width="40" height="40">
                                <?php else: ?>
                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                        <i class="bi bi-building"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h6 class="mb-0"><?= h($activeConv['other_user_name']) ?></h6>
                                    <small class="text-muted">
                                        <?php
                                            $offerTypes = [
                                                'freight' => '<i class="bi bi-box-seam"></i> Offre de fret',
                                                'vehicle' => '<i class="bi bi-truck"></i> Offre de véhicule',
                                                'general' => 'Discussion générale'
                                            ];
                                            echo $offerTypes[$activeConv['offer_type']] ?? '';
                                        ?>
                                    </small>
                                </div>
                            </div>
                            <div>
                                <a href="/directory.php?user=<?= $activeConv['other_user_id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Voir le profil
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <!-- Messages -->
                        <div class="chat-messages p-3" id="messagesContainer" style="height: 50vh; overflow-y: auto;">
                            <?php foreach ($messages as $msg): ?>
                                <div class="chat-message <?= $msg['sender_id'] == $userId ? 'sent' : 'received' ?>" data-message-id="<?= $msg['id'] ?>">
                                    <div class="chat-bubble">
                                        <div><?= nl2br(h($msg['message'])) ?></div>
                                        <div class="small mt-1 opacity-75">
                                            <?= formatDateTime($msg['created_at'], 'd/m/Y H:i') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Formulaire d'envoi -->
                        <div class="border-top p-3">
                            <form method="POST" action="" id="messageForm">
                                <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                                <input type="hidden" name="conversation_id" value="<?= $activeConversationId ?>">
                                <input type="hidden" name="send_message" value="1">
                                <input type="hidden" id="conversationId" value="<?= $activeConversationId ?>">
                                <input type="hidden" id="currentUserId" value="<?= $userId ?>">

                                <div class="input-group">
                                    <textarea class="form-control" name="message" id="messageInput"
                                              placeholder="Écrivez votre message..." rows="2" required></textarea>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-send"></i> Envoyer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-chat-dots fs-1 text-muted d-block mb-3"></i>
                        <h5>Sélectionnez une conversation</h5>
                        <p class="text-muted">Choisissez une conversation dans la liste pour démarrer</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Scroll vers le bas de la conversation
    const messagesContainer = document.getElementById('messagesContainer');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Auto-submit du formulaire avec Enter (Shift+Enter pour nouvelle ligne)
    $('#messageInput').on('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            $('#messageForm').submit();
        }
    });

    // Soumettre le formulaire via AJAX pour éviter le rechargement
    $('#messageForm').on('submit', function(e) {
        e.preventDefault();

        const message = $('#messageInput').val().trim();
        if (!message) return;

        // Soumettre via AJAX (optionnel - pour l'instant on recharge)
        this.submit();
    });
});
</script>

<?php include 'includes/footer.php'; ?>
