<?php
class AIAlbiePrompterLibrary {
    public static function get_templates() {
        return array(
            'coding' => array(
                array(
                    'title' => 'API Endpoint',
                    'template' => "Create a [language] API endpoint that:\n- Handles [method] requests\n- Accepts [input_type] as input\n- Returns [output_type]\n- Includes error handling\n- Has proper documentation",
                    'example' => "Create a Python API endpoint that:\n- Handles POST requests\n- Accepts JSON as input\n- Returns user data\n- Includes error handling\n- Has proper documentation"
                ),
                array(
                    'title' => 'Database Query',
                    'template' => "Write a [database_type] query to:\n- Select [fields]\n- From [tables]\n- With [conditions]\n- Optimize for performance\n- Include indexing recommendations",
                    'example' => "Write a MySQL query to:\n- Select user profiles\n- From users and orders tables\n- With recent purchase history\n- Optimize for performance\n- Include indexing recommendations"
                ),
                array(
                    'title' => 'Unit Test',
                    'template' => "Create unit tests for [function_name] that:\n- Test main functionality\n- Cover edge cases\n- Include error scenarios\n- Mock dependencies\n- Follow [framework] conventions",
                    'example' => "Create unit tests for user authentication that:\n- Test login success/failure\n- Cover password requirements\n- Include token expiration\n- Mock database calls\n- Follow Jest conventions"
                ),
                array(
                    'title' => 'Code Refactoring',
                    'template' => "Refactor this [language] code to:\n- Improve readability\n- Enhance performance\n- Follow [standard] conventions\n- Reduce complexity\n- Add proper comments",
                    'example' => "Refactor this Python code to:\n- Improve readability\n- Enhance performance\n- Follow PEP8 conventions\n- Reduce complexity\n- Add proper comments"
                ),
                array(
                    'title' => 'Security Review',
                    'template' => "Review this code for security issues:\n- Check for [vulnerability_type]\n- Identify data exposure risks\n- Validate input handling\n- Verify authentication\n- Suggest improvements",
                    'example' => "Review this code for security issues:\n- Check for SQL injection\n- Identify data exposure risks\n- Validate input handling\n- Verify authentication\n- Suggest improvements"
                )
            ),
            'creative' => array(
                array(
                    'title' => 'Blog Post',
                    'template' => "Write a blog post about [topic] that:\n- Targets [audience]\n- Includes [key_points]\n- Uses [tone]\n- Incorporates SEO keywords\n- Has a compelling call-to-action",
                    'example' => "Write a blog post about AI tools that:\n- Targets small business owners\n- Includes practical applications\n- Uses professional yet friendly tone\n- Incorporates SEO keywords\n- Has a compelling call-to-action"
                ),
                array(
                    'title' => 'Social Media Campaign',
                    'template' => "Create a social media campaign for [brand] that:\n- Spans [platforms]\n- Targets [demographic]\n- Includes visual guidelines\n- Has engagement hooks\n- Measures [metrics]",
                    'example' => "Create a social media campaign for fitness app that:\n- Spans Instagram and TikTok\n- Targets young professionals\n- Includes visual guidelines\n- Has engagement hooks\n- Measures conversion rates"
                ),
                array(
                    'title' => 'Video Script',
                    'template' => "Write a video script for [content_type] that:\n- Hooks viewers in [time] seconds\n- Explains [concept]\n- Uses engaging visuals\n- Includes call-to-action\n- Optimized for [platform]",
                    'example' => "Write a video script for product demo that:\n- Hooks viewers in 5 seconds\n- Explains key features\n- Uses engaging visuals\n- Includes call-to-action\n- Optimized for YouTube"
                ),
                array(
                    'title' => 'Email Sequence',
                    'template' => "Design an email sequence that:\n- Nurtures [audience_type] leads\n- Addresses [pain_points]\n- Builds trust through [methods]\n- Includes social proof\n- Converts to [goal]",
                    'example' => "Design an email sequence that:\n- Nurtures freelancer leads\n- Addresses time management\n- Builds trust through case studies\n- Includes testimonials\n- Converts to course sales"
                )
            ),
            'business' => array(
                array(
                    'title' => 'Pitch Deck',
                    'template' => "Create a pitch deck for [business] that:\n- Highlights market opportunity\n- Shows traction metrics\n- Presents financial projections\n- Outlines team capabilities\n- Includes funding ask",
                    'example' => "Create a pitch deck for SaaS startup that:\n- Highlights $1B market opportunity\n- Shows 20% monthly growth\n- Presents 5-year projections\n- Outlines team expertise\n- Includes $2M seed round ask"
                ),
                array(
                    'title' => 'Business Plan',
                    'template' => "Write a business plan that:\n- Analyzes [market_size]\n- Details [revenue_streams]\n- Outlines operations\n- Includes financial models\n- Addresses risks",
                    'example' => "Write a business plan that:\n- Analyzes e-commerce market\n- Details subscription model\n- Outlines fulfillment\n- Includes 3-year projections\n- Addresses competition"
                ),
                array(
                    'title' => 'Customer Survey',
                    'template' => "Design a survey to:\n- Measure [metrics]\n- Gather [feedback_type]\n- Include rating scales\n- Have open-ended questions\n- Track demographics",
                    'example' => "Design a survey to:\n- Measure satisfaction\n- Gather feature requests\n- Include NPS scale\n- Have feedback section\n- Track user segments"
                ),
                array(
                    'title' => 'Sales Script',
                    'template' => "Create a sales script that:\n- Qualifies [prospect_type]\n- Addresses [objections]\n- Demonstrates [value]\n- Includes social proof\n- Closes effectively",
                    'example' => "Create a sales script that:\n- Qualifies enterprise leads\n- Addresses budget concerns\n- Demonstrates ROI\n- Includes case studies\n- Closes with trial offer"
                )
            ),
            'research' => array(
                array(
                    'title' => 'Market Analysis',
                    'template' => "Analyze [market] focusing on:\n- Size and growth rate\n- Key players and shares\n- Entry barriers\n- Customer segments\n- Future trends",
                    'example' => "Analyze AI software market focusing on:\n- Size and growth rate\n- Key players and shares\n- Entry barriers\n- Customer segments\n- Future trends"
                ),
                array(
                    'title' => 'Competitive Analysis',
                    'template' => "Research competitors:\n- Compare [features]\n- Analyze pricing models\n- Identify strengths/weaknesses\n- Map market positioning\n- Find opportunities",
                    'example' => "Research CRM competitors:\n- Compare core features\n- Analyze subscription tiers\n- Identify unique selling points\n- Map market positioning\n- Find gaps to fill"
                )
            ),
            'technical' => array(
                array(
                    'title' => 'API Documentation',
                    'template' => "Document API endpoint:\n- Describe purpose\n- List parameters\n- Show request/response\n- Include examples\n- Note limitations",
                    'example' => "Document user authentication API:\n- Describe JWT flow\n- List required fields\n- Show login response\n- Include curl example\n- Note rate limits"
                ),
                array(
                    'title' => 'Technical Spec',
                    'template' => "Write technical specification for [feature]:\n- Define requirements\n- Outline architecture\n- List dependencies\n- Include timeline\n- Note risks",
                    'example' => "Write technical specification for payment system:\n- Define security requirements\n- Outline microservices\n- List third-party APIs\n- Include sprint plan\n- Note scaling concerns"
                )
            ),
            'content' => array(
                array(
                    'title' => 'Product Description',
                    'template' => "Write product description for [item]:\n- Hook opening line\n- List key features\n- Address pain points\n- Include specifications\n- Add social proof",
                    'example' => "Write product description for wireless earbuds:\n- Hook about freedom\n- List battery life\n- Address comfort\n- Include tech specs\n- Add user reviews"
                ),
                array(
                    'title' => 'Course Outline',
                    'template' => "Create course outline for [topic]:\n- Define learning outcomes\n- Structure modules\n- Plan exercises\n- Include resources\n- Add assessments",
                    'example' => "Create course outline for Python:\n- Define coding skills\n- Structure basic to advanced\n- Plan coding projects\n- Include documentation\n- Add quizzes"
                )
            ),
            'wordpress_migration' => array(
                array(
                    'title' => 'HTML Content Migration',
                    'template' => "Convert this HTML content to WordPress blocks:\n[html_content]\n\nRequirements:\n- Preserve all formatting\n- Convert images to WordPress media blocks\n- Maintain heading hierarchy\n- Keep all links\n- Preserve text styling",
                    'example' => "Convert this HTML content to WordPress blocks:\n<div class='content'>\n  <h1>Welcome to Our Site</h1>\n  <img src='welcome.jpg' alt='Welcome'>\n  <p>Our <strong>amazing</strong> content...</p>\n</div>\n\nRequirements:\n- Preserve all formatting\n- Convert images to WordPress media blocks\n- Maintain heading hierarchy\n- Keep all links\n- Preserve text styling"
                ),
                array(
                    'title' => 'Image Gallery Migration',
                    'template' => "Convert these HTML images to WordPress gallery:\n[image_html]\n\nRequirements:\n- Create WordPress media library entries\n- Maintain original image order\n- Preserve alt text and captions\n- Use [gallery_type] layout\n- Keep original image sizes",
                    'example' => "Convert these HTML images to WordPress gallery:\n<div class='gallery'>\n  <img src='pic1.jpg' alt='Product 1'>\n  <img src='pic2.jpg' alt='Product 2'>\n</div>\n\nRequirements:\n- Create WordPress media library entries\n- Maintain original image order\n- Preserve alt text and captions\n- Use grid layout\n- Keep original image sizes"
                ),
                array(
                    'title' => 'Navigation Menu Migration',
                    'template' => "Convert this HTML navigation to WordPress menu:\n[menu_html]\n\nRequirements:\n- Create WordPress menu structure\n- Preserve hierarchy\n- Maintain all links\n- Keep custom classes\n- Include mobile responsiveness",
                    'example' => "Convert this HTML navigation to WordPress menu:\n<nav>\n  <ul>\n    <li><a href='/'>Home</a></li>\n    <li><a href='/about'>About</a></li>\n  </ul>\n</nav>\n\nRequirements:\n- Create WordPress menu structure\n- Preserve hierarchy\n- Maintain all links\n- Keep custom classes\n- Include mobile responsiveness"
                ),
                array(
                    'title' => 'Layout Block Migration',
                    'template' => "Convert this HTML layout to WordPress blocks:\n[layout_html]\n\nRequirements:\n- Use WordPress columns/rows\n- Maintain spacing and alignment\n- Preserve responsive behavior\n- Keep background styles\n- Convert to [block_type] blocks",
                    'example' => "Convert this HTML layout to WordPress blocks:\n<div class='row'>\n  <div class='col'>\n    <h2>Left Content</h2>\n  </div>\n  <div class='col'>\n    <img src='right.jpg'>\n  </div>\n</div>\n\nRequirements:\n- Use WordPress columns/rows\n- Maintain spacing and alignment\n- Preserve responsive behavior\n- Keep background styles\n- Convert to Gutenberg blocks"
                ),
                array(
                    'title' => 'Form Migration',
                    'template' => "Convert this HTML form to WordPress:\n[form_html]\n\nRequirements:\n- Use [form_plugin] plugin\n- Maintain all fields\n- Preserve validation rules\n- Keep styling\n- Include form actions",
                    'example' => "Convert this HTML form to WordPress:\n<form>\n  <input type='text' required>\n  <button type='submit'>Send</button>\n</form>\n\nRequirements:\n- Use Contact Form 7 plugin\n- Maintain all fields\n- Preserve validation rules\n- Keep styling\n- Include form actions"
                ),
                array(
                    'title' => 'Full Page Migration',
                    'template' => "Migrate this entire HTML page to WordPress:\n[page_html]\n\nRequirements:\n- Extract main content\n- Identify and migrate images\n- Create WordPress page template\n- Preserve SEO elements\n- Maintain page structure",
                    'example' => "Migrate this entire HTML page to WordPress:\n<!DOCTYPE html>\n<html>\n  <head>...</head>\n  <body>\n    <main>Content</main>\n  </body>\n</html>\n\nRequirements:\n- Extract main content\n- Identify and migrate images\n- Create WordPress page template\n- Preserve SEO elements\n- Maintain page structure"
                )
            ),
            'migration' => array(
                array(
                    'title' => 'Convert HTML to WordPress Blocks',
                    'template' => "Convert this HTML content to WordPress blocks while:
- Preserving exact content and layout
- Maintaining all images in their positions
- Keeping text formatting intact
- Not adding any new styling

HTML Content to Convert:
{content}

Additional Instructions:
- Remove table structures but keep layout
- Preserve image dimensions and positions
- Maintain all text exactly as is
- Create clean, separate blocks for easy editing",
                    'variables' => array('{content}'),
                    'example' => 'Your HTML table content here'
                ),
                array(
                    'title' => 'Organize Navigation Links',
                    'template' => "Analyze and categorize these navigation links:
{links}

Create a structured navigation system that:
- Maintains the original blue button style
- Groups related content
- Preserves all existing links
- Makes the structure logical for users",
                    'variables' => array('{links}'),
                    'example' => 'List of navigation links'
                ),
                array(
                    'title' => 'Categorize Historic Aviation Content',
                    'template' => "Categorize this aviation/military content:
{content}

Create a logical structure that:
- Maintains historical context
- Groups related items
- Preserves chronological order
- Makes content easily findable",
                    'variables' => array('{content}'),
                    'example' => 'Your aviation/military content'
                )
            )
        );
    }

