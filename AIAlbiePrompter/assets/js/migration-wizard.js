document.addEventListener('DOMContentLoaded', function() {
    const wizard = {
        currentStep: 1,
        totalSteps: 4,
        selectedTemplate: null,
        
        init: function() {
            this.bindEvents();
            this.updateStepDisplay();
        },
        
        bindEvents: function() {
            // Navigation buttons
            document.querySelectorAll('.wizard-nav button').forEach(button => {
                button.addEventListener('click', this.handleNavigation.bind(this));
            });
            
            // URL form
            document.getElementById('url-form').addEventListener('submit', this.handleUrlSubmit.bind(this));
            
            // Template selection
            document.querySelectorAll('.template-card').forEach(card => {
                card.addEventListener('click', this.handleTemplateSelect.bind(this));
            });
            
            // Live preview buttons
            document.querySelectorAll('.live-preview-btn').forEach(button => {
                button.addEventListener('click', this.handlePreviewClick.bind(this));
            });
            
            // Migration start
            document.getElementById('start-migration').addEventListener('click', this.handleMigrationStart.bind(this));
        },
        
        handleNavigation: function(event) {
            const direction = event.target.dataset.direction;
            
            if (direction === 'next' && this.validateCurrentStep()) {
                this.currentStep++;
            } else if (direction === 'prev') {
                this.currentStep--;
            }
            
            this.updateStepDisplay();
        },
        
        validateCurrentStep: function() {
            switch(this.currentStep) {
                case 1:
                    return this.validateUrlStep();
                case 2:
                    return this.validateTemplateStep();
                case 3:
                    return this.validatePreviewStep();
                default:
                    return true;
            }
        },
        
        validateUrlStep: function() {
            const url = document.getElementById('site-url').value;
            const intent = document.getElementById('migration-intent').value;
            
            if (!url || !intent) {
                this.showError('Please provide both URL and migration intent');
                return false;
            }
            
            return true;
        },
        
        validateTemplateStep: function() {
            if (!this.selectedTemplate) {
                this.showError('Please select a template');
                return false;
            }
            
            return true;
        },
        
        validatePreviewStep: function() {
            const confirmed = document.getElementById('preview-confirm').checked;
            
            if (!confirmed) {
                this.showError('Please confirm the preview looks correct');
                return false;
            }
            
            return true;
        },
        
        handleUrlSubmit: async function(event) {
            event.preventDefault();
            
            const url = document.getElementById('site-url').value;
            const intent = document.getElementById('migration-intent').value;
            
            try {
                const response = await this.startMigration(url, intent);
                
                if (response.success) {
                    this.updateRecommendations(response.recommendations);
                    this.currentStep++;
                    this.updateStepDisplay();
                } else {
                    this.showError(response.error);
                }
            } catch (error) {
                this.showError('Failed to analyze site: ' + error.message);
            }
        },
        
        handleTemplateSelect: function(event) {
            const card = event.currentTarget;
            this.selectedTemplate = card.dataset.templateId;
            
            // Update UI
            document.querySelectorAll('.template-card').forEach(c => {
                c.classList.remove('selected');
            });
            card.classList.add('selected');
        },
        
        handlePreviewClick: async function(event) {
            event.preventDefault();
            event.stopPropagation();
            
            const templateId = event.target.closest('.template-card').dataset.templateId;
            
            try {
                const preview = await this.getTemplatePreview(templateId);
                this.showPreviewModal(preview);
            } catch (error) {
                this.showError('Failed to load preview: ' + error.message);
            }
        },
        
        handleMigrationStart: async function() {
            if (!this.validateCurrentStep()) {
                return;
            }
            
            const options = this.gatherMigrationOptions();
            
            try {
                const response = await this.applyMigration(this.selectedTemplate, options);
                
                if (response.success) {
                    this.showSuccess('Migration completed successfully!');
                    this.showMigrationResults(response);
                } else {
                    this.showError(response.error);
                }
            } catch (error) {
                this.showError('Migration failed: ' + error.message);
            }
        },
        
        startMigration: async function(url, intent) {
            const response = await fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-WP-Nonce': wpApiSettings.nonce
                },
                body: new URLSearchParams({
                    action: 'aialbie_start_migration',
                    url: url,
                    intent: intent
                })
            });
            
            return await response.json();
        },
        
        getTemplatePreview: async function(templateId) {
            const response = await fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-WP-Nonce': wpApiSettings.nonce
                },
                body: new URLSearchParams({
                    action: 'aialbie_get_preview',
                    template_id: templateId
                })
            });
            
            return await response.json();
        },
        
        applyMigration: async function(templateId, options) {
            const response = await fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-WP-Nonce': wpApiSettings.nonce
                },
                body: new URLSearchParams({
                    action: 'aialbie_apply_migration',
                    template_id: templateId,
                    options: JSON.stringify(options)
                })
            });
            
            return await response.json();
        },
        
        updateStepDisplay: function() {
            // Update progress bar
            const progress = ((this.currentStep - 1) / (this.totalSteps - 1)) * 100;
            document.querySelector('.progress-bar-fill').style.width = `${progress}%`;
            
            // Update step numbers
            document.querySelectorAll('.step-number').forEach((step, index) => {
                if (index + 1 < this.currentStep) {
                    step.classList.add('completed');
                } else if (index + 1 === this.currentStep) {
                    step.classList.add('active');
                } else {
                    step.classList.remove('completed', 'active');
                }
            });
            
            // Show/hide steps
            document.querySelectorAll('.wizard-step').forEach((step, index) => {
                if (index + 1 === this.currentStep) {
                    step.classList.add('active');
                } else {
                    step.classList.remove('active');
                }
            });
            
            // Update navigation buttons
            const prevBtn = document.querySelector('[data-direction="prev"]');
            const nextBtn = document.querySelector('[data-direction="next"]');
            
            prevBtn.style.display = this.currentStep === 1 ? 'none' : 'block';
            nextBtn.style.display = this.currentStep === this.totalSteps ? 'none' : 'block';
            
            // Show/hide start migration button
            const startBtn = document.getElementById('start-migration');
            startBtn.style.display = this.currentStep === this.totalSteps ? 'block' : 'none';
        },
        
        updateRecommendations: function(recommendations) {
            const container = document.querySelector('.recommendation-grid');
            container.innerHTML = '';
            
            recommendations.forEach(rec => {
                const template = rec.template;
                const card = document.createElement('div');
                card.className = 'template-card';
                card.dataset.templateId = template.id;
                
                card.innerHTML = `
                    <div class="template-preview">
                        <img src="${template.preview_image}" alt="${template.name}">
                        <div class="preview-overlay">
                            <button class="live-preview-btn">Live Preview</button>
                        </div>
                    </div>
                    <div class="template-info">
                        <h3>${template.name}</h3>
                        <p>${template.description}</p>
                        <div class="ai-confidence">
                            <span class="confidence-label">AI Confidence Match:</span>
                            <div class="confidence-bar">
                                <div class="confidence-fill" style="width: ${rec.score}%"></div>
                            </div>
                            <span class="confidence-percent">${rec.score}%</span>
                        </div>
                        <div class="recommendation-reasons">
                            ${rec.reasons.map(reason => `<div class="reason">${reason}</div>`).join('')}
                        </div>
                    </div>
                `;
                
                container.appendChild(card);
            });
            
            // Rebind events
            this.bindEvents();
        },
        
        showPreviewModal: function(preview) {
            const modal = document.createElement('div');
            modal.className = 'preview-modal';
            
            modal.innerHTML = `
                <div class="preview-content">
                    <button class="close-preview">&times;</button>
                    <iframe srcdoc="${preview.html}" frameborder="0"></iframe>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Close button
            modal.querySelector('.close-preview').addEventListener('click', () => {
                modal.remove();
            });
            
            // Click outside to close
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                }
            });
        },
        
        showMigrationResults: function(results) {
            const container = document.querySelector('.migration-results');
            container.innerHTML = '';
            
            // Show created pages
            const pageList = document.createElement('div');
            pageList.className = 'created-pages';
            pageList.innerHTML = `
                <h3>Created Pages</h3>
                <ul>
                    ${results.pages.map(page => `
                        <li>
                            <a href="${page.url}" target="_blank">${page.title}</a>
                            <span class="page-status">Draft</span>
                        </li>
                    `).join('')}
                </ul>
            `;
            
            container.appendChild(pageList);
            
            // Show next steps
            const nextSteps = document.createElement('div');
            nextSteps.className = 'next-steps';
            nextSteps.innerHTML = `
                <h3>Next Steps</h3>
                <ol>
                    <li>Review the created pages</li>
                    <li>Make any necessary adjustments</li>
                    <li>Publish when ready</li>
                </ol>
            `;
            
            container.appendChild(nextSteps);
        },
        
        gatherMigrationOptions: function() {
            return {
                content: true, // Get from form
                layout: document.querySelector('input[name="layout"]:checked')?.value,
                customizations: {
                    colors: {
                        primary: document.getElementById('primary-color')?.value,
                        secondary: document.getElementById('secondary-color')?.value
                    },
                    fonts: {
                        heading: document.getElementById('heading-font')?.value,
                        body: document.getElementById('body-font')?.value
                    }
                }
            };
        },
        
        showError: function(message) {
            const alert = document.createElement('div');
            alert.className = 'error-alert';
            alert.textContent = message;
            
            document.querySelector('.wizard-content').prepend(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 5000);
        },
        
        showSuccess: function(message) {
            const alert = document.createElement('div');
            alert.className = 'success-alert';
            alert.textContent = message;
            
            document.querySelector('.wizard-content').prepend(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }
    };
    
    // Initialize wizard
    wizard.init();
});
