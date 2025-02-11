<?php
$history_manager = new AIAlbieHistoryManager();
$history = $history_manager->get_history();
?>

<div class="history-panel">
    <!-- Main Controls -->
    <div class="main-controls">
        <button class="undo-button" <?php echo !$history['can_undo'] ? 'disabled' : ''; ?>>
            <span class="dashicons dashicons-undo"></span> Undo Last Change
        </button>
        <button class="history-toggle">
            <span class="dashicons dashicons-backup"></span> Show History
        </button>
        <button class="restart-button">
            <span class="dashicons dashicons-controls-back"></span> Start Fresh
        </button>
    </div>

    <!-- History Viewer -->
    <div class="history-viewer" style="display: none;">
        <div class="history-header">
            <h3>Change History</h3>
            <button class="close-history">×</button>
        </div>

        <!-- Timeline with Previews -->
        <div class="timeline-container">
            <?php foreach ($history['actions'] as $index => $action): ?>
                <div class="timeline-item <?php echo $index === $history['current_index'] ? 'current' : ''; ?>"
                     data-index="<?php echo $index; ?>">
                    <div class="timeline-marker">
                        <div class="timeline-dot"></div>
                        <div class="timeline-line"></div>
                    </div>
                    
                    <div class="timeline-content">
                        <div class="action-info">
                            <span class="time"><?php echo date('H:i:s', strtotime($action['timestamp'])); ?></span>
                            <span class="action"><?php echo esc_html($action['action']); ?></span>
                        </div>
                        
                        <!-- Preview Panel (shows on hover) -->
                        <div class="preview-panel">
                            <div class="preview-header">
                                <span>Before</span>
                                <span>After</span>
                            </div>
                            <div class="preview-content">
                                <div class="preview-before">
                                    <img src="data:image/png;base64,<?php echo $action['snapshot']['before_preview']; ?>" 
                                         alt="Before change">
                                </div>
                                <div class="preview-after">
                                    <img src="data:image/png;base64,<?php echo $action['snapshot']['after_preview']; ?>" 
                                         alt="After change">
                                </div>
                            </div>
                            <div class="preview-actions">
                                <button class="restore-point">Restore to This Point</button>
                                <button class="preview-changes">Preview Changes</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="preview-modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Preview Changes</h3>
                <button class="close-modal">×</button>
            </div>
            <div class="preview-comparison">
                <div class="preview-before">
                    <h4>Before</h4>
                    <div class="preview-frame"></div>
                </div>
                <div class="preview-after">
                    <h4>After</h4>
                    <div class="preview-frame"></div>
                </div>
            </div>
            <div class="modal-actions">
                <button class="apply-changes">Apply These Changes</button>
                <button class="cancel-preview">Cancel</button>
            </div>
        </div>
    </div>
</div>

<style>
.history-panel {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    z-index: 1000;
    max-width: 800px;
    width: 90%;
}

.main-controls {
    display: flex;
    gap: 10px;
    padding: 15px;
    border-bottom: 1px solid #eee;
}

.main-controls button {
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    background: #f0f0f0;
}

.main-controls button:hover {
    background: #e0e0e0;
}

.undo-button {
    color: #0073aa;
}

.restart-button {
    color: #dc3545;
}

.history-viewer {
    max-height: 400px;
    overflow-y: auto;
}

.history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #eee;
}

.timeline-container {
    padding: 15px;
}

.timeline-item {
    display: flex;
    gap: 15px;
    padding: 10px 0;
    cursor: pointer;
    position: relative;
}

.timeline-marker {
    position: relative;
    width: 20px;
}

.timeline-dot {
    width: 12px;
    height: 12px;
    background: #0073aa;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #0073aa;
}

.timeline-line {
    position: absolute;
    top: 12px;
    bottom: -10px;
    left: 6px;
    width: 2px;
    background: #ddd;
}

.timeline-item:last-child .timeline-line {
    display: none;
}