    public static function analyze_prompt($prompt) {
        $scores = array(
            'clarity' => 0,
            'specificity' => 0,
            'context' => 0
        );

        // Analyze clarity
        $scores['clarity'] = self::analyze_clarity($prompt);
        
        // Analyze specificity
        $scores['specificity'] = self::analyze_specificity($prompt);
        
        // Analyze context
        $scores['context'] = self::analyze_context($prompt);

        return array(
            'scores' => $scores,
            'tips' => self::generate_tips($scores)
        );
    }

    private static function analyze_clarity($prompt) {
        $score = 0;
        
        // Check for clear sentence structure
        if (str_contains($prompt, '.')) $score += 2;
        
        // Check for proper formatting
        if (str_contains($prompt, "\n")) $score += 2;
        
        // Check for reasonable length
        if (strlen($prompt) > 50 && strlen($prompt) < 500) $score += 1;

        return min($score, 5);
    }

    private static function analyze_specificity($prompt) {
        $score = 0;
        
        // Check for numbers and specific data
        if (preg_match('/\d+/', $prompt)) $score += 1;
        
        // Check for detailed requirements
        if (str_contains($prompt, 'must') || str_contains($prompt, 'should')) $score += 2;
        
        // Check for examples
        if (str_contains(strtolower($prompt), 'example')) $score += 2;

        return min($score, 5);
    }

