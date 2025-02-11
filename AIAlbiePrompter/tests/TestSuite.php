<?php
namespace AIAlbie\Tests;

use PHPUnit\Framework\TestCase;

class TestSuite extends TestCase {
    protected $marketplace;
    protected $visual;
    protected $voice;
    protected $customization;

    protected function setUp(): void {
        // Initialize test environment
        $this->marketplace = new \AIAlbieMarketplaceEngine();
        $this->visual = new \AIAlbieVisualEngine();
        $this->voice = new \AIAlbieVoiceEngine();
        $this->customization = new \AIAlbieCustomizationEngine();
    }

    public function testMarketplaceFeatures() {
        // Test agent creation
        $agent_data = [
            'name' => 'Test Agent',
            'description' => 'Test Description',
            'capabilities' => ['test_capability'],
            'pricing' => ['type' => 'free']
        ];
        $agent_id = $this->marketplace->create_ai_agent($agent_data);
        $this->assertNotNull($agent_id);

        // Test agent deployment
        $deployment = $this->marketplace->deploy_agent_for_user($agent_id, 1);
        $this->assertTrue($deployment > 0);

        // Test marketplace analytics
        $analytics = $this->marketplace->analyze_marketplace_performance();
        $this->assertArrayHasKey('insights', $analytics);
    }

    public function testVisualFeatures() {
        // Test screenshot analysis
        $screenshot_data = 'test_screenshot_data';
        $analysis = $this->visual->analyze_screenshot($screenshot_data);
        $this->assertArrayHasKey('ui_elements', $analysis);

        // Test OCR
        $ocr_result = $this->visual->perform_ocr($screenshot_data);
        $this->assertNotNull($ocr_result);

        // Test UI detection
        $elements = $this->visual->detect_ui_elements($screenshot_data);
        $this->assertArrayHasKey('basic', $elements);
    }

    public function testVoiceFeatures() {
        // Test command processing
        $command_data = [
            'text' => 'test command',
            'context' => []
        ];
        $result = $this->voice->process_voice_command($command_data);
        $this->assertNotNull($result);

        // Test chat handling
        $message_data = [
            'audio' => 'test_audio_data',
            'context' => []
        ];
        $response = $this->voice->handle_voice_chat($message_data);
        $this->assertNotNull($response);

        // Test voice notes
        $note_data = [
            'audio' => 'test_audio_data',
            'user_id' => 1
        ];
        $note = $this->voice->create_voice_note($note_data);
        $this->assertNotNull($note);
    }

    public function testCustomizationFeatures() {
        // Test personality profiles
        $profile_data = [
            'name' => 'Test Profile',
            'traits' => ['friendly', 'helpful'],
            'communication_style' => 'casual'
        ];
        $profile_id = $this->customization->create_personality_profile($profile_data);
        $this->assertNotNull($profile_id);

        // Test learning preferences
        $preferences = [
            'style' => 'visual',
            'pace' => 'moderate',
            'feedback' => 'detailed'
        ];
        $result = $this->customization->customize_learning_preferences(1, $preferences);
        $this->assertNotNull($result);

        // Test interaction styles
        $style_data = [
            'name' => 'Test Style',
            'rules' => ['rule1', 'rule2'],
            'context_handling' => ['type' => 'adaptive']
        ];
        $style_id = $this->customization->create_interaction_style($style_data);
        $this->assertNotNull($style_id);
    }

    public function testIntegration() {
        // Test marketplace and customization integration
        $agent_id = $this->marketplace->create_ai_agent([
            'name' => 'Test Agent',
            'description' => 'Test Description',
            'capabilities' => ['test_capability'],
            'pricing' => ['type' => 'free']
        ]);

        $customization_data = [
            'personality' => ['friendly', 'helpful'],
            'learning' => ['style' => 'visual'],
            'interaction' => ['style' => 'casual']
        ];

        $result = $this->customization->apply_customization($agent_id, $customization_data);
        $this->assertTrue($result > 0);

        // Test voice and visual integration
        $screenshot_data = 'test_screenshot_data';
        $visual_analysis = $this->visual->analyze_screenshot($screenshot_data);

        $voice_command = [
            'text' => 'analyze screenshot',
            'context' => $visual_analysis
        ];

        $result = $this->voice->process_voice_command($voice_command);
        $this->assertNotNull($result);
    }
}
