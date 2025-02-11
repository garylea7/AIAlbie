<?php defined('ABSPATH') || exit; ?>

<div class="domain-migration-guide">
    <div class="guide-header">
        <h2>Domain Migration Guide</h2>
        <p class="guide-subtitle">Safely transfer your domain to your new WordPress site</p>
    </div>

    <!-- Domain Analysis -->
    <div class="domain-analyzer">
        <h3>Domain Health Check</h3>
        <div class="domain-input">
            <input type="text" id="domain-name" placeholder="Enter your domain (e.g., historicaviationmilitary.com)" class="domain-field">
            <button id="check-domain" class="check-button">
                <span class="button-text">Check Domain</span>
                <span class="spinner"></span>
            </button>
        </div>

        <!-- Domain Status -->
        <div id="domain-status" class="status-panel" style="display: none;">
            <div class="status-grid">
                <div class="status-card registrar">
                    <span class="card-icon dashicons dashicons-admin-site"></span>
                    <h4>Current Registrar</h4>
                    <p class="registrar-name">Loading...</p>
                </div>
                <div class="status-card dns">
                    <span class="card-icon dashicons dashicons-networking"></span>
                    <h4>DNS Provider</h4>
                    <p class="dns-provider">Loading...</p>
                </div>
                <div class="status-card expiry">
                    <span class="card-icon dashicons dashicons-calendar-alt"></span>
                    <h4>Expiration Date</h4>
                    <p class="expiry-date">Loading...</p>
                </div>
                <div class="status-card ssl">
                    <span class="card-icon dashicons dashicons-shield"></span>
                    <h4>SSL Status</h4>
                    <p class="ssl-status">Loading...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Migration Steps -->
    <div class="migration-steps">
        <h3>Domain Migration Steps</h3>
        
        <!-- Step 1: Preparation -->
        <div class="step-card">
            <div class="step-header">
                <span class="step-number">1</span>
                <h4>Preparation</h4>
            </div>
            <div class="step-content">
                <ul class="checklist">
                    <li>Verify domain ownership</li>
                    <li>Check domain lock status</li>
                    <li>Obtain EPP/Authorization code</li>
                    <li>Back up DNS records</li>
                    <li>Document email settings</li>
                </ul>
                <div class="step-tools">
                    <button class="tool-button dns-backup">Backup DNS Records</button>
                    <button class="tool-button email-backup">Export Email Settings</button>
                </div>
            </div>
        </div>

        <!-- Step 2: DNS Planning -->
        <div class="step-card">
            <div class="step-header">
                <span class="step-number">2</span>
                <h4>DNS Planning</h4>
            </div>
            <div class="step-content">
                <div class="dns-records">
                    <h5>Current DNS Records</h5>
                    <table class="records-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Name</th>
                                <th>Value</th>
                                <th>TTL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
                <div class="new-records">
                    <h5>Required WordPress DNS Records</h5>
                    <div class="records-list">
                        <!-- Populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Temporary Setup -->
        <div class="step-card">
            <div class="step-header">
                <span class="step-number">3</span>
                <h4>Temporary Domain Setup</h4>
            </div>
            <div class="step-content">
                <p>Set up your new WordPress site on a temporary URL while keeping your current site live.</p>
                <div class="temp-domain">
                    <h5>Your Temporary Domain</h5>
                    <div class="temp-url-display">
                        <span class="url">staging.yoursite.com</span>
                        <button class="copy-url">Copy</button>
                    </div>
                </div>
                <div class="access-credentials">
                    <h5>Staging Access</h5>
                    <div class="credentials-grid">
                        <div class="credential-item">
                            <span class="label">URL:</span>
                            <span class="value">https://staging.yoursite.com</span>
                        </div>
                        <div class="credential-item">
                            <span class="label">Admin URL:</span>
                            <span class="value">/wp-admin</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: DNS Migration -->
        <div class="step-card">
            <div class="step-header">
                <span class="step-number">4</span>
                <h4>DNS Migration</h4>
            </div>
            <div class="step-content">
                <div class="migration-timing">
                    <h5>Recommended Migration Window</h5>
                    <div class="timing-display">
                        <span class="timing-icon dashicons dashicons-clock"></span>
                        <span class="timing-text">Best time to migrate: <strong>Saturday, 02:00 AM</strong></span>
                    </div>
                    <p class="timing-note">Based on your website's traffic patterns</p>
                </div>
                <div class="dns-instructions">
                    <h5>Step-by-Step DNS Update</h5>
                    <ol class="instruction-list">
                        <li>Lower DNS TTL values 24-48 hours before migration</li>
                        <li>Back up all existing DNS records</li>
                        <li>Update nameservers or DNS records</li>
                        <li>Verify DNS propagation</li>
                        <li>Test website functionality</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Step 5: Verification -->
        <div class="step-card">
            <div class="step-header">
                <span class="step-number">5</span>
                <h4>Post-Migration Verification</h4>
            </div>
            <div class="step-content">
                <div class="verification-checklist">
                    <h5>Essential Checks</h5>
                    <div class="check-items">
                        <div class="check-item">
                            <input type="checkbox" id="check-ssl">
                            <label for="check-ssl">SSL Certificate Active</label>
                        </div>
                        <div class="check-item">
                            <input type="checkbox" id="check-email">
                            <label for="check-email">Email Services Working</label>
                        </div>
                        <div class="check-item">
                            <input type="checkbox" id="check-forms">
                            <label for="check-forms">Contact Forms Functional</label>
                        </div>
                        <div class="check-item">
                            <input type="checkbox" id="check-links">
                            <label for="check-links">Internal Links Updated</label>
                        </div>
                    </div>
                </div>
                <div class="monitoring-setup">
                    <h5>Monitoring Tools</h5>
                    <div class="tools-grid">
                        <button class="tool-button setup-uptime">Setup Uptime Monitoring</button>
                        <button class="tool-button setup-ssl">Setup SSL Monitoring</button>
                        <button class="tool-button setup-performance">Setup Performance Monitoring</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Emergency Support -->
    <div class="emergency-support">
        <h3>Emergency Support</h3>
        <div class="support-options">
            <div class="support-card chat">
                <span class="support-icon dashicons dashicons-admin-comments"></span>
                <h4>Live Chat Support</h4>
                <p>Available 24/7 for urgent domain issues</p>
                <button class="start-chat">Start Chat</button>
            </div>
            <div class="support-card phone">
                <span class="support-icon dashicons dashicons-phone"></span>
                <h4>Emergency Phone</h4>
                <p>Direct line to domain specialists</p>
                <button class="call-support">Call Now</button>
            </div>
            <div class="support-card rollback">
                <span class="support-icon dashicons dashicons-backup"></span>
                <h4>Emergency Rollback</h4>
                <p>Instantly revert DNS changes</p>
                <button class="trigger-rollback">Rollback Changes</button>
            </div>
        </div>
    </div>

    <!-- Common Issues -->
    <div class="common-issues">
        <h3>Common Issues & Solutions</h3>
        <div class="issues-accordion">
            <div class="issue-item">
                <div class="issue-header">
                    <h4>DNS Propagation Delays</h4>
                    <span class="toggle-icon dashicons dashicons-arrow-down-alt2"></span>
                </div>
                <div class="issue-content">
                    <p>DNS changes can take 24-48 hours to propagate globally. During this time:</p>
                    <ul>
                        <li>Some users might see the old site while others see the new site</li>
                        <li>Email services might experience temporary interruptions</li>
                        <li>SSL certificates might show warnings</li>
                    </ul>
                    <div class="solution">
                        <h5>Solution:</h5>
                        <p>Use our DNS propagation checker to monitor the status across different regions.</p>
                        <button class="check-propagation">Check DNS Propagation</button>
                    </div>
                </div>
            </div>
            
            <div class="issue-item">
                <div class="issue-header">
                    <h4>SSL Certificate Issues</h4>
                    <span class="toggle-icon dashicons dashicons-arrow-down-alt2"></span>
                </div>
                <div class="issue-content">
                    <p>Common SSL issues during migration:</p>
                    <ul>
                        <li>Certificate not properly installed</li>
                        <li>Mixed content warnings</li>
                        <li>Certificate mismatch errors</li>
                    </ul>
                    <div class="solution">
                        <h5>Solution:</h5>
                        <p>Our SSL checker can identify and help fix common certificate issues.</p>
                        <button class="check-ssl">Check SSL Status</button>
                    </div>
                </div>
            </div>
            
            <div class="issue-item">
                <div class="issue-header">
                    <h4>Email Configuration</h4>
                    <span class="toggle-icon dashicons dashicons-arrow-down-alt2"></span>
                </div>
                <div class="issue-content">
                    <p>Email issues to watch for:</p>
                    <ul>
                        <li>MX records not properly configured</li>
                        <li>SPF record missing or incorrect</li>
                        <li>DKIM configuration errors</li>
                    </ul>
                    <div class="solution">
                        <h5>Solution:</h5>
                        <p>Use our email configuration validator to ensure proper setup.</p>
                        <button class="check-email">Validate Email Config</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
