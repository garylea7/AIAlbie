<?php
class AIAlbieMediaHandler {
    private $base_url;
    private $upload_dir;
    private $processed_images = array();

    public function __construct($base_url = '') {
        $this->base_url = $base_url;
        $wp_upload_dir = wp_upload_dir();
        $this->upload_dir = $wp_upload_dir['path'];
    }

    public function process_images($images) {
        foreach ($images as $image) {
            $result = $this->process_single_image($image);
            if ($result) {
                $this->processed_images[] = $result;
            }
        }
        return $this->processed_images;
    }

    private function process_single_image($image) {
        $src = $image['src'];
        
        // Skip already processed images
        if (isset($this->processed_images[$src])) {
            return $this->processed_images[$src];
        }

        // Handle different URL formats
        $image_url = $this->normalize_url($src);
        if (!$image_url) {
            return false;
        }

        // Download the image
        $downloaded_file = $this->download_image($image_url);
        if (!$downloaded_file) {
            return false;
        }

        // Upload to WordPress media library
        $attachment_id = $this->upload_to_media_library($downloaded_file, $image);
        
        // Clean up temporary file
        @unlink($downloaded_file);

        if (!$attachment_id) {
            return false;
        }

        return array(
            'original_url' => $src,
            'attachment_id' => $attachment_id,
            'wordpress_url' => wp_get_attachment_url($attachment_id),
            'metadata' => wp_get_attachment_metadata($attachment_id)
        );
    }

    private function normalize_url($src) {
        // Handle data URLs
        if (strpos($src, 'data:image/') === 0) {
            return $this->handle_data_url($src);
        }

        // Handle relative URLs
        if (strpos($src, 'http') !== 0) {
            if (strpos($src, '//') === 0) {
                return 'https:' . $src;
            }
            return rtrim($this->base_url, '/') . '/' . ltrim($src, '/');
        }

        return $src;
    }

    private function handle_data_url($data_url) {
        $temp_file = $this->upload_dir . '/temp_' . uniqid() . '.png';
        $data = explode(',', $data_url);
        
        if (count($data) < 2) {
            return false;
        }

        $image_data = base64_decode($data[1]);
        if (!$image_data) {
            return false;
        }

        file_put_contents($temp_file, $image_data);
        return $temp_file;
    }

    private function download_image($url) {
        $temp_file = download_url($url);
        
        if (is_wp_error($temp_file)) {
            error_log('Failed to download image: ' . $url . ' - ' . $temp_file->get_error_message());
            return false;
        }

        return $temp_file;
    }

    private function upload_to_media_library($file_path, $image_data) {
        // Prepare file data
        $file_name = basename($image_data['src']);
        $wp_filetype = wp_check_filetype($file_name);
        
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => $this->generate_image_title($file_name, $image_data),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        // Insert attachment
        $attachment_id = wp_insert_attachment($attachment, $file_path);
        
        if (!$attachment_id) {
            return false;
        }

        // Generate metadata
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id, $attachment_data);

        // Add alt text if available
        if (!empty($image_data['alt'])) {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $image_data['alt']);
        }

        // Add caption if available
        if (!empty($image_data['caption'])) {
            wp_update_post(array(
                'ID' => $attachment_id,
                'post_excerpt' => $image_data['caption']
            ));
        }

        return $attachment_id;
    }

    private function generate_image_title($file_name, $image_data) {
        // Try to use alt text first
        if (!empty($image_data['alt'])) {
            return $image_data['alt'];
        }

        // Clean up filename
        $title = pathinfo($file_name, PATHINFO_FILENAME);
        $title = str_replace(array('-', '_'), ' ', $title);
        $title = ucwords($title);

        return $title;
    }

    public function create_gallery($images, $type = 'grid') {
        if (empty($images)) {
            return '';
        }

        $attachment_ids = array_map(function($img) {
            return $img['attachment_id'];
        }, $images);

        switch ($type) {
            case 'slider':
                return $this->create_slider_gallery($attachment_ids);
            case 'masonry':
                return $this->create_masonry_gallery($attachment_ids);
            case 'grid':
            default:
                return $this->create_grid_gallery($attachment_ids);
        }
    }

    private function create_grid_gallery($attachment_ids) {
        $shortcode = '[gallery ids="' . implode(',', $attachment_ids) . '" ';
        $shortcode .= 'columns="3" size="medium" link="file"]';
        return $shortcode;
    }

    private function create_slider_gallery($attachment_ids) {
        // You could use a slider plugin here
        return $this->create_grid_gallery($attachment_ids);
    }

    private function create_masonry_gallery($attachment_ids) {
        // You could use a masonry plugin here
        return $this->create_grid_gallery($attachment_ids);
    }

    public function get_processed_images() {
        return $this->processed_images;
    }

    public function get_stats() {
        return array(
            'total_processed' => count($this->processed_images),
            'total_size' => array_reduce($this->processed_images, function($carry, $item) {
                return $carry + $item['metadata']['filesize'];
            }, 0),
            'failed_urls' => array_filter($this->processed_images, function($item) {
                return $item === false;
            })
        );
    }
}
