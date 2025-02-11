<?php
// Prevent direct access
if (!defined('ABSPATH')) exit;
?>

<div class="aialbie-prompter-container">
    <div class="prompter-header">
        <img src="<?php echo plugins_url('assets/images/albie-logo.png', dirname(__FILE__)); ?>" alt="AIAlbie Prompter" class="prompter-logo">
        <h2>Transform Your AI Prompts</h2>
        <p>Get better results from any AI tool with optimized prompts</p>
    </div>

    <div class="prompter-main">
        <div class="prompt-input-section">
            <select id="prompt-category" class="prompt-category-select">
                <option value="general">General Purpose</option>
                <option value="coding">Code & Development</option>
                <option value="creative">Creative & Design</option>
                <option value="business">Business & Marketing</option>
            </select>

            <textarea id="original-prompt" 
                      class="prompt-input" 
                      placeholder="Enter your basic prompt here..."></textarea>

            <button id="optimize-prompt" class="optimize-button">
                <span class="button-text">Optimize Prompt</span>
                <span class="loading-spinner hidden"></span>
            </button>
        </div>

        <div class="prompt-output-section hidden">
            <h3>Optimized Prompt:</h3>
            <div id="optimized-prompt" class="optimized-result"></div>
            
            <div class="action-buttons">
                <button id="copy-prompt" class="action-button">
                    <span class="dashicons dashicons-clipboard"></span> Copy
                </button>
                <button id="save-prompt" class="action-button">
                    <span class="dashicons dashicons-star-filled"></span> Save
                </button>
            </div>
        </div>
    </div>

    <div class="prompter-tips">
        <h3>Pro Tips:</h3>
        <ul>
            <li>Be specific about your desired outcome</li>
            <li>Include context and constraints</li>
            <li>Specify format preferences</li>
            <li>Mention any relevant examples</li>
        </ul>
    </div>
</div>
