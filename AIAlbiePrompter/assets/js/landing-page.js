jQuery(document).ready(function($) {
    const aiAlbieLanding = {
        init: function() {
            this.bindEvents();
            this.initializeAnimations();
        },

        bindEvents: function() {
            $('#analyze-site').on('click', this.handleSiteAnalysis.bind(this));
            $('#migration-goal').on('change', this.handleGoalInput.bind(this));
            $('.try-case').on('click', this.handleUseCaseClick.bind(this));
            $('.analyze-site-cta').on('click', this.scrollToAnalyzer.bind(this));
        },

        initializeAnimations: function() {
            // Initialize any animations or visual effects
            this.initScoreAnimation();
        },

        handleSiteAnalysis: async function(e) {
            e.preventDefault();
            const url = $('#website-url').val().trim();
            
            if (!url) {
                this.showError('Please enter a valid website URL');
                return;
            }

            this.startAnalysis();

            try {
                const analysis = await this.analyzeSite(url);
                this.showAnalysisResults(analysis);
            } catch (error) {
                this.showError('Error analyzing site: ' + error.message);
            }
        },

        startAnalysis: function() {
            const button = $('#analyze-site');
            button.addClass('analyzing');
            button.prop('disabled', true);
            button.find('.button-text').text('Analyzing...');
        },

        analyzeSite: function(url) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: aiAlbieAdmin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'aialbie_analyze_site',
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
                    error: () => {
                        reject(new Error('Network error'));
                    }
                });
            });
        },

        showAnalysisResults: function(analysis) {
            // Update platform info
            $('.platform-name').text(analysis.platform);
            
            // Update content stats
            $('.pages-count').text(analysis.stats.pages);
            $('.images-count').text(analysis.stats.images);
            $('.posts-count').text(analysis.stats.posts);
            
            // Update compatibility score
            this.updateCompatibilityScore(analysis.compatibility);
            
            // Show migration steps
            this.showMigrationSteps(analysis.steps);
            
            // Show technical requirements
            this.showTechnicalRequirements(analysis.requirements);
            
            // Show the results section
            $('#analysis-results').slideDown();
            
            // Reset the analyze button
            const button = $('#analyze-site');
            button.removeClass('analyzing');
            button.prop('disabled', false);
            button.find('.button-text').text('Analyze My Site');
            
            // Scroll to results
            $('html, body').animate({
                scrollTop: $('#analysis-results').offset().top - 50
            }, 1000);
        },

        updateCompatibilityScore: function(score) {
            const circle = document.querySelector('.score-circle');
            const number = document.querySelector('.score-number');
            
            // Calculate the circumference
            const radius = 15.9155;
            const circumference = radius * 2 * Math.PI;
            
            // Calculate the dash array
            const dashArray = (score / 100) * circumference;
            
            // Animate the circle and number
            circle.style.strokeDasharray = `${dashArray} ${circumference}`;
            number.textContent = `${score}%`;
        },

        showMigrationSteps: function(steps) {
            const stepsHtml = steps.map((step, index) => `
                <div class="path-step">
                    <span class="step-number">${index + 1}</span>
                    <div class="step-content">
                        <h4>${step.title}</h4>
                        <p>${step.description}</p>
                    </div>
                </div>
            `).join('');
            
            $('.path-steps').html(stepsHtml);
        },

        showTechnicalRequirements: function(requirements) {
            const reqHtml = requirements.map(req => `
                <div class="requirement-item">
                    <span class="requirement-status ${req.met ? 'status-met' : 'status-missing'}">
                        <span class="dashicons ${req.met ? 'dashicons-yes-alt' : 'dashicons-warning'}"></span>
                    </span>
                    <div class="requirement-content">
                        <h4>${req.title}</h4>
                        <p>${req.description}</p>
                    </div>
                </div>
            `).join('');
            
            $('.requirements-list').html(reqHtml);
        },

        handleGoalInput: function(e) {
            const goal = e.target.value.trim();
            
            if (goal) {
                this.analyzeGoal(goal);
            }
        },

        analyzeGoal: function(goal) {
            $.ajax({
                url: aiAlbieAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'aialbie_analyze_goal',
                    nonce: aiAlbieAdmin.nonce,
                    goal: goal
                },
                success: (response) => {
                    if (response.success) {
                        this.showGoalRecommendations(response.data);
                    }
                }
            });
        },

        showGoalRecommendations: function(recommendations) {
            // Implementation for showing AI-generated recommendations
            // based on the user's natural language input
        },

        handleUseCaseClick: function(e) {
            const useCase = $(e.currentTarget).data('case');
            this.showUseCaseDemo(useCase);
        },

        showUseCaseDemo: function(useCase) {
            // Implementation for showing use case demonstrations
        },

        scrollToAnalyzer: function(e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $('.quick-analyzer').offset().top - 50
            }, 1000);
        },

        showError: function(message) {
            // Implementation for showing error messages
        },

        initScoreAnimation: function() {
            // Initialize the compatibility score circle animation
            const circle = document.querySelector('.score-circle');
            if (circle) {
                const radius = 15.9155;
                const circumference = radius * 2 * Math.PI;
                circle.style.strokeDasharray = `${circumference} ${circumference}`;
                circle.style.strokeDashoffset = circumference;
            }
        }
    };

    // Initialize
    aiAlbieLanding.init();
});
