/**
 * Teleroute Marketplace - Main JavaScript
 */

(function() {
    'use strict';

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeApp();
    });

    /**
     * Initialize application
     */
    function initializeApp() {
        // Auto-hide alerts after 5 seconds
        autoHideAlerts();

        // Initialize tooltips
        initializeTooltips();

        // Initialize form validations
        initializeFormValidations();

        // Initialize search filters
        initializeSearchFilters();

        // Initialize auto-refresh for messages
        initializeMessageRefresh();
    }

    /**
     * Auto-hide alerts
     */
    function autoHideAlerts() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    }

    /**
     * Initialize Bootstrap tooltips
     */
    function initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    /**
     * Initialize form validations
     */
    function initializeFormValidations() {
        const forms = document.querySelectorAll('.needs-validation');

        Array.from(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                form.classList.add('was-validated');
            }, false);
        });
    }

    /**
     * Initialize search filters
     */
    function initializeSearchFilters() {
        const filterForm = document.getElementById('filterForm');
        if (filterForm) {
            const inputs = filterForm.querySelectorAll('input, select');

            inputs.forEach(function(input) {
                input.addEventListener('change', function() {
                    // Auto-submit form on filter change (optional)
                    // filterForm.submit();
                });
            });
        }
    }

    /**
     * Initialize message auto-refresh
     */
    function initializeMessageRefresh() {
        const messagesContainer = document.getElementById('messagesContainer');
        if (messagesContainer) {
            // Refresh messages every 10 seconds
            setInterval(function() {
                refreshMessages();
            }, 10000);
        }
    }

    /**
     * Refresh messages via AJAX
     */
    function refreshMessages() {
        const conversationId = document.getElementById('conversationId')?.value;
        const lastMessageId = getLastMessageId();

        if (!conversationId) return;

        fetch(`/api/messages.php?conversation_id=${conversationId}&last_message_id=${lastMessageId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.messages.length > 0) {
                    appendNewMessages(data.messages);
                }
            })
            .catch(error => console.error('Error refreshing messages:', error));
    }

    /**
     * Get last message ID
     */
    function getLastMessageId() {
        const messages = document.querySelectorAll('.chat-message');
        if (messages.length > 0) {
            return messages[messages.length - 1].dataset.messageId || 0;
        }
        return 0;
    }

    /**
     * Append new messages to chat
     */
    function appendNewMessages(messages) {
        const container = document.getElementById('messagesContainer');
        if (!container) return;

        messages.forEach(function(message) {
            const messageHtml = createMessageHtml(message);
            container.insertAdjacentHTML('beforeend', messageHtml);
        });

        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    }

    /**
     * Create message HTML
     */
    function createMessageHtml(message) {
        const currentUserId = document.getElementById('currentUserId')?.value;
        const isSent = message.sender_id == currentUserId;

        return `
            <div class="chat-message ${isSent ? 'sent' : 'received'}" data-message-id="${message.id}">
                <div class="chat-bubble">
                    ${escapeHtml(message.message)}
                    <div class="small mt-1 opacity-75">
                        ${formatTime(message.created_at)}
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Escape HTML
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Format time
     */
    function formatTime(datetime) {
        const date = new Date(datetime);
        return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    }

    /**
     * Show loading spinner
     */
    window.showLoading = function() {
        const spinner = document.createElement('div');
        spinner.className = 'spinner-overlay';
        spinner.id = 'loadingSpinner';
        spinner.innerHTML = `
            <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Chargement...</span>
            </div>
        `;
        document.body.appendChild(spinner);
    };

    /**
     * Hide loading spinner
     */
    window.hideLoading = function() {
        const spinner = document.getElementById('loadingSpinner');
        if (spinner) {
            spinner.remove();
        }
    };

    /**
     * Confirm action
     */
    window.confirmAction = function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    };

    /**
     * Format currency
     */
    window.formatCurrency = function(amount) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount);
    };

    /**
     * Debounce function
     */
    window.debounce = function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    /**
     * Copy to clipboard
     */
    window.copyToClipboard = function(text) {
        navigator.clipboard.writeText(text).then(function() {
            alert('Copié dans le presse-papier !');
        }, function(err) {
            console.error('Erreur lors de la copie:', err);
        });
    };

})();
