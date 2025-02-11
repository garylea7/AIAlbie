jQuery(document).ready(function($) {
    const domainGuide = {
        init: function() {
            this.bindEvents();
            this.initializeTooltips();
        },

        bindEvents: function() {
            $('#check-domain').on('click', this.handleDomainCheck.bind(this));
            $('.copy-url').on('click', this.handleCopyUrl.bind(this));
            $('.issue-header').on('click', this.toggleIssueContent.bind(this));
            $('.tool-button').on('click', this.handleToolAction.bind(this));
            $('.check-item input').on('change', this.handleCheckItem.bind(this));
        },

        handleDomainCheck: async function(e) {
            e.preventDefault();
            const domain = $('#domain-name').val().trim();
            
            if (!domain) {
                this.showError('Please enter a valid domain name');
                return;
            }

            this.startDomainCheck();

            try {
                const analysis = await this.analyzeDomain(domain);
                this.showDomainStatus(analysis);
            } catch (error) {
                this.showError('Error analyzing domain: ' + error.message);
            }
        },

        analyzeDomain: function(domain) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: aiAlbieAdmin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'aialbie_analyze_domain',
                        nonce: aiAlbieAdmin.nonce,
                        domain: domain
                    },
                    success: (response) => {
                        if (response.success) {
                            resolve(response.data);
                        } else {
                            reject(new Error(response.data.message));
                        }
                    },
                    error: () => {
                        reject(new Error('Network error'));
                    }
                });
            });
        },

        startDomainCheck: function() {
            const button = $('#check-domain');
            button.addClass('checking');
            button.prop('disabled', true);
            button.find('.button-text').text('Checking...');
        },

        showDomainStatus: function(analysis) {
            // Update registrar info
            $('.registrar-name').text(analysis.registrar);
            
            // Update DNS provider
            $('.dns-provider').text(analysis.dns_provider);
            
            // Update expiry date
            $('.expiry-date').text(analysis.expiry_date);
            
            // Update SSL status
            $('.ssl-status').html(this.getSSLStatusHtml(analysis.ssl));
            
            // Show the status panel
            $('#domain-status').slideDown();
            
            // Update DNS records
            this.updateDNSRecords(analysis.dns_records);
            
            // Reset the check button
            const button = $('#check-domain');
            button.removeClass('checking');
            button.prop('disabled', false);
            button.find('.button-text').text('Check Domain');
        },

        getSSLStatusHtml: function(ssl) {
            if (ssl.valid) {
                return `<span class="ssl-valid">
                    <span class="dashicons dashicons-yes-alt"></span>
                    Valid until ${ssl.expiry}
                </span>`;
            } else {
                return `<span class="ssl-invalid">
                    <span class="dashicons dashicons-warning"></span>
                    ${ssl.error}
                </span>`;
            }
        },

        updateDNSRecords: function(records) {
            const tbody = $('.records-table tbody');
            tbody.empty();
            
            records.forEach(record => {
                tbody.append(`
                    <tr>
                        <td>${record.type}</td>
                        <td>${record.name}</td>
                        <td>${record.value}</td>
                        <td>${record.ttl}</td>
                    </tr>
                `);
            });
        },

        handleCopyUrl: function(e) {
            const url = $(e.currentTarget).prev('.url').text();
            navigator.clipboard.writeText(url).then(() => {
                this.showSuccess('URL copied to clipboard');
            }).catch(() => {
                this.showError('Failed to copy URL');
            });
        },

        toggleIssueContent: function(e) {
            const item = $(e.currentTarget).closest('.issue-item');
            item.toggleClass('active');
        },

        handleToolAction: function(e) {
            const tool = $(e.currentTarget).data('tool');
            
            switch (tool) {
                case 'dns-backup':
                    this.backupDNSRecords();
                    break;
                case 'email-backup':
                    this.backupEmailSettings();
                    break;
                case 'check-propagation':
                    this.checkDNSPropagation();
                    break;
                case 'check-ssl':
                    this.checkSSLStatus();
                    break;
                case 'check-email':
                    this.validateEmailConfig();
                    break;
            }
        },

        backupDNSRecords: function() {
            $.ajax({
                url: aiAlbieAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'aialbie_backup_dns',
                    nonce: aiAlbieAdmin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.downloadFile(response.data.file, 'dns_backup.json');
                    } else {
                        this.showError(response.data.message);
                    }
                }
            });
        },

        backupEmailSettings: function() {
            $.ajax({
                url: aiAlbieAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'aialbie_backup_email',
                    nonce: aiAlbieAdmin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.downloadFile(response.data.file, 'email_backup.json');
                    } else {
                        this.showError(response.data.message);
                    }
                }
            });
        },

        checkDNSPropagation: function() {
            const domain = $('#domain-name').val().trim();
            
            if (!domain) {
                this.showError('Please enter a domain first');
                return;
            }
            
            window.open(`https://dnschecker.org/#A/${domain}`, '_blank');
        },

        checkSSLStatus: function() {
            const domain = $('#domain-name').val().trim();
            
            if (!domain) {
                this.showError('Please enter a domain first');
                return;
            }
            
            window.open(`https://www.ssllabs.com/ssltest/analyze.html?d=${domain}`, '_blank');
        },

        validateEmailConfig: function() {
            const domain = $('#domain-name').val().trim();
            
            if (!domain) {
                this.showError('Please enter a domain first');
                return;
            }
            
            $.ajax({
                url: aiAlbieAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'aialbie_validate_email',
                    nonce: aiAlbieAdmin.nonce,
                    domain: domain
                },
                success: (response) => {
                    if (response.success) {
                        this.showEmailValidationResults(response.data);
                    } else {
                        this.showError(response.data.message);
                    }
                }
            });
        },

        handleCheckItem: function(e) {
            const checkbox = $(e.target);
            const item = checkbox.closest('.check-item');
            
            if (checkbox.is(':checked')) {
                item.addClass('completed');
            } else {
                item.removeClass('completed');
            }
            
            this.updateProgress();
        },

        updateProgress: function() {
            const total = $('.check-item').length;
            const completed = $('.check-item.completed').length;
            const progress = (completed / total) * 100;
            
            $('.verification-progress-bar').css('width', progress + '%');
            $('.verification-progress-text').text(Math.round(progress) + '%');
        },

        downloadFile: function(content, filename) {
            const blob = new Blob([JSON.stringify(content, null, 2)], { type: 'application/json' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        },

        showSuccess: function(message) {
            const notice = $('<div class="notice notice-success is-dismissible"><p></p></div>');
            notice.find('p').text(message);
            $('.domain-migration-guide').prepend(notice);
            setTimeout(() => notice.fadeOut(() => notice.remove()), 3000);
        },

        showError: function(message) {
            const notice = $('<div class="notice notice-error is-dismissible"><p></p></div>');
            notice.find('p').text(message);
            $('.domain-migration-guide').prepend(notice);
            setTimeout(() => notice.fadeOut(() => notice.remove()), 5000);
        },

        initializeTooltips: function() {
            $('[data-tooltip]').tooltip();
        }
    };

    // Initialize
    domainGuide.init();
});
