<?php include_once('header.php'); ?>

<div class="aialbie-container">
    <div class="converter-section">
        <h1>Convert Website to WordPress</h1>
        
        <!-- Single URL Converter -->
        <div class="converter-box">
            <h2>Convert Single Page</h2>
            <form method="post" class="url-form">
                <input type="url" name="single_url" placeholder="https://historicaviationmilitary.com/page.html" required>
                <button type="submit">Convert Page</button>
            </form>
        </div>

        <!-- Batch URL Converter -->
        <div class="converter-box">
            <h2>Convert Entire Website</h2>
            <form method="post" class="url-form">
                <input type="url" name="site_url" placeholder="https://historicaviationmilitary.com" required>
                <div class="options">
                    <label>
                        <input type="checkbox" name="preserve_structure" checked>
                        Preserve site structure
                    </label>
                    <label>
                        <input type="checkbox" name="create_menu" checked>
                        Create navigation menu
                    </label>
                </div>
                <button type="submit">Convert Site</button>
            </form>
        </div>

        <!-- Progress Display -->
        <div class="progress-section" style="display: none;">
            <h3>Conversion Progress</h3>
            <div class="progress-bar">
                <div class="progress"></div>
            </div>
            <div class="status-message"></div>
        </div>

        <!-- Results Display -->
        <div class="results-section" style="display: none;">
            <h3>Converted Pages</h3>
            <div class="results-list"></div>
        </div>
    </div>
</div>

<style>
.converter-section {
    max-width: 800px;
    margin: 2em auto;
    padding: 20px;
}

.converter-box {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.url-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.url-form input[type="url"] {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.url-form button {
    padding: 10px 20px;
    background: #0073aa;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.options {
    display: flex;
    gap: 20px;
}

.progress-bar {
    height: 20px;
    background: #f0f0f0;
    border-radius: 10px;
    overflow: hidden;
    margin: 10px 0;
}

.progress {
    height: 100%;
    background: #0073aa;
    width: 0%;
    transition: width 0.3s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.url-form');
    const progressSection = document.querySelector('.progress-section');
    const progressBar = document.querySelector('.progress');
    const statusMessage = document.querySelector('.status-message');
    const resultsList = document.querySelector('.results-list');

    forms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Show progress section
            progressSection.style.display = 'block';
            
            // Get form data
            const formData = new FormData(form);
            
            try {
                // Start conversion
                const response = await fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update progress
                    updateProgress(data);
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                statusMessage.textContent = `Error: ${error.message}`;
            }
        });
    });

    function updateProgress(data) {
        progressBar.style.width = data.progress + '%';
        statusMessage.textContent = data.message;
        
        if (data.completed) {
            showResults(data.results);
        }
    }

    function showResults(results) {
        const resultsSection = document.querySelector('.results-section');
        resultsSection.style.display = 'block';
        
        resultsList.innerHTML = results.map(page => `
            <div class="result-item">
                <h4>${page.title}</h4>
                <p>Status: ${page.status}</p>
                <a href="${page.edit_url}" target="_blank">Edit Page</a>
            </div>
        `).join('');
    }
});
</script>

<?php include_once('footer.php'); ?>
