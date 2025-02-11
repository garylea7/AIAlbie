jQuery(document).ready(function($) {
    const wpAccessAI = {
        init: function() {
            this.chatMessages = $('#chat-messages');
            this.userInput = $('#user-input');
            this.sendButton = $('#send-message');
            this.toolsPanel = $('.ai-tools-panel');
            this.loadingIndicator = $('.ai-loading');
            
            this.bindEvents();
            this.initAutoResize();
        },

        bindEvents: function() {
            this.sendButton.on('click', () => this.handleUserMessage());
            this.userInput.on('keypress', (e) => {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    this.handleUserMessage();
                }
            });

            $('.quick-option').on('click', (e) => {
                const query = $(e.currentTarget).data('query');
                this.userInput.val(query);
                this.handleUserMessage();
            });

            $('.suggestion').on('click', (e) => {
                const query = $(e.currentTarget).text();
                this.userInput.val(query);
                this.handleUserMessage();
            });

            $('.clear-chat').on('click', () => this.clearChat());
            $('.toggle-guide').on('click', () => this.toggleFullGuide());
        },

        initAutoResize: function() {
            this.userInput.on('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        },

        handleUserMessage: async function() {
            const message = this.userInput.val().trim();
            if (!message) return;

            // Add user message to chat
            this.addMessage(message, 'user');
            this.userInput.val('').trigger('input');

            // Show loading state
            this.showLoading();

            try {
                // Process the message and get AI response
                const response = await this.processUserMessage(message);
                this.hideLoading();
                
                // Add AI response to chat
                this.addMessage(response.message, 'ai');

                // Show relevant tools if any
                if (response.tools) {
                    this.showTools(response.tools);
                }
            } catch (error) {
                this.hideLoading();
                this.addMessage('Sorry, I encountered an error. Please try again.', 'ai', true);
            }
        },

        processUserMessage: async function(message) {
            // Send to WordPress backend
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: aiAlbieAdmin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'aialbie_process_access_query',
                        nonce: aiAlbieAdmin.nonce,
                        message: message
                    },
                    success: (response) => {
                        if (response.success) {
                            resolve(response.data);
                        } else {
                            reject(new Error(response.data.message));
                        }
                    },
                    error: reject
                });
            });
        },

        addMessage: function(content, type, isError = false) {
            const messageHtml = `
                <div class="message ${type}-message ${isError ? 'error' : ''}">
                    <div class="message-content">
                        ${this.formatMessageContent(content)}
                    </div>
                </div>
            `;
            
            this.chatMessages.append(messageHtml);
            this.scrollToBottom();
        },

        formatMessageContent: function(content) {
            // Convert URLs to links
            content = content.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank">$1</a>');
            
            // Convert code blocks
            content = content.replace(/`([^`]+)`/g, '<code>$1</code>');
            
            // Convert lists
            if (content.includes('\n- ')) {
                content = content.split('\n').map(line => {
                    if (line.startsWith('- ')) {
                        return '<li>' + line.substring(2) + '</li>';
                    }
                    return line;
                }).join('\n');
                content = '<ul>' + content + '</ul>';
            }

            return content;
        },

        showTools: function(tools) {
            const toolsContent = $('.tools-content');
            toolsContent.empty();

            tools.forEach(tool => {
                const template = $(`#${tool}-tool`).html();
                if (template) {
                    toolsContent.append(template);
                }
            });

            this.toolsPanel.slideDown();
            this.initializeTools();
        },

        initializeTools: function() {
            // URL Checker Tool
            $('.check-url').on('click', async () => {
                const url = $('.site-url-input').val().trim();
                if (!url) return;

                const results = await this.checkLoginUrls(url);
                this.displayUrlResults(results);
            });

            // Password Reset Tool
            $('.send-reset').on('click', async () => {
                const email = $('.admin-email-input').val().trim();
                if (!email) return;

                const result = await this.sendPasswordReset(email);
                this.displayResetResult(result);
            });

            // Security Checker Tool
            $('.check-security').on('click', async () => {
                const url = $('.security-url-input').val().trim();
                if (!url) return;

                const results = await this.checkSecurity(url);
                this.displaySecurityResults(results);
            });
        },

        checkLoginUrls: async function(url) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: aiAlbieAdmin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'aialbie_check_login_urls',
                        nonce: aiAlbieAdmin.nonce,
                        site_url: url
                    },
                    success: (response) => {
                        if (response.success) {
                            resolve(response.data);
                        } else {
                            reject(new Error(response.data.message));
                        }
                    },
                    error: reject
                });
            });
        },

        sendPasswordReset: async function(email) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: aiAlbieAdmin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'aialbie_send_password_reset',
                        nonce: aiAlbieAdmin.nonce,
                        email: email
                    },
                    success: (response) => {
                        if (response.success) {
                            resolve(response.data);
                        } else {
                            reject(new Error(response.data.message));
                        }
                    },
                    error: reject
                });
            });
        },

        checkSecurity: async function(url) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: aiAlbieAdmin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'aialbie_check_security',
                        nonce: aiAlbieAdmin.nonce,
                        site_url: url
                    },
                    success: (response) => {
                        if (response.success) {
                            resolve(response.data);
                        } else {
                            reject(new Error(response.data.message));
                        }
                    },
                    error: reject
                });
            });
        },

        displayUrlResults: function(results) {
            const resultsDiv = $('.url-checker .results');
            let html = '<ul class="url-results">';
            
            results.forEach(result => {
                html += `
                    <li class="url-result ${result.status}">
                        <span class="url">${result.url}</span>
                        <span class="status-icon"></span>
                        ${result.accessible ? '<a href="' + result.url + '" target="_blank">Open</a>' : ''}
                    </li>
                `;
            });

            html += '</ul>';
            resultsDiv.html(html);
        },

        displayResetResult: function(result) {
            const resultsDiv = $('.password-reset .results');
            resultsDiv.html(`
                <div class="reset-result ${result.success ? 'success' : 'error'}">
                    <p>${result.message}</p>
                    ${result.alternatives ? '<div class="alternatives">' + result.alternatives + '</div>' : ''}
                </div>
            `);
        },

        displaySecurityResults: function(results) {
            const resultsDiv = $('.security-checker .security-results');
            let html = '<div class="security-summary">';
            
            results.forEach(result => {
                html += `
                    <div class="security-item ${result.status}">
                        <span class="item-name">${result.name}</span>
                        <span class="item-status">${result.message}</span>
                    </div>
                `;
            });

            html += '</div>';
            resultsDiv.html(html);
        },

        showLoading: function() {
            this.loadingIndicator.fadeIn();
        },

        hideLoading: function() {
            this.loadingIndicator.fadeOut();
        },

        scrollToBottom: function() {
            this.chatMessages.scrollTop(this.chatMessages[0].scrollHeight);
        },

        clearChat: function() {
            // Keep the welcome message, remove the rest
            const welcomeMessage = this.chatMessages.find('.message').first();
            this.chatMessages.empty().append(welcomeMessage);
            this.toolsPanel.slideUp();
        },

        toggleFullGuide: function() {
            // Toggle visibility of the full WordPress access guide
            $('.wp-access-guide').slideToggle();
        }
    };

    // Initialize
    wpAccessAI.init();
});
