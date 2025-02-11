<?php
// Get current history state
$history_manager = new AIAlbieHistoryManager();
$history = $history_manager->get_history();
?>

<div class="history-controls">
    <!-- Main Controls -->
    <div class="main-controls">
        <button class="undo-button" <?php echo !$history['can_undo'] ? 'disabled' : ''; ?>>
            <span class="dashicons dashicons-undo"></span> Undo
        </button>
        <button class="redo-button" <?php echo !$history['can_redo'] ? 'disabled' : ''; ?>>
            <span class="dashicons dashicons-redo"></span> Redo
        </button>
        <button class="history-toggle">
            <span class="dashicons dashicons-backup"></span> Show History
        </button>
        <button class="restart-button">
            <span class="dashicons dashicons-controls-back"></span> Start Over
        </button>
    </div>

    <!-- History Timeline -->
    <div class="history-timeline" style="display: none;">
        <h3>Action History</h3>
        <div class="timeline-container">
            <?php foreach ($history['actions'] as $index => $action): ?>
                <div class="timeline-item <?php echo $index === $history['current_index'] ? 'current' : ''; ?>"
                     data-index="<?php echo $index; ?>">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <span class="time"><?php echo date('H:i:s', strtotime($action['timestamp'])); ?></span>
                        <span class="action"><?php echo esc_html($action['action']); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.history-controls {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    padding: 10px 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    z-index: 1000;
}

.main-controls {
    display: flex;
    gap: 10px;
}

.main-controls button {
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    background: #f0f0f0;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
}

.main-controls button:hover {
    background: #e0e0e0;
}

.main-controls button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.history-timeline {
    margin-top: 15px;
    max-height: 200px;
    overflow-y: auto;
}

.timeline-container {
    position: relative;
    padding-left: 20px;
}

.timeline-container::before {
    content: '';
    position: absolute;
    left: 5px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #ddd;
}

.timeline-item {
    position: relative;
    padding: 10px 0;
    cursor: pointer;
}

.timeline-item:hover {
    background: #f5f5f5;
}

.timeline-dot {
    position: absolute;
    left: -20px;
    top: 50%;
    transform: translateY(-50%);
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #0073aa;
    border: 2px solid white;
}

.timeline-item.current .timeline-dot {
    background: #00a0d2;
    box-shadow: 0 0 0 3px rgba(0,160,210,0.2);
}

.timeline-content {
    display: flex;
    gap: 10px;
    align-items: center;
}

.time {
    font-size: 12px;
    color: #666;
}

.restart-button {
    background: #dc3545 !important;
    color: white;
}

.restart-button:hover {
    background: #c82333 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const historyControls = document.querySelector('.history-controls');
    const undoButton = document.querySelector('.undo-button');
    const redoButton = document.querySelector('.redo-button');
    const historyToggle = document.querySelector('.history-toggle');
    const historyTimeline = document.querySelector('.history-timeline');
    const restartButton = document.querySelector('.restart-button');

    // Undo/Redo handlers
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

    redoButton.addEventListener('click', async () => {
        if (redoButton.disabled) return;
        
        try {
            const response = await fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'aialbie_redo'
                })
            });
            
            const result = await response.json();
            if (result.success) {
                updateContent(result.blocks);
                updateHistoryControls(result.history);
            }
        } catch (error) {
            console.error('Redo failed:', error);
        }
    });

    // History timeline toggle
    historyToggle.addEventListener('click', () => {
        historyTimeline.style.display = 
            historyTimeline.style.display === 'none' ? 'block' : 'none';
    });

    // Restart button
    restartButton.addEventListener('click', () => {
        if (confirm('Are you sure you want to start over? This will reset everything to the beginning.')) {
            window.location.reload();
        }
    });

    // Timeline item click handler
    document.querySelectorAll('.timeline-item').forEach(item => {
        item.addEventListener('click', async () => {
            const index = item.dataset.index;
            
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

    // Update content and controls
    function updateContent(blocks) {
        // Update blocks in the editor
        // This will depend on your block editor implementation
    }

    function updateHistoryControls(history) {
        undoButton.disabled = !history.can_undo;
        redoButton.disabled = !history.can_redo;
        
        // Update timeline items
        const timelineContainer = document.querySelector('.timeline-container');
        timelineContainer.innerHTML = history.actions.map((action, index) => `
            <div class="timeline-item ${index === history.current_index ? 'current' : ''}"
                 data-index="${index}">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <span class="time">${new Date(action.timestamp).toLocaleTimeString()}</span>
                    <span class="action">${action.action}</span>
                </div>
            </div>
        `).join('');
    }
});
</script>
