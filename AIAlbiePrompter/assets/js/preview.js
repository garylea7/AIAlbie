document.addEventListener('DOMContentLoaded', function() {
    const previewContainer = document.querySelector('.preview-container');
    if (!previewContainer) return;

    // Initialize sync scrolling
    const originalContent = document.querySelector('.original-content');
    const wordpressPreview = document.querySelector('.wordpress-preview');
    let isScrolling = false;

    function syncScroll(source, target) {
        if (!isScrolling) {
            isScrolling = true;
            const percentage = source.scrollTop / (source.scrollHeight - source.clientHeight);
            target.scrollTop = percentage * (target.scrollHeight - target.clientHeight);
            setTimeout(() => isScrolling = false, 50);
        }
    }

    // Handle sync scroll checkbox
    const syncScrollCheckbox = document.querySelector('.sync-scroll');
    syncScrollCheckbox.addEventListener('change', function() {
        if (this.checked) {
            originalContent.addEventListener('scroll', () => syncScroll(originalContent, wordpressPreview));
            wordpressPreview.addEventListener('scroll', () => syncScroll(wordpressPreview, originalContent));
        } else {
            originalContent.removeEventListener('scroll', () => syncScroll(originalContent, wordpressPreview));
            wordpressPreview.removeEventListener('scroll', () => syncScroll(wordpressPreview, originalContent));
        }
    });

    // Handle block controls
    document.querySelectorAll('.preview-block').forEach(block => {
        const controls = block.querySelector('.block-controls');
        
        controls.querySelector('.move-up').addEventListener('click', () => {
            const prev = block.previousElementSibling;
            if (prev) {
                block.parentNode.insertBefore(block, prev);
            }
        });

        controls.querySelector('.move-down').addEventListener('click', () => {
            const next = block.nextElementSibling;
            if (next) {
                block.parentNode.insertBefore(next, block);
            }
        });

        controls.querySelector('.edit').addEventListener('click', () => {
            openBlockEditor(block);
        });

        controls.querySelector('.delete').addEventListener('click', () => {
            if (confirm('Are you sure you want to remove this block?')) {
                block.remove();
            }
        });
    });

    // Block editor modal
    function openBlockEditor(block) {
        const modal = document.createElement('div');
        modal.className = 'block-editor-modal';
        
        const content = block.querySelector('.wp-block-paragraph, .wp-block-image');
        const isImage = block.querySelector('.wp-block-image') !== null;
        
        modal.innerHTML = `
            <div class="modal-content">
                <h3>Edit ${isImage ? 'Image' : 'Text'} Block</h3>
                ${isImage ? `
                    <input type="text" value="${content.querySelector('img').src}" placeholder="Image URL">
                    <input type="text" value="${content.querySelector('img').alt}" placeholder="Alt text">
                ` : `
                    <textarea>${content.innerHTML}</textarea>
                `}
                <div class="modal-buttons">
                    <button class="save">Save</button>
                    <button class="cancel">Cancel</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        modal.querySelector('.save').addEventListener('click', () => {
            if (isImage) {
                const [urlInput, altInput] = modal.querySelectorAll('input');
                content.querySelector('img').src = urlInput.value;
                content.querySelector('img').alt = altInput.value;
            } else {
                content.innerHTML = modal.querySelector('textarea').value;
            }
            modal.remove();
        });
        
        modal.querySelector('.cancel').addEventListener('click', () => {
            modal.remove();
        });
    }

    // Handle conversion
    document.querySelector('.convert-approved').addEventListener('click', async () => {
        const blocks = Array.from(document.querySelectorAll('.preview-block')).map(block => ({
            type: block.dataset.blockType,
            content: block.innerHTML
        }));
        
        try {
            const response = await fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'convert_blocks',
                    blocks: blocks
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.location.href = data.edit_url;
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            alert('Error converting blocks: ' + error.message);
        }
    });
});
