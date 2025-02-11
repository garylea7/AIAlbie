<?php
class AIAlbiePrompterPlaceholders {
    public static function get_common_placeholders() {
        return array(
            'language' => array(
                'description' => 'Programming language to use',
                'examples' => ['Python', 'JavaScript', 'PHP', 'Java']
            ),
            'method' => array(
                'description' => 'HTTP method for API endpoints',
                'examples' => ['GET', 'POST', 'PUT', 'DELETE']
            ),
            'brand' => array(
                'description' => 'Company or product name',
                'examples' => ['Your Company', 'Product Name', 'Service Brand']
            ),
            'platforms' => array(
                'description' => 'Social media or marketing platforms',
                'examples' => ['Instagram & TikTok', 'LinkedIn', 'Facebook & Twitter']
            ),
            'demographic' => array(
                'description' => 'Target audience description',
                'examples' => ['Young Professionals', 'Small Business Owners', 'Parents']
            ),
            'market_size' => array(
                'description' => 'Total addressable market size',
                'examples' => ['$1B', '$500M annually', 'Growing Healthcare Market']
            ),
            'metrics' => array(
                'description' => 'Key performance indicators',
                'examples' => ['Conversion Rate', 'User Engagement', 'Sales Growth']
            ),
            'pain_points' => array(
                'description' => 'Customer problems to address',
                'examples' => ['Time Management', 'Cost Efficiency', 'Technical Complexity']
            ),
            'features' => array(
                'description' => 'Product or service capabilities',
                'examples' => ['Core Features', 'Premium Features', 'Integration Options']
            ),
            'timeline' => array(
                'description' => 'Project or implementation duration',
                'examples' => ['Q1 2025', '6 Months', '2 Week Sprint']
            )
        );
    }

    public static function get_category_placeholders($category) {
        $category_specific = array(
            'coding' => array(
                'framework' => array(
                    'description' => 'Testing or development framework',
                    'examples' => ['Jest', 'PyTest', 'JUnit']
                ),
                'vulnerability_type' => array(
                    'description' => 'Security vulnerability to check',
                    'examples' => ['SQL Injection', 'XSS', 'CSRF']
                )
            ),
            'business' => array(
                'revenue_streams' => array(
                    'description' => 'Sources of income',
                    'examples' => ['Subscription', 'Transaction Fees', 'Advertising']
                ),
                'funding_ask' => array(
                    'description' => 'Investment request amount',
                    'examples' => ['$2M Seed Round', '$5M Series A', 'Angel Investment']
                )
            ),
            'creative' => array(
                'tone' => array(
                    'description' => 'Content writing style',
                    'examples' => ['Professional', 'Casual', 'Technical']
                ),
                'content_type' => array(
                    'description' => 'Type of creative content',
                    'examples' => ['Product Demo', 'Tutorial', 'Brand Story']
                )
            )
        );

        return isset($category_specific[$category]) 
            ? $category_specific[$category] 
            : array();
    }

    public static function render_placeholder_help($template) {
        $placeholders = self::extract_placeholders($template);
        $common = self::get_common_placeholders();
        $html = '<div class="placeholder-help">';
        
        foreach ($placeholders as $placeholder) {
            if (isset($common[$placeholder])) {
                $help = $common[$placeholder];
                $html .= sprintf(
                    '<div class="placeholder-item">
                        <strong>[%s]:</strong> %s<br>
                        <em>Examples:</em> %s
                    </div>',
                    $placeholder,
                    $help['description'],
                    implode(', ', $help['examples'])
                );
            }
        }
        
        $html .= '</div>';
        return $html;
    }

    private static function extract_placeholders($template) {
        preg_match_all('/\[(.*?)\]/', $template, $matches);
        return $matches[1];
    }
}
