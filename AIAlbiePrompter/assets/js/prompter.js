jQuery(document).ready(function($) {
    const optimizeButton = $('#optimize-prompt');
    const outputSection = $('.prompt-output-section');
    const copyButton = $('#copy-prompt');
    const saveButton = $('#save-prompt');

    optimizeButton.on('click', function() {
        const prompt = $('#original-prompt').val();
        const category = $('#prompt-category').val();
        
        if (!prompt) {
            alert('Please enter a prompt to optimize');
            return;
        }

        // Show loading state
        optimizeButton.prop('disabled', true);
        $('.button-text').addClass('hidden');
        $('.loading-spinner').removeClass('hidden');

        // Call WordPress REST API endpoint
        $.ajax({
            url: '/wp-json/aialbie-prompter/v1/optimize',
            method: 'POST',
            data: JSON.stringify({
                prompt: prompt,
                category: category
            }),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    $('#optimized-prompt').text(response.optimized);
                    outputSection.removeClass('hidden');
                } else {
                    alert('Failed to optimize prompt. Please try again.');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            },
            complete: function() {
                // Reset button state
                optimizeButton.prop('disabled', false);
                $('.button-text').removeClass('hidden');
                $('.loading-spinner').addClass('hidden');
            }
        });
    });

    copyButton.on('click', function() {
        const optimizedText = $('#optimized-prompt').text();
        navigator.clipboard.writeText(optimizedText).then(function() {
            const originalText = copyButton.text();
            copyButton.text('Copied!');
            setTimeout(() => copyButton.text(originalText), 2000);
        });
    });

    saveButton.on('click', function() {
        const optimizedText = $('#optimized-prompt').text();
        // Save to user's saved prompts (implement this feature later)
        alert('Prompt saved to your collection!');
    });
});
