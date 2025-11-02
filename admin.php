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
     * Cached instance of the action plugin
     * @var action_plugin_deletepageguard|null
     */
    private $actionPlugin = null;

    /**
     * Get the action plugin instance (cached)
     * @return action_plugin_deletepageguard|null
     */
    private function getActionPlugin() {
        if ($this->actionPlugin === null) {
            $this->actionPlugin = plugin_load('action', 'deletepageguard');
        }
        return $this->actionPlugin;
    }

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
        
        // Show validation results if "Validate" button was clicked
        if (isset($_POST['validate_patterns'])) {
            echo '<h2>' . $this->getLang('validation_results_title') . '</h2>';
            $this->showPatternValidation($patterns);
        } 
        // Show matching pages if "Show Matches" button was clicked
        elseif (isset($_POST['show_matches'])) {
            echo '<h2>' . $this->getLang('validation_results_title') . '</h2>';
            $this->showPatternValidation($patterns);
            echo '<h2>' . $this->getLang('matching_pages_title') . '</h2>';
            $this->showMatchingPages($patterns);
        } 
        // Initial load - just show validation
        else {
            $this->showPatternValidation($patterns);
        }
        
        // Add validation form
        echo '<h2>' . $this->getLang('test_patterns_title') . '</h2>';
        echo '<form method="post" accept-charset="utf-8">';
        echo '<p>' . $this->getLang('test_patterns_help') . '</p>';
        echo '<textarea name="test_patterns" rows="10" cols="80" class="edit">' . hsc($patterns) . '</textarea><br>';
        echo '<input type="submit" name="validate_patterns" value="' . $this->getLang('validate_button') . '" class="button"> ';
        echo '<input type="submit" name="show_matches" value="' . $this->getLang('show_matches_button') . '" class="button">';
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
        $actionPlugin = $this->getActionPlugin();
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

    /**
     * Show all wiki pages that match the given patterns
     * @param string $patterns The patterns to test
     * @return void
     */
    private function showMatchingPages($patterns) {
        // Load action plugin for matching logic
        $actionPlugin = $this->getActionPlugin();
        if (!$actionPlugin) {
            echo '<div class="error">Error: Could not load action plugin</div>';
            return;
        }
        
        // Parse patterns
        $lines = preg_split('/\R+/', $patterns, -1, PREG_SPLIT_NO_EMPTY);
        $validPatterns = [];
        
        foreach ($lines as $line) {
            $pattern = trim($line);
            if ($pattern === '') continue;
            
            // Only use valid patterns
            if ($actionPlugin->validateRegexPattern($pattern, 0) === true) {
                $validPatterns[] = $pattern;
            }
        }
        
        if (empty($validPatterns)) {
            echo '<div class="info">' . $this->getLang('no_valid_patterns') . '</div>';
            return;
        }
        
        // Get all pages using DokuWiki's search function
        global $conf;
        $allPages = [];
        
        // DokuWiki's search expects to search in the pages directory
        $pagesDir = $conf['datadir'] . '/pages';
        search($allPages, $pagesDir, 'search_allpages', []);
        
        // Fallback: use indexer if search returns nothing
        if (empty($allPages)) {
            require_once(DOKU_INC . 'inc/indexer.php');
            $indexer = idx_get_indexer();
            $pagesList = $indexer->getPages();
            
            // Convert simple page list to expected format
            if (!empty($pagesList)) {
                $allPages = [];
                foreach ($pagesList as $pageId) {
                    $allPages[] = ['id' => $pageId];
                }
            }
        }
        
        if (empty($allPages)) {
            echo '<div class="info">' . $this->getLang('no_pages_found') . '</div>';
            return;
        }
        
        // Test each page against patterns
        $matchedPages = [];
        $testedCount = 0;
        foreach ($allPages as $page) {
            $pageId = $page['id'];
            $matchTarget = $actionPlugin->getMatchTarget($pageId);
            $testedCount++;
            
            foreach ($validPatterns as $pattern) {
                if ($actionPlugin->matchesPattern($pattern, $matchTarget)) {
                    $matchedPages[] = [
                        'id' => $pageId,
                        'target' => $matchTarget,
                        'pattern' => $pattern
                    ];
                    break; // Only list each page once
                }
            }
        }
        
        // Display results
        echo '<div class="level2">';
        
        if (empty($matchedPages)) {
            echo '<div class="info">' . sprintf($this->getLang('no_matching_pages'), count($allPages)) . '</div>';
        } else {
            echo '<p>' . sprintf($this->getLang('found_matching_pages'), count($matchedPages), count($allPages)) . '</p>';
            echo '<table class="inline">';
            echo '<thead><tr>';
            echo '<th>' . $this->getLang('page_id') . '</th>';
            echo '<th>' . $this->getLang('match_target') . '</th>';
            echo '<th>' . $this->getLang('matched_pattern') . '</th>';
            echo '</tr></thead>';
            echo '<tbody>';
            
            foreach ($matchedPages as $match) {
                echo '<tr>';
                echo '<td><a href="' . wl($match['id']) . '">' . hsc($match['id']) . '</a></td>';
                echo '<td><code>' . hsc($match['target']) . '</code></td>';
                echo '<td><code>' . hsc($match['pattern']) . '</code></td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
        }
        
        echo '</div>';
    }
}
