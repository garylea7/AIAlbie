<?php defined('ABSPATH') || exit; ?>

<div class="wp-access-guide">
    <div class="guide-header">
        <h2>WordPress Access Guide</h2>
        <p class="guide-subtitle">Everything you need to know about accessing your WordPress site</p>
    </div>

    <!-- Quick Access Finder -->
    <div class="access-finder">
        <h3>Find Your WordPress Login</h3>
        <div class="finder-input">
            <input type="text" id="site-url" placeholder="Enter your website URL (e.g., historicaviationmilitary.com)" class="url-input">
            <button id="find-login" class="find-button">
                <span class="button-text">Find Login URL</span>
                <span class="spinner"></span>
            </button>
        </div>

        <!-- Common Login URLs -->
        <div class="common-urls">
            <h4>Common Login URLs</h4>
            <div class="url-list">
                <div class="url-item">
                    <code class="url-pattern">/wp-admin</code>
                    <span class="url-example">Example: yoursite.com/wp-admin</span>
                    <button class="try-url" data-pattern="/wp-admin">Try This</button>
                </div>
                <div class="url-item">
                    <code class="url-pattern">/wp-login.php</code>
                    <span class="url-example">Example: yoursite.com/wp-login.php</span>
                    <button class="try-url" data-pattern="/wp-login.php">Try This</button>
                </div>
                <div class="url-item">
                    <code class="url-pattern">/login</code>
                    <span class="url-example">Example: yoursite.com/login</span>
                    <button class="try-url" data-pattern="/login">Try This</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Recovery -->
    <div class="password-recovery">
        <h3>Lost Access?</h3>
        <div class="recovery-options">
            <div class="option-card email">
                <span class="option-icon dashicons dashicons-email"></span>
                <h4>Email Recovery</h4>
                <p>Recover access using your admin email</p>
                <div class="recovery-form">
                    <input type="email" id="admin-email" placeholder="Enter your admin email">
                    <button class="send-recovery">Send Recovery Link</button>
                </div>
            </div>
            <div class="option-card ftp">
                <span class="option-icon dashicons dashicons-admin-network"></span>
                <h4>FTP Recovery</h4>
                <p>Reset access via FTP if you have server access</p>
                <button class="show-ftp-guide">View FTP Guide</button>
            </div>
            <div class="option-card database">
                <span class="option-icon dashicons dashicons-database"></span>
                <h4>Database Reset</h4>
                <p>Reset password through database access</p>
                <button class="show-db-guide">View Database Guide</button>
            </div>
        </div>
    </div>

    <!-- Access Management -->
    <div class="access-management">
        <h3>Access Management</h3>
        
        <!-- User Roles -->
        <div class="role-guide">
            <h4>WordPress User Roles</h4>
            <div class="role-grid">
                <div class="role-card">
                    <h5>Administrator</h5>
                    <ul class="role-permissions">
                        <li>Full site management</li>
                        <li>Install plugins/themes</li>
                        <li>Manage users</li>
                        <li>Edit code</li>
                    </ul>
                </div>
                <div class="role-card">
                    <h5>Editor</h5>
                    <ul class="role-permissions">
                        <li>Manage all posts/pages</li>
                        <li>Moderate comments</li>
                        <li>Manage categories</li>
                        <li>Manage other users' posts</li>
                    </ul>
                </div>
                <div class="role-card">
                    <h5>Author</h5>
                    <ul class="role-permissions">
                        <li>Write/edit own posts</li>
                        <li>Delete own posts</li>
                        <li>Upload files</li>
                    </ul>
                </div>
                <div class="role-card">
                    <h5>Contributor</h5>
                    <ul class="role-permissions">
                        <li>Write own posts</li>
                        <li>Edit own posts</li>
                        <li>No publishing</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Security Best Practices -->
        <div class="security-practices">
            <h4>Security Best Practices</h4>
            <div class="practices-grid">
                <div class="practice-card">
                    <span class="practice-icon dashicons dashicons-shield"></span>
                    <h5>Strong Passwords</h5>
                    <p>Use complex passwords with mixed characters</p>
                    <button class="generate-password">Generate Strong Password</button>
                </div>
                <div class="practice-card">
                    <span class="practice-icon dashicons dashicons-lock"></span>
                    <h5>Two-Factor Auth</h5>
                    <p>Enable additional security layer</p>
                    <button class="setup-2fa">Setup 2FA</button>
                </div>
                <div class="practice-card">
                    <span class="practice-icon dashicons dashicons-admin-users"></span>
                    <h5>Limited Access</h5>
                    <p>Only grant necessary permissions</p>
                    <button class="audit-users">Audit User Access</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Common Issues -->
    <div class="access-issues">
        <h3>Common Access Issues</h3>
        <div class="issues-grid">
            <div class="issue-card">
                <h4>Can't Find Login URL</h4>
                <div class="solution">
                    <h5>Solutions:</h5>
                    <ol>
                        <li>Try common login URLs listed above</li>
                        <li>Check your welcome email</li>
                        <li>Contact your hosting provider</li>
                        <li>Use our automatic login finder</li>
                    </ol>
                </div>
            </div>
            <div class="issue-card">
                <h4>Forgot Password</h4>
                <div class="solution">
                    <h5>Solutions:</h5>
                    <ol>
                        <li>Use the "Lost your password?" link</li>
                        <li>Check spam folder for reset email</li>
                        <li>Use FTP to reset password</li>
                        <li>Contact site administrator</li>
                    </ol>
                </div>
            </div>
            <div class="issue-card">
                <h4>Lost Admin Email</h4>
                <div class="solution">
                    <h5>Solutions:</h5>
                    <ol>
                        <li>Check website's WHOIS information</li>
                        <li>Access through FTP/phpMyAdmin</li>
                        <li>Contact hosting provider</li>
                        <li>Use emergency recovery tools</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Emergency Access -->
    <div class="emergency-access">
        <h3>Emergency Access Recovery</h3>
        <div class="emergency-options">
            <div class="emergency-card hosting">
                <span class="emergency-icon dashicons dashicons-admin-home"></span>
                <h4>Hosting Provider Recovery</h4>
                <p>Contact your hosting provider for emergency access</p>
                <div class="provider-lookup">
                    <input type="text" id="domain-lookup" placeholder="Enter your domain">
                    <button class="find-provider">Find Provider</button>
                </div>
            </div>
            <div class="emergency-card support">
                <span class="emergency-icon dashicons dashicons-businessman"></span>
                <h4>Expert Support</h4>
                <p>Get help from WordPress experts</p>
                <button class="contact-support">Contact Support</button>
            </div>
        </div>
    </div>

    <!-- Access Documentation -->
    <div class="access-docs">
        <h3>Important Documentation</h3>
        <div class="docs-grid">
            <a href="#" class="doc-card">
                <span class="doc-icon dashicons dashicons-media-document"></span>
                <h4>Access Recovery Guide</h4>
                <p>Step-by-step recovery instructions</p>
            </a>
            <a href="#" class="doc-card">
                <span class="doc-icon dashicons dashicons-welcome-learn-more"></span>
                <h4>WordPress Basics</h4>
                <p>Learn WordPress fundamentals</p>
            </a>
            <a href="#" class="doc-card">
                <span class="doc-icon dashicons dashicons-shield"></span>
                <h4>Security Guide</h4>
                <p>Keep your site secure</p>
            </a>
        </div>
    </div>
</div>
