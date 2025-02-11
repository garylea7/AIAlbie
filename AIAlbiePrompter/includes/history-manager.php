class AIAlbieHistoryManager {
    private $history = array();
    private $current_index = -1;
    private $max_history = 50;

    public function __construct() {
        $this->clear();
    }

    public function add_action($action_data) {
        // Create detailed action description
        $description = $this->generate_action_description($action_data);

        // Add new action with details
        $this->history[] = array(
            'timestamp' => current_time('mysql'),
            'action' => $action_data['type'],
            'description' => $description,
            'details' => array(
                'affected_elements' => $action_data['elements'] ?? array(),
                'changes_made' => $action_data['changes'] ?? array(),
                'user_action' => $action_data['user_action'] ?? '',
                'location' => $action_data['location'] ?? ''
            ),
            'snapshot' => $this->take_snapshot()
        );

        // Update pointer and trim history
        $this->current_index++;
        $this->trim_history();
    }

    private function generate_action_description($action_data) {
        $descriptions = array(
            'text_edit' => array(
                'format' => 'Changed text from "%s" to "%s"',
                'params' => array('old_text', 'new_text')
            ),
            'image_move' => array(
                'format' => 'Moved image "%s" from %s to %s',
                'params' => array('image_name', 'old_position', 'new_position')
            ),
            'block_add' => array(
                'format' => 'Added new %s block with content: %s',
                'params' => array('block_type', 'content_preview')
            ),
            'block_delete' => array(
                'format' => 'Removed %s block containing: %s',
                'params' => array('block_type', 'content_preview')
            ),
            'block_move' => array(
                'format' => 'Moved %s block from position %d to %d',
                'params' => array('block_type', 'old_position', 'new_position')
            ),
            'image_edit' => array(
                'format' => 'Modified image "%s" (%s)',
                'params' => array('image_name', 'change_type')
            ),
            'layout_change' => array(
                'format' => 'Changed layout from %s to %s',
                'params' => array('old_layout', 'new_layout')
            ),
            'style_change' => array(
                'format' => 'Updated styling of %s (%s)',
                'params' => array('element_type', 'style_changes')
            ),
            'undo' => array(
                'format' => 'Undid previous action: %s',
                'params' => array('previous_action')
            ),
            'redo' => array(
                'format' => 'Redid action: %s',
                'params' => array('action_description')
            )
        );

        if (isset($descriptions[$action_data['type']])) {
            $format = $descriptions[$action_data['type']]['format'];
            $params = array_map(function($param) use ($action_data) {
                return $action_data[$param] ?? 'unknown';
            }, $descriptions[$action_data['type']]['params']);

            return vsprintf($format, $params);
        }

        return 'Performed ' . $action_data['type'];
    }

    private function trim_history() {
        if (count($this->history) > $this->max_history) {
            array_shift($this->history);
            $this->current_index--;
        }
    }

    public function undo() {
        if ($this->can_undo()) {
            $this->current_index--;
            return $this->restore_snapshot($this->history[$this->current_index]['snapshot']);
        }
        return false;
    }

    public function redo() {
        if ($this->can_redo()) {
            $this->current_index++;
            return $this->restore_snapshot($this->history[$this->current_index]['snapshot']);
        }
        return false;
    }

    public function can_undo() {
        return $this->current_index > 0;
    }

    public function can_redo() {
        return $this->current_index < count($this->history) - 1;
    }

    public function clear() {
        $this->history = array(array(
            'timestamp' => current_time('mysql'),
            'action' => 'Initial State',
            'description' => 'Initial State',
            'details' => array(),
            'snapshot' => $this->take_snapshot()
        ));
        $this->current_index = 0;
    }

    public function get_history() {
        return array(
            'actions' => array_map(function($item) {
                return array(
                    'timestamp' => $item['timestamp'],
                    'action' => $item['action'],
                    'description' => $item['description'],
                    'details' => $this->format_details($item['details']),
                    'can_undo' => true
                );
            }, $this->history),
            'current_index' => $this->current_index,
            'can_undo' => $this->can_undo(),
            'can_redo' => $this->can_redo()
        );
    }

    private function format_details($details) {
        $formatted = array();
        
        if (!empty($details['affected_elements'])) {
            $formatted[] = sprintf(
                'Modified %d elements: %s',
                count($details['affected_elements']),
                implode(', ', array_map(function($el) {
                    return $el['type'] . ' (' . $el['id'] . ')';
                }, $details['affected_elements']))
            );
        }

        if (!empty($details['changes_made'])) {
            $formatted[] = 'Changes: ' . implode(', ', $details['changes_made']);
        }

        if (!empty($details['location'])) {
            $formatted[] = 'Location: ' . $details['location'];
        }

        return $formatted;
    }

    private function take_snapshot() {
        return array(
            'blocks' => $this->get_current_blocks(),
            'settings' => $this->get_current_settings()
        );
    }

    private function restore_snapshot($snapshot) {
        return array(
            'success' => true,
            'blocks' => $snapshot['blocks'],
            'settings' => $snapshot['settings']
        );
    }

    private function get_current_blocks() {
        // Get current block data from the editor
        return isset($_POST['blocks']) ? $_POST['blocks'] : array();
    }

    private function get_current_settings() {
        // Get current settings
        return isset($_POST['settings']) ? $_POST['settings'] : array();
    }

    public function get_checkpoint($index) {
        if (isset($this->history[$index])) {
            return $this->history[$index];
        }
        return false;
    }

    public function restore_checkpoint($index) {
        if (isset($this->history[$index])) {
            $this->current_index = $index;
            return $this->restore_snapshot($this->history[$index]['snapshot']);
        }
        return false;
    }
}
