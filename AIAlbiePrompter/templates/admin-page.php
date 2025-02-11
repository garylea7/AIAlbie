<?php defined('ABSPATH') || exit; ?>

<div class="wrap aialbie-admin">
    <h1>
        <img src="<?php echo esc_url(AIALBIE_ASSETS_URL . 'images/logo.png'); ?>" alt="AI Albie" class="aialbie-logo">
        AI Albie Prompter
    </h1>

    <div class="aialbie-dashboard">
        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="<?php echo esc_url(admin_url('admin.php?page=ai-albie-wizard')); ?>" class="button button-primary">
                    <span class="dashicons dashicons-migrate"></span>
                    Start Migration Wizard
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=ai-albie-bulk')); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-database-import"></span>
                    Bulk Migration
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=ai-albie-settings')); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-admin-settings"></span>
                    Settings
                </a>
            </div>
        </div>

        <!-- Migration Stats -->
        <div class="migration-stats">
            <h2>Migration Statistics</h2>
            <div class="stat-grid">
                <div class="stat-card">
                    <span class="stat-number"><?php echo esc_html($this->get_total_migrations()); ?></span>
                    <span class="stat-label">Total Migrations</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo esc_html($this->get_active_migrations()); ?></span>
                    <span class="stat-label">Active Migrations</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo esc_html($this->get_completed_migrations()); ?></span>
                    <span class="stat-label">Completed</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo esc_html($this->get_success_rate()); ?>%</span>
                    <span class="stat-label">Success Rate</span>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity">
            <h2>Recent Activity</h2>
            <div class="activity-list">
                <?php $activities = $this->get_recent_activities(5); ?>
                <?php if (!empty($activities)) : ?>
                    <?php foreach ($activities as $activity) : ?>
                        <div class="activity-item">
                            <span class="activity-icon <?php echo esc_attr($activity['type']); ?>">
                                <span class="dashicons <?php echo esc_attr($activity['icon']); ?>"></span>
                            </span>
                            <div class="activity-content">
                                <span class="activity-title"><?php echo esc_html($activity['title']); ?></span>
                                <span class="activity-time"><?php echo esc_html($activity['time']); ?></span>
                            </div>
                            <span class="activity-status <?php echo esc_attr($activity['status']); ?>">
                                <?php echo esc_html($activity['status_text']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="no-activity">
                        <p>No recent activity</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- System Status -->
        <div class="system-status">
            <h2>System Status</h2>
            <div class="status-grid">
                <?php $statuses = $this->get_system_status(); ?>
                <?php foreach ($statuses as $status) : ?>
                    <div class="status-item <?php echo esc_attr($status['status']); ?>">
                        <span class="status-icon">
                            <span class="dashicons <?php echo esc_attr($status['icon']); ?>"></span>
                        </span>
                        <span class="status-label"><?php echo esc_html($status['label']); ?></span>
                        <span class="status-value"><?php echo esc_html($status['value']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Migration Queue -->
        <div class="migration-queue">
            <h2>Migration Queue</h2>
            <div class="queue-list">
                <?php $queue = $this->get_migration_queue(); ?>
                <?php if (!empty($queue)) : ?>
                    <?php foreach ($queue as $item) : ?>
                        <div class="queue-item">
                            <div class="item-info">
                                <span class="item-title"><?php echo esc_html($item['title']); ?></span>
                                <span class="item-type"><?php echo esc_html($item['type']); ?></span>
                            </div>
                            <div class="item-progress">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo esc_attr($item['progress']); ?>%"></div>
                                </div>
                                <span class="progress-text"><?php echo esc_html($item['progress']); ?>%</span>
                            </div>
                            <div class="item-actions">
                                <button class="button pause-migration" data-id="<?php echo esc_attr($item['id']); ?>">
                                    <span class="dashicons dashicons-pause"></span>
                                </button>
                                <button class="button cancel-migration" data-id="<?php echo esc_attr($item['id']); ?>">
                                    <span class="dashicons dashicons-no"></span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="no-queue">
                        <p>No migrations in queue</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Help & Support -->
        <div class="help-support">
            <h2>Help & Support</h2>
            <div class="support-links">
                <a href="https://aialbie.com/docs" target="_blank" class="support-link">
                    <span class="dashicons dashicons-book"></span>
                    Documentation
                </a>
                <a href="https://aialbie.com/support" target="_blank" class="support-link">
                    <span class="dashicons dashicons-sos"></span>
                    Get Support
                </a>
                <a href="https://aialbie.com/tutorials" target="_blank" class="support-link">
                    <span class="dashicons dashicons-video-alt3"></span>
                    Video Tutorials
                </a>
            </div>
        </div>
    </div>
</div>
