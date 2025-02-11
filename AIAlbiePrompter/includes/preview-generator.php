<?php
class AIAlbiePreviewGenerator {
    private $url;
    private $content;
    private $blocks;

    public function __construct($url) {
        $this->url = $url;
    }

    public function generate_preview() {
        // Fetch and analyze content
        $converter = new AIAlbieURLConverter($this->url);
        $result = $converter->convert_single_page();
        
        if (!$result['success']) {
            return array(
                'success' => false,
                'message' => 'Could not generate preview'
            );
        }

        $this->blocks = $result['blocks'];
        
        return array(
            'success' => true,
            'preview_html' => $this->create_preview_html(),
            'block_info' => $this->get_block_info()
        );
    }

    private function create_preview_html() {
        $preview = '<div class="preview-container">';
        
        // Side by side comparison
        $preview .= '<div class="preview-grid">';
        
        // Original content
        $preview .= '<div class="original-content">';
        $preview .= '<h3>Original Page</h3>';
        $preview .= '<iframe src="' . esc_url($this->url) . '" width="100%" height="600px"></iframe>';
        $preview .= '</div>';
        
        // WordPress preview
        $preview .= '<div class="wordpress-preview">';
        $preview .= '<h3>WordPress Preview</h3>';
        $preview .= '<div class="wp-preview-content">';
        
        foreach ($this->blocks as $block) {
            $preview .= $this->render_block_preview($block);
        }
        
        $preview .= '</div></div></div>';
        
        // Add controls
        $preview .= $this->add_preview_controls();
        
        $preview .= '</div>';
        
        return $preview;
    }

    private function render_block_preview($block) {
        $html = '<div class="preview-block" data-block-type="' . esc_attr($block['blockName']) . '">';
        
        // Add block type indicator
        $html .= '<div class="block-type">' . $this->get_block_icon($block['blockName']) . '</div>';
        
        // Add block content
        switch ($block['blockName']) {
            case 'core/image':
                $html .= '<figure class="wp-block-image">';
                $html .= '<img src="' . esc_url($block['attrs']['url']) . '" ';
                $html .= 'alt="' . esc_attr($block['attrs']['alt']) . '">';
                $html .= '</figure>';
                break;
                
            case 'core/paragraph':
                $html .= '<div class="wp-block-paragraph">';
                $html .= wp_kses_post($block['innerHTML']);
                $html .= '</div>';
                break;
                
            default:
                $html .= wp_kses_post($block['innerHTML']);
        }
        
        // Add block controls
        $html .= $this->add_block_controls();
        
        $html .= '</div>';
        
        return $html;
    }

    private function get_block_icon($blockName) {
        $icons = array(
            'core/paragraph' => '¶',
            'core/image' => '🖼️',
            'core/heading' => 'H',
            'core/list' => '•'
        );
        
        return $icons[$blockName] ?? '⬒';
    }

    private function add_block_controls() {
        return '
        <div class="block-controls">
            <button class="move-up">↑</button>
            <button class="move-down">↓</button>
            <button class="edit">✎</button>
            <button class="delete">×</button>
        </div>';
    }

    private function add_preview_controls() {
        return '
        <div class="preview-controls">
            <button class="approve-all">Approve All</button>
            <button class="convert-approved">Convert Approved Blocks</button>
            <div class="preview-options">
                <label>
                    <input type="checkbox" class="sync-scroll" checked>
                    Sync scrolling
                </label>
                <label>
                    <input type="checkbox" class="highlight-differences">
                    Highlight differences
                </label>
            </div>
        </div>';
    }

    private function get_block_info() {
        $info = array(
            'total_blocks' => count($this->blocks),
            'block_types' => array(),
            'images' => 0,
            'paragraphs' => 0,
            'other' => 0
        );
        
        foreach ($this->blocks as $block) {
            $info['block_types'][$block['blockName']] = 
                ($info['block_types'][$block['blockName']] ?? 0) + 1;
                
            switch ($block['blockName']) {
                case 'core/image':
                    $info['images']++;
                    break;
                case 'core/paragraph':
                    $info['paragraphs']++;
                    break;
                default:
                    $info['other']++;
            }
        }
        
        return $info;
    }
}