    private static function analyze_context($prompt) {
        $score = 0;
        
        // Check for background information
        if (strlen($prompt) > 100) $score += 1;
        
        // Check for purpose/goal
        if (str_contains(strtolower($prompt), 'goal') || str_contains(strtolower($prompt), 'purpose')) $score += 2;
        
        // Check for target audience
        if (str_contains(strtolower($prompt), 'for') || str_contains(strtolower($prompt), 'audience')) $score += 2;

        return min($score, 5);
    }

    private static function generate_tips($scores) {
        $tips = array();
        
        if ($scores['clarity'] < 3) {
            $tips[] = "Add more structure to your prompt using bullet points or numbered lists";
        }
        
        if ($scores['specificity'] < 3) {
            $tips[] = "Include specific requirements and examples of what you're looking for";
        }
        
        if ($scores['context'] < 3) {
            $tips[] = "Provide more background information and clearly state your goal";
        }

        return $tips;
    }

    public static function get_migration_tips() {
        return array(
            'content' => array(
                'Identify the main content container in your HTML',
                'Look for common content wrappers like <main>, <article>, or <div class="content">',
                'Extract images before content for proper media library integration',
                'Preserve heading hierarchy (h1, h2, etc.) for SEO'
            ),
            'images' => array(
                'Download all images to local directory first',
                'Check for and maintain image alt text',
                'Look for image captions in surrounding elements',
                'Verify image paths are correct'
            ),
            'structure' => array(
                'Map HTML elements to appropriate WordPress blocks',
                'Maintain content hierarchy and relationships',
                'Preserve custom CSS classes where needed',
                'Check responsive behavior after migration'
            )
        );
    }
}
