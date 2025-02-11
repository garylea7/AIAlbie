<?php defined('ABSPATH') || exit; ?>

<div class="wrap aialbie-insights">
    <h1>
        <img src="<?php echo esc_url(AIALBIE_ASSETS_URL . 'images/logo.png'); ?>" alt="AI Albie" class="aialbie-logo">
        AI Insights Dashboard
    </h1>

    <div class="insights-dashboard">
        <!-- Performance Overview -->
        <div class="performance-overview">
            <h2>Performance Overview</h2>
            <?php $performance = $insights->analyze_performance(); ?>
            <div class="performance-grid">
                <div class="performance-card">
                    <span class="metric-value"><?php echo esc_html($performance['summary']['success_rate']); ?>%</span>
                    <span class="metric-label">Success Rate</span>
                    <?php if ($performance['summary']['success_rate'] < 90) : ?>
                        <span class="metric-alert warning">
                            <span class="dashicons dashicons-warning"></span>
                            Below Target
                        </span>
                    <?php endif; ?>
                </div>
                <div class="performance-card">
                    <span class="metric-value"><?php echo esc_html($performance['summary']['avg_duration']); ?> min</span>
                    <span class="metric-label">Average Duration</span>
                </div>
                <div class="performance-card">
                    <span class="metric-value"><?php echo esc_html($performance['summary']['total_migrations']); ?></span>
                    <span class="metric-label">Total Migrations</span>
                </div>
                <div class="performance-card">
                    <span class="metric-value"><?php echo esc_html($performance['summary']['failed_migrations']); ?></span>
                    <span class="metric-label">Failed Migrations</span>
                    <?php if ($performance['summary']['failed_migrations'] > 0) : ?>
                        <span class="metric-alert error">
                            <span class="dashicons dashicons-warning"></span>
                            Action Needed
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Migration Trends -->
        <div class="migration-trends">
            <h2>Migration Trends</h2>
            <?php $trends = $insights->analyze_trends(); ?>
            <div class="trends-chart">
                <canvas id="trendsChart"></canvas>
            </div>
            <div class="trends-breakdown">
                <div class="platform-stats">
                    <h3>Popular Platforms</h3>
                    <div class="platform-list">
                        <?php foreach ($trends['platforms'] as $platform) : ?>
                            <div class="platform-item">
                                <span class="platform-name"><?php echo esc_html($platform->source_platform); ?></span>
                                <span class="platform-count"><?php echo esc_html($platform->count); ?></span>
                                <div class="platform-bar">
                                    <div class="bar-fill" style="width: <?php echo esc_attr(($platform->count / max(array_column($trends['platforms'], 'count'))) * 100); ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="issues-summary">
                    <h3>Common Issues</h3>
                    <div class="issues-list">
                        <?php foreach ($trends['common_issues'] as $issue) : ?>
                            <div class="issue-item">
                                <span class="issue-type"><?php echo esc_html($issue->error_type); ?></span>
                                <span class="issue-count"><?php echo esc_html($issue->count); ?> occurrences</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Recommendations -->
        <div class="ai-recommendations">
            <h2>AI Recommendations</h2>
            <?php $recommendations = $insights->get_recommendations(); ?>
            <div class="recommendations-list">
                <?php foreach ($recommendations as $rec) : ?>
                    <div class="recommendation-card <?php echo esc_attr($rec['priority']); ?>">
                        <div class="rec-header">
                            <span class="rec-type"><?php echo esc_html(ucfirst($rec['type'])); ?></span>
                            <span class="rec-priority"><?php echo esc_html(ucfirst($rec['priority'])); ?></span>
                        </div>
                        <h3><?php echo esc_html($rec['title']); ?></h3>
                        <p><?php echo esc_html($rec['description']); ?></p>
                        <div class="rec-actions">
                            <h4>Recommended Actions:</h4>
                            <ul>
                                <?php foreach ($rec['actions'] as $action) : ?>
                                    <li><?php echo esc_html($action); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Optimization Opportunities -->
        <div class="optimization-opportunities">
            <h2>Optimization Opportunities</h2>
            <?php $opportunities = $insights->find_optimization_opportunities(); ?>
            <div class="opportunities-list">
                <?php foreach ($opportunities as $opp) : ?>
                    <div class="opportunity-card">
                        <div class="opp-header">
                            <span class="opp-type"><?php echo esc_html(ucfirst($opp['type'])); ?></span>
                            <span class="opp-impact <?php echo esc_attr($opp['potential_impact']); ?>">
                                <?php echo esc_html(ucfirst($opp['potential_impact'])); ?> Impact
                            </span>
                        </div>
                        <h3><?php echo esc_html($opp['title']); ?></h3>
                        <p><?php echo esc_html($opp['description']); ?></p>
                        <div class="opp-suggestions">
                            <h4>Suggestions:</h4>
                            <ul>
                                <?php foreach ($opp['suggestions'] as $suggestion) : ?>
                                    <li><?php echo esc_html($suggestion); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Security Insights -->
        <div class="security-insights">
            <h2>Security Insights</h2>
            <?php $security = $insights->analyze_security(); ?>
            <div class="security-overview">
                <?php if (!empty($security['vulnerabilities'])) : ?>
                    <div class="vulnerabilities-section">
                        <h3>Critical Issues</h3>
                        <div class="vulnerabilities-list">
                            <?php foreach ($security['vulnerabilities'] as $vuln) : ?>
                                <div class="vulnerability-card critical">
                                    <h4><?php echo esc_html($vuln['title']); ?></h4>
                                    <p><?php echo esc_html($vuln['description']); ?></p>
                                    <div class="remediation-steps">
                                        <h5>Remediation Steps:</h5>
                                        <ol>
                                            <?php foreach ($vuln['remediation_steps'] as $step) : ?>
                                                <li><?php echo esc_html($step); ?></li>
                                            <?php endforeach; ?>
                                        </ol>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($security['warnings'])) : ?>
                    <div class="warnings-section">
                        <h3>Security Warnings</h3>
                        <div class="warnings-list">
                            <?php foreach ($security['warnings'] as $warning) : ?>
                                <div class="warning-card">
                                    <h4><?php echo esc_html($warning['title']); ?></h4>
                                    <p><?php echo esc_html($warning['description']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Resource Usage -->
        <div class="resource-usage">
            <h2>Resource Usage</h2>
            <?php $resources = $insights->analyze_resources(); ?>
            <div class="resource-grid">
                <div class="resource-card">
                    <h3>Disk Usage</h3>
                    <div class="resource-meter">
                        <div class="meter-fill" style="width: <?php echo esc_attr($resources['disk_usage']); ?>%"></div>
                    </div>
                    <div class="resource-details">
                        <span class="usage-percent"><?php echo esc_html($resources['disk_usage']); ?>%</span>
                        <span class="usage-label">used of <?php echo esc_html(size_format($resources['disk_total'])); ?></span>
                    </div>
                </div>
                <div class="resource-card">
                    <h3>Memory Usage</h3>
                    <div class="resource-meter">
                        <div class="meter-fill" style="width: <?php echo esc_attr(($resources['memory_usage'] / $resources['memory_limit']) * 100); ?>%"></div>
                    </div>
                    <div class="resource-details">
                        <span class="usage-current"><?php echo esc_html(size_format($resources['memory_usage'])); ?></span>
                        <span class="usage-peak">Peak: <?php echo esc_html(size_format($resources['memory_peak'])); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    // Trends chart
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($trends['daily'], 'date')); ?>,
            datasets: [{
                label: 'Total Migrations',
                data: <?php echo json_encode(array_column($trends['daily'], 'migrations')); ?>,
                borderColor: '#2271b1',
                tension: 0.4
            }, {
                label: 'Successful Migrations',
                data: <?php echo json_encode(array_column($trends['daily'], 'successful')); ?>,
                borderColor: '#00844a',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
