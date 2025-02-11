jQuery(document).ready(function($) {
    const aiAlbie = {
        init: function() {
            this.bindEvents();
            this.initRefreshTimers();
        },
        
        bindEvents: function() {
            // Queue actions
            $('.pause-migration').on('click', this.handlePauseMigration.bind(this));
            $('.cancel-migration').on('click', this.handleCancelMigration.bind(this));
            
            // Stats refresh
            $('.migration-stats').on('click', '.refresh-stats', this.refreshStats.bind(this));
            
            // System status
            $('.system-status').on('click', '.refresh-status', this.refreshSystemStatus.bind(this));
        },
        
        initRefreshTimers: function() {
            // Refresh queue every 30 seconds
            setInterval(this.refreshQueue.bind(this), 30000);
            
            // Refresh stats every 5 minutes
            setInterval(this.refreshStats.bind(this), 300000);
            
            // Refresh system status every minute
            setInterval(this.refreshSystemStatus.bind(this), 60000);
        },
        
        handlePauseMigration: function(e) {
            const migrationId = $(e.currentTarget).data('id');
            
            $.ajax({
                url: aiAlbieAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'aialbie_pause_migration',
                    nonce: aiAlbieAdmin.nonce,
                    migration_id: migrationId
                },
                success: (response) => {
                    if (response.success) {
                        this.refreshQueue();
                    } else {
                        this.showError(response.data.message);
                    }
                },
                error: () => {
                    this.showError(aiAlbieAdmin.strings.error);
                }
            });
        },
        
        handleCancelMigration: function(e) {
            const migrationId = $(e.currentTarget).data('id');
            
            if (!confirm('Are you sure you want to cancel this migration?')) {
                return;
            }
            
            $.ajax({
                url: aiAlbieAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'aialbie_cancel_migration',
                    nonce: aiAlbieAdmin.nonce,
                    migration_id: migrationId
                },
                success: (response) => {
                    if (response.success) {
                        this.refreshQueue();
                    } else {
                        this.showError(response.data.message);
                    }
                },
                error: () => {
                    this.showError(aiAlbieAdmin.strings.error);
                }
            });
        },
        
        refreshQueue: function() {
            $.ajax({
                url: aiAlbieAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'aialbie_get_queue',
                    nonce: aiAlbieAdmin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateQueueUI(response.data);
                    }
                }
            });
        },
        
        refreshStats: function() {
            $.ajax({
                url: aiAlbieAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'aialbie_get_stats',
                    nonce: aiAlbieAdmin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateStatsUI(response.data);
                    }
                }
            });
        },
        
        refreshSystemStatus: function() {
            $.ajax({
                url: aiAlbieAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'aialbie_get_system_status',
                    nonce: aiAlbieAdmin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateSystemStatusUI(response.data);
                    }
                }
            });
        },
        
        updateQueueUI: function(queue) {
            const queueList = $('.queue-list');
            
            if (queue.length === 0) {
                queueList.html('<div class="no-queue"><p>No migrations in queue</p></div>');
                return;
            }
            
            const html = queue.map(item => `
                <div class="queue-item">
                    <div class="item-info">
                        <span class="item-title">${item.title}</span>
                        <span class="item-type">${item.type}</span>
                    </div>
                    <div class="item-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${item.progress}%"></div>
                        </div>
                        <span class="progress-text">${item.progress}%</span>
                    </div>
                    <div class="item-actions">
                        <button class="button pause-migration" data-id="${item.id}">
                            <span class="dashicons dashicons-pause"></span>
                        </button>
                        <button class="button cancel-migration" data-id="${item.id}">
                            <span class="dashicons dashicons-no"></span>
                        </button>
                    </div>
                </div>
            `).join('');
            
            queueList.html(html);
        },
        
        updateStatsUI: function(stats) {
            $('.stat-number').each(function() {
                const key = $(this).data('stat');
                if (stats[key] !== undefined) {
                    $(this).text(stats[key]);
                }
            });
        },
        
        updateSystemStatusUI: function(statuses) {
            const statusGrid = $('.status-grid');
            
            const html = statuses.map(status => `
                <div class="status-item ${status.status}">
                    <span class="status-icon">
                        <span class="dashicons ${status.icon}"></span>
                    </span>
                    <span class="status-label">${status.label}</span>
                    <span class="status-value">${status.value}</span>
                </div>
            `).join('');
            
            statusGrid.html(html);
        },
        
        showError: function(message) {
            const notice = $('<div class="notice notice-error is-dismissible"><p></p></div>');
            notice.find('p').text(message);
            
            $('.wrap.aialbie-admin').prepend(notice);
            
            setTimeout(() => {
                notice.fadeOut(() => notice.remove());
            }, 5000);
        }
    };
    
    // Initialize
    aiAlbie.init();
});
