<?php
class AIAlbiePrompterExport {
    public static function export_history($format = 'pdf') {
        $user_id = get_current_user_id();
        $history = self::get_user_history($user_id);
        
        switch($format) {
            case 'pdf':
                return self::generate_pdf($history);
            case 'csv':
                return self::generate_csv($history);
            default:
                return false;
        }
    }

    private static function get_user_history($user_id) {
        return get_user_meta($user_id, 'aialbie_prompter_history', true) ?: array();
    }

    private static function generate_pdf($history) {
        // Use WordPress PDF library or external library
        // For MVP, we'll create a simple HTML file that's printer-friendly
        $html = '<html><head><title>Your Prompt History</title>';
        $html .= '<style>
            body { font-family: Arial, sans-serif; }
            .prompt-item { margin-bottom: 20px; padding: 10px; border-bottom: 1px solid #ccc; }
            .original { color: #666; }
            .optimized { color: #000; font-weight: bold; }
        </style></head><body>';
        
        foreach($history as $item) {
            $html .= '<div class="prompt-item">';
            $html .= '<div class="original">Original: ' . esc_html($item['original']) . '</div>';
            $html .= '<div class="optimized">Optimized: ' . esc_html($item['optimized']) . '</div>';
            $html .= '<div class="meta">Category: ' . esc_html($item['category']) . ' | Date: ' . esc_html($item['date']) . '</div>';
            $html .= '</div>';
        }
        
        $html .= '</body></html>';
        
        return $html;
    }

    private static function generate_csv($history) {
        $csv = "Date,Category,Original Prompt,Optimized Prompt\n";
        
        foreach($history as $item) {
            $csv .= implode(',', array(
                $item['date'],
                $item['category'],
                '"' . str_replace('"', '""', $item['original']) . '"',
                '"' . str_replace('"', '""', $item['optimized']) . '"'
            )) . "\n";
        }
        
        return $csv;
    }

    public static function save_to_history($original, $optimized, $category) {
        $user_id = get_current_user_id();
        $history = self::get_user_history($user_id);
        
        $history[] = array(
            'date' => current_time('mysql'),
            'category' => $category,
            'original' => $original,
            'optimized' => $optimized
        );
        
        update_user_meta($user_id, 'aialbie_prompter_history', $history);
    }
}
