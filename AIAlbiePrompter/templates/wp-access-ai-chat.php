<?php defined('ABSPATH') || exit; ?>

<div class="wp-access-ai-chat">
    <!-- AI Chat Interface -->
    <div class="ai-chat-interface">
        <div class="chat-header">
            <div class="ai-assistant-info">
                <div class="ai-avatar">
                    <img src="<?php echo esc_url(plugins_url('assets/images/albie-avatar.png', dirname(__FILE__))); ?>" alt="AI Assistant">
                </div>
                <div class="ai-details">
                    <h3>WordPress Access Assistant</h3>
                    <span class="ai-status">Online</span>
                </div>
            </div>
            <div class="chat-actions">
                <button class="clear-chat" title="Clear Chat">
                    <span class="dashicons dashicons-trash"></span>
                </button>
                <button class="toggle-guide" title="Toggle Full Guide">
                    <span class="dashicons dashicons-book-alt"></span>
                </button>
            </div>
        </div>

        <div class="chat-messages" id="chat-messages">
            <!-- Initial Welcome Message -->
            <div class="message ai-message">
                <div class="message-content">
                    <p>👋 Hi! I'm your WordPress Access Assistant. I can help you with:</p>
                    <ul class="quick-options">
                        <li><button class="quick-option" data-query="find login url">Finding your login URL</button></li>
                        <li><button class="quick-option" data-query="reset password">Resetting your password</button></li>
                        <li><button class="quick-option" data-query="recover admin access">Recovering admin access</button></li>
                        <li><button class="quick-option" data-query="security best practices">Security best practices</button></li>
                    </ul>
                    <p>Or just type your question below!</p>
                </div>
            </div>
        </div>

        <div class="chat-input-area">
            <div class="input-wrapper">
                <textarea id="user-input" placeholder="Ask me anything about WordPress access..." rows="1"></textarea>
                <button class="send-message" id="send-message">
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
            </div>
            <div class="input-suggestions">
                <span class="suggestion-label">Try asking:</span>
                <div class="suggestions-scroll">
                    <button class="suggestion">How do I find my wp-admin URL?</button>
                    <button class="suggestion">I forgot my password</button>
                    <button class="suggestion">Can't access admin email</button>
                    <button class="suggestion">How to secure my login?</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Context-Aware Tools -->
    <div class="ai-tools-panel" style="display: none;">
        <div class="tools-header">
            <h4>Helpful Tools</h4>
            <button class="close-tools">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="tools-content">
            <!-- Dynamic tools will be loaded here based on context -->
        </div>
    </div>

    <!-- Loading States -->
    <div class="ai-loading" style="display: none;">
        <div class="loading-indicator">
            <div class="loading-dots">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <p>Analyzing your request...</p>
        </div>
    </div>
</div>

<!-- Templates for Dynamic Content -->
<template id="url-checker-tool">
    <div class="tool-card url-checker">
        <h5>WordPress Login URL Checker</h5>
        <div class="tool-content">
            <input type="text" class="site-url-input" placeholder="Enter your website URL">
            <button class="check-url">Check Common Login URLs</button>
            <div class="results"></div>
        </div>
    </div>
</template>

<template id="password-reset-tool">
    <div class="tool-card password-reset">
        <h5>Password Reset Helper</h5>
        <div class="tool-content">
            <input type="email" class="admin-email-input" placeholder="Enter your admin email">
            <button class="send-reset">Send Reset Instructions</button>
            <div class="alternative-methods"></div>
        </div>
    </div>
</template>

<template id="security-checker-tool">
    <div class="tool-card security-checker">
        <h5>Security Status Check</h5>
        <div class="tool-content">
            <input type="text" class="security-url-input" placeholder="Enter your WordPress site URL">
            <button class="check-security">Run Security Check</button>
            <div class="security-results"></div>
        </div>
    </div>
</template>
