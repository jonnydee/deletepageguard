<?php
/**
 * Admin interface for Delete Page Guard pattern validation
 *
 * @license GPL 2 (https://www.gnu.org/licenses/gpl-2.0.html) - see LICENSE.md
 * @author  Johann Duscher <jonny.dee@posteo.net>
 * @copyright 2025 Johann Duscher
 */

use dokuwiki\Extension\AdminPlugin;

// Protect against direct call
if (!defined('DOKU_INC')) die();

/**
 * Class admin_plugin_deletepageguard
 *
 * Provides an admin interface for validating Delete Page Guard patterns
 * and offering configuration guidance to administrators.
 */
class admin_plugin_deletepageguard extends AdminPlugin {

    /**
     * Return sort order for position in admin menu
     * @return int
     */
    public function getMenuSort() {
        return 200;
    }

    /**
     * Return the text to display in the admin menu
     * @return string
     */
    public function getMenuText($language) {
        return $this->getLang('menu');
    }

    /**
     * Return true if access to this admin plugin is allowed
     * @return bool
     */
    public function forAdminOnly() {
        return true;
    }

    /**
     * Handle user request
     * @return void
     */
    public function handle() {
        // Nothing to handle - validation is done in html() method
    }

    /**
     * Render HTML output
     * @return void
     */
    public function html() {
        echo '<h1>' . $this->getLang('admin_title') . '</h1>';
        echo '<div class="level1">';
        
        // Determine which patterns to show - use POST data if available, otherwise config
        $patterns = $_POST['test_patterns'] ?? $this->getConf('patterns');
        
        // Show validation results if form was submitted
        if (isset($_POST['validate_patterns'])) {
            echo '<h2>' . $this->getLang('validation_results_title') . '</h2>';
            $this->showPatternValidation($patterns);
        } else {
            // Show current config patterns on initial load
            $this->showPatternValidation($patterns);
        }
        
        // Add validation form
        echo '<h2>' . $this->getLang('test_patterns_title') . '</h2>';
        echo '<form method="post" accept-charset="utf-8">';
        echo '<p>' . $this->getLang('test_patterns_help') . '</p>';
        echo '<textarea name="test_patterns" rows="10" cols="80" class="edit">' . hsc($patterns) . '</textarea><br>';
        echo '<input type="submit" name="validate_patterns" value="' . $this->getLang('validate_button') . '" class="button">';
        echo '</form>';
        
        echo '</div>';
    }

    /**
     * Display pattern validation results
     * @param string $patterns The patterns to validate
     * @return void
     */
    private function showPatternValidation($patterns) {
        if (empty(trim($patterns))) {
            echo '<div class="info">' . $this->getLang('no_patterns') . '</div>';
            return;
        }
        
        $lines = preg_split('/\R+/', $patterns, -1, PREG_SPLIT_NO_EMPTY);
        $hasErrors = false;
        $validCount = 0;
        
        echo '<div class="level2">';
        echo '<h3>' . $this->getLang('validation_results') . '</h3>';
        echo '<ul>';
        
        foreach ($lines as $i => $line) {
            $pattern = trim($line);
            if ($pattern === '') continue;
            
            $lineNum = $i + 1;
            $status = $this->validateSinglePattern($pattern);
            
            if ($status === true) {
                echo '<li><span style="color: green; font-weight: bold;">✓</span> ';
                echo '<strong>Line ' . $lineNum . ':</strong> <code>' . hsc($pattern) . '</code></li>';
                $validCount++;
            } else {
                echo '<li><span style="color: red; font-weight: bold;">✗</span> ';
                echo '<strong>Line ' . $lineNum . ':</strong> <code>' . hsc($pattern) . '</code><br>';
                echo '&nbsp;&nbsp;&nbsp;<em style="color: red;">' . hsc($status) . '</em></li>';
                $hasErrors = true;
            }
        }
        
        echo '</ul>';
        
        if (!$hasErrors && $validCount > 0) {
            echo '<div class="success">' . sprintf($this->getLang('all_patterns_valid'), $validCount) . '</div>';
        } elseif ($hasErrors) {
            echo '<div class="error">' . $this->getLang('some_patterns_invalid') . '</div>';
        }
        
        echo '</div>';
    }

    /**
     * Validate a single pattern by delegating to the action plugin's validator.
     * This ensures consistent validation logic between admin UI and runtime checks.
     * 
     * @param string $pattern The pattern to validate
     * @return string|true True if valid, error message if invalid
     */
    private function validateSinglePattern($pattern) {
        // Load the action plugin to use its centralized validation logic
        $actionPlugin = plugin_load('action', 'deletepageguard');
        if (!$actionPlugin) {
            return 'Error: Could not load validation service';
        }
        
        // Use the action plugin's validateRegexPattern method (without line number)
        $result = $actionPlugin->validateRegexPattern($pattern, 0);
        
        // The action plugin returns true for valid, string for invalid
        // We need to strip the "Line 0: " prefix if present
        if (is_string($result)) {
            $result = preg_replace('/^Line 0: /', '', $result);
        }
        
        return $result;
    }
}