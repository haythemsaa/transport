<?php
/**
 * Mailer Class - Email Sending System
 *
 * Simple SMTP email sender without external dependencies
 * Supports HTML and plain text emails
 */

class Mailer {
    private static $instance = null;
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $fromAddress;
    private $fromName;
    private $replyTo;

    /**
     * Private constructor for Singleton
     */
    private function __construct() {
        // Load config from environment or config.php
        $this->smtpHost = getenv('MAIL_HOST') ?: 'localhost';
        $this->smtpPort = getenv('MAIL_PORT') ?: 25;
        $this->smtpUsername = getenv('MAIL_USERNAME') ?: '';
        $this->smtpPassword = getenv('MAIL_PASSWORD') ?: '';
        $this->fromAddress = getenv('MAIL_FROM_ADDRESS') ?: 'noreply@teleroute-marketplace.com';
        $this->fromName = getenv('MAIL_FROM_NAME') ?: 'Teleroute Marketplace';
        $this->replyTo = getenv('MAIL_REPLY_TO') ?: $this->fromAddress;
    }

    /**
     * Get Mailer instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Send an email
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (HTML or plain text)
     * @param array $options Additional options (cc, bcc, attachments, etc.)
     * @return bool Success status
     */
    public function send($to, $subject, $body, $options = []) {
        try {
            // Validate email
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email address: $to");
            }

            // Prepare headers
            $headers = [];
            $headers[] = "From: {$this->fromName} <{$this->fromAddress}>";
            $headers[] = "Reply-To: {$this->replyTo}";
            $headers[] = "X-Mailer: PHP/" . phpversion();
            $headers[] = "MIME-Version: 1.0";

            // Check if body contains HTML
            $isHtml = (stripos($body, '<html>') !== false || stripos($body, '<!DOCTYPE') !== false || stripos($body, '<body>') !== false);

            if ($isHtml) {
                $headers[] = "Content-Type: text/html; charset=UTF-8";
            } else {
                $headers[] = "Content-Type: text/plain; charset=UTF-8";
            }

            // Add CC if provided
            if (isset($options['cc'])) {
                $headers[] = "Cc: {$options['cc']}";
            }

            // Add BCC if provided
            if (isset($options['bcc'])) {
                $headers[] = "Bcc: {$options['bcc']}";
            }

            // Send email using PHP mail() function
            // Note: For production, consider using PHPMailer or SwiftMailer for better SMTP support
            $sent = mail($to, $subject, $body, implode("\r\n", $headers));

            if ($sent) {
                // Log successful email
                if (class_exists('Logger')) {
                    Logger::getInstance()->info('Email sent', [
                        'to' => $to,
                        'subject' => $subject
                    ], 'email');
                }
                return true;
            } else {
                throw new Exception("Failed to send email");
            }

        } catch (Exception $e) {
            // Log error
            if (class_exists('Logger')) {
                Logger::getInstance()->error('Email sending failed', [
                    'to' => $to,
                    'subject' => $subject,
                    'error' => $e->getMessage()
                ], 'email');
            }
            error_log("Mailer error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send welcome email
     */
    public function sendWelcomeEmail($to, $name, $userType) {
        $subject = "Bienvenue sur Teleroute Marketplace !";

        $body = $this->getTemplate('welcome', [
            'name' => $name,
            'user_type' => $userType,
            'login_url' => APP_URL . '/login.php',
            'dashboard_url' => APP_URL . '/dashboard.php'
        ]);

        return $this->send($to, $subject, $body);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($to, $name, $resetLink) {
        $subject = "Réinitialisation de votre mot de passe";

        $body = $this->getTemplate('password-reset', [
            'name' => $name,
            'reset_link' => $resetLink,
            'expiry_hours' => 24
        ]);

        return $this->send($to, $subject, $body);
    }

    /**
     * Send new message notification
     */
    public function sendMessageNotification($to, $name, $senderName, $messagePreview) {
        $subject = "Nouveau message de $senderName";

        $body = $this->getTemplate('new-message', [
            'name' => $name,
            'sender_name' => $senderName,
            'message_preview' => substr($messagePreview, 0, 100),
            'messages_url' => APP_URL . '/messages.php'
        ]);

        return $this->send($to, $subject, $body);
    }

    /**
     * Send alert notification (for saved searches)
     */
    public function sendAlertNotification($to, $name, $alertName, $matchesCount) {
        $subject = "Nouvelles offres correspondant à votre alerte: $alertName";

        $body = $this->getTemplate('alert', [
            'name' => $name,
            'alert_name' => $alertName,
            'matches_count' => $matchesCount,
            'search_url' => APP_URL . '/search-freight.php'
        ]);

        return $this->send($to, $subject, $body);
    }

    /**
     * Send transaction confirmation
     */
    public function sendTransactionConfirmation($to, $name, $transactionId, $amount) {
        $subject = "Confirmation de transaction #$transactionId";

        $body = $this->getTemplate('transaction', [
            'name' => $name,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'transactions_url' => APP_URL . '/transactions.php'
        ]);

        return $this->send($to, $subject, $body);
    }

    /**
     * Get email template
     *
     * @param string $template Template name
     * @param array $vars Variables to replace in template
     * @return string Rendered template
     */
    private function getTemplate($template, $vars = []) {
        // Simple template system
        // In production, consider using a proper template engine

        $templates = [
            'welcome' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bienvenue</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0d6efd; color: white; padding: 20px; text-align: center; }
        .content { padding: 30px; background: #f8f9fa; }
        .button { display: inline-block; padding: 12px 24px; background: #0d6efd; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚚 Teleroute Marketplace</h1>
        </div>
        <div class="content">
            <h2>Bienvenue {{name}} !</h2>
            <p>Merci de vous être inscrit en tant que <strong>{{user_type}}</strong> sur Teleroute Marketplace.</p>
            <p>Vous pouvez dès maintenant accéder à toutes les fonctionnalités de la plateforme.</p>
            <p style="text-align: center;">
                <a href="{{dashboard_url}}" class="button">Accéder au tableau de bord</a>
            </p>
            <p>Si vous avez des questions, n\'hésitez pas à nous contacter.</p>
        </div>
        <div class="footer">
            <p>&copy; 2024 Teleroute Marketplace. Tous droits réservés.</p>
            <p><a href="'.APP_URL.'/settings.php">Gérer les notifications</a></p>
        </div>
    </div>
</body>
</html>',

            'password-reset' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation de mot de passe</title>
</head>
<body>
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
        <h2>Réinitialisation de votre mot de passe</h2>
        <p>Bonjour {{name}},</p>
        <p>Vous avez demandé la réinitialisation de votre mot de passe sur Teleroute Marketplace.</p>
        <p>Cliquez sur le lien ci-dessous pour créer un nouveau mot de passe :</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{reset_link}}" style="display: inline-block; padding: 12px 24px; background: #0d6efd; color: white; text-decoration: none; border-radius: 4px;">
                Réinitialiser mon mot de passe
            </a>
        </p>
        <p><strong>Ce lien est valable {{expiry_hours}} heures.</strong></p>
        <p>Si vous n\'avez pas demandé cette réinitialisation, ignorez cet email.</p>
        <p>Cordialement,<br>L\'équipe Teleroute Marketplace</p>
    </div>
</body>
</html>',

            'new-message' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
        <h2>Nouveau message reçu</h2>
        <p>Bonjour {{name}},</p>
        <p>Vous avez reçu un nouveau message de <strong>{{sender_name}}</strong>.</p>
        <div style="background: #f8f9fa; padding: 15px; border-left: 4px solid #0d6efd; margin: 20px 0;">
            <p style="margin: 0;">{{message_preview}}...</p>
        </div>
        <p style="text-align: center;">
            <a href="{{messages_url}}" style="display: inline-block; padding: 12px 24px; background: #0d6efd; color: white; text-decoration: none; border-radius: 4px;">
                Voir mes messages
            </a>
        </p>
    </div>
</body>
</html>',

            'alert' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
        <h2>🔔 Alerte : Nouvelles offres disponibles</h2>
        <p>Bonjour {{name}},</p>
        <p>Nous avons trouvé <strong>{{matches_count}} nouvelle(s) offre(s)</strong> correspondant à votre alerte :</p>
        <p><strong>{{alert_name}}</strong></p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{search_url}}" style="display: inline-block; padding: 12px 24px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;">
                Voir les offres
            </a>
        </p>
        <p><small>Pour gérer vos alertes, rendez-vous dans <a href="'.APP_URL.'/alerts.php">Mes alertes</a></small></p>
    </div>
</body>
</html>',

            'transaction' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
        <h2>Confirmation de transaction</h2>
        <p>Bonjour {{name}},</p>
        <p>Votre transaction a été confirmée avec succès.</p>
        <div style="background: #f8f9fa; padding: 20px; border-radius: 4px; margin: 20px 0;">
            <p><strong>ID de transaction :</strong> #{{transaction_id}}</p>
            <p><strong>Montant :</strong> {{amount}}</p>
        </div>
        <p style="text-align: center;">
            <a href="{{transactions_url}}" style="display: inline-block; padding: 12px 24px; background: #0d6efd; color: white; text-decoration: none; border-radius: 4px;">
                Voir mes transactions
            </a>
        </p>
    </div>
</body>
</html>'
        ];

        $template = $templates[$template] ?? '<p>{{message}}</p>';

        // Replace variables
        foreach ($vars as $key => $value) {
            $template = str_replace('{{' . $key . '}}', htmlspecialchars($value), $template);
        }

        return $template;
    }

    /**
     * Test SMTP connection
     */
    public function testConnection() {
        // Simple connection test
        try {
            $socket = fsockopen($this->smtpHost, $this->smtpPort, $errno, $errstr, 10);
            if ($socket) {
                fclose($socket);
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}

// Helper function
function mailer() {
    return Mailer::getInstance();
}
