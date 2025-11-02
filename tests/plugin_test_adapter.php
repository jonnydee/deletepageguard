<?php
/**
 * Test Adapter for Delete Page Guard Plugin
 *
 * This file provides a testable version of the plugin by extending the
 * actual plugin class and making protected methods accessible for testing.
 *
 * @license GPL 2 (https://www.gnu.org/licenses/gpl-2.0.html) - see LICENSE.md
 * @author  Johann Duscher <jonny.dee@posteo.net>
 * @copyright 2025 Johann Duscher
 */

// Mock the DokuWiki ActionPlugin class for testing
if (!class_exists('dokuwiki\Extension\ActionPlugin')) {
    class ActionPlugin {
        protected $config = [
            'patterns' => "^start$\n^sidebar$\n^users:[^:]+:start$",
            'match_target' => 'id',
            'exempt_groups' => 'editors,moderators',
            'trim_mode' => true
        ];
        
        protected $lang = [
            'pattern_redos_warning' => 'Pattern "%s" may cause performance issues',
            'pattern_too_long' => 'Pattern "%s" is too long (max 1000 chars)',
            'pattern_invalid_syntax' => 'Pattern "%s" has invalid syntax: %s',
            'deny_msg' => 'Deleting this page is not allowed.',
            'config_validation_errors' => 'Some regex patterns have validation errors.'
        ];
        
        public function getConf($key) {
            return isset($this->config[$key]) ? $this->config[$key] : null;
        }
        
        public function getLang($key) {
            return isset($this->lang[$key]) ? $this->lang[$key] : "[$key]";
        }
    }
    
    // Create the namespace alias
    class_alias('ActionPlugin', 'dokuwiki\Extension\ActionPlugin');
}

// Mock DokuWiki Event classes
if (!class_exists('dokuwiki\Extension\Event')) {
    class Event {
        public $data = [];
        public $canPreventDefault = true;
    }
    class_alias('Event', 'dokuwiki\Extension\Event');
}

if (!class_exists('dokuwiki\Extension\EventHandler')) {
    class EventHandler {
        public function register_hook($event, $when, $obj, $method) {
            // Mock implementation
        }
    }
    class_alias('EventHandler', 'dokuwiki\Extension\EventHandler');
}

// Mock DokuWiki constants and functions
if (!defined('DOKU_INC')) define('DOKU_INC', dirname(__DIR__) . '/');

// Include the actual plugin file
require_once dirname(__DIR__) . '/action.php';

/**
 * Testable version of the Delete Page Guard plugin
 *
 * Extends the actual plugin class and exposes protected methods for testing.
 */
class TestableDeletePageGuard extends action_plugin_deletepageguard {
    
    /**
     * Override getConf to use mock configuration
     */
    public function getConf($key) {
        $config = [
            'patterns' => "^start$\n^sidebar$\n^users:[^:]+:start$",
            'match_target' => 'id',
            'exempt_groups' => 'editors,moderators',
            'trim_mode' => true
        ];
        return isset($config[$key]) ? $config[$key] : null;
    }
    
    /**
     * Override getLang to use mock language strings
     */
    public function getLang($key) {
        $lang = [
            'pattern_redos_warning' => 'Pattern "%s" may cause performance issues',
            'pattern_too_long' => 'Pattern "%s" is too long (max 1000 chars)',
            'pattern_invalid_syntax' => 'Pattern "%s" has invalid syntax: %s',
            'deny_msg' => 'Deleting this page is not allowed.',
            'config_validation_errors' => 'Some regex patterns have validation errors.'
        ];
        return isset($lang[$key]) ? $lang[$key] : "[$key]";
    }
    
    /**
     * Expose protected validateRegexPattern method for testing
     */
    public function validateRegexPattern($pattern, $lineNumber = 0) {
        return parent::validateRegexPattern($pattern, $lineNumber);
    }
    
    /**
     * Expose protected matchesPattern method for testing
     */
    public function matchesPattern($pattern, $target) {
        return parent::matchesPattern($pattern, $target);
    }
    
    /**
     * Expose protected getRelativeFilePath method for testing
     */
    public function getRelativeFilePath($fullPath, $dataDir) {
        return parent::getRelativeFilePath($fullPath, $dataDir);
    }
}