.timeline-content {
    flex: 1;
}

.action-info {
    display: flex;
    gap: 10px;
    align-items: center;
}

.time {
    color: #666;
    font-size: 12px;
}

/* Preview Panel */
.preview-panel {
    display: none;
    margin-top: 10px;
    background: #f8f9fa;
    border-radius: 4px;
    padding: 10px;
}

.timeline-item:hover .preview-panel {
    display: block;
}

.preview-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-weight: bold;
}

.preview-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.preview-before,
.preview-after {
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.preview-before img,
.preview-after img {
    width: 100%;
    height: auto;
}

.preview-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.preview-actions button {
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.restore-point {
    background: #0073aa;
    color: white;
}

.preview-changes {
    background: #f0f0f0;
}

/* Preview Modal */
.preview-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1001;
}

.modal-content {
    background: white;
    border-radius: 8px;
    padding: 20px;
    width: 90%;
    max-width: 1200px;
    max-height: 90vh;
    overflow-y: auto;
}

.preview-comparison {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 20px 0;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

.apply-changes {
    background: #0073aa;
    color: white;
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.cancel-preview {
    background: #f0f0f0;
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const historyPanel = document.querySelector('.history-panel');
    const undoButton = document.querySelector('.undo-button');
    const historyToggle = document.querySelector('.history-toggle');
    const historyViewer = document.querySelector('.history-viewer');
    const restartButton = document.querySelector('.restart-button');
    const previewModal = document.querySelector('.preview-modal');

    // Undo handler - can go back multiple steps
    undoButton.addEventListener('click', async () => {
        if (undoButton.disabled) return;
        
        try {
            const response = await fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'aialbie_undo'
                })
            });
            
            const result = await response.json();
            if (result.success) {
                updateContent(result.blocks);
                updateHistoryControls(result.history);
            }
        } catch (error) {
            console.error('Undo failed:', error);
        }
    });

    // History toggle
    historyToggle.addEventListener('click', () => {
        historyViewer.style.display = 
            historyViewer.style.display === 'none' ? 'block' : 'none';
    });

    // Restart handler
    restartButton.addEventListener('click', () => {
        if (confirm('Start fresh? This will reset everything to the beginning but you can always restore from history later.')) {
            window.location.reload();
        }
    });

    // Timeline item preview
    document.querySelectorAll('.preview-changes').forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            const timelineItem = e.target.closest('.timeline-item');
            showPreviewModal(timelineItem.dataset.index);
        });
    });

    // Restore point handler
    document.querySelectorAll('.restore-point').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.stopPropagation();
            const timelineItem = e.target.closest('.timeline-item');
            const index = timelineItem.dataset.index;
            
            try {
                const response = await fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'aialbie_restore_checkpoint',
                        index: index
                    })
                });
                
                const result = await response.json();
                if (result.success) {
                    updateContent(result.blocks);
                    updateHistoryControls(result.history);
                }
            } catch (error) {
                console.error('Restore checkpoint failed:', error);
            }
        });
    });

    // Preview modal handlers
    function showPreviewModal(index) {
        previewModal.style.display = 'flex';
        // Load preview content
        loadPreviewContent(index);
    }

    document.querySelector('.close-modal').addEventListener('click', () => {
        previewModal.style.display = 'none';
    });

    document.querySelector('.apply-changes').addEventListener('click', async () => {
        const index = previewModal.dataset.previewIndex;
        // Apply changes
        await restoreCheckpoint(index);
        previewModal.style.display = 'none';
    });

    // Update functions
    function updateContent(blocks) {
        // Update blocks in the editor
    }

    function updateHistoryControls(history) {
        undoButton.disabled = !history.can_undo;
        // Update timeline items
    }

    async function loadPreviewContent(index) {
        // Load preview content for the modal
    }

    async function restoreCheckpoint(index) {
        // Restore to specific checkpoint
    }
});
</script>
