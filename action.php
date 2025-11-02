<?php
/**
 * Delete Page Guard for DokuWiki
 *
 * This action plugin prevents the deletion of pages by blocking "empty save"
 * operations on pages whose IDs or file paths match a set of user‑defined
 * regular expressions. Administrators (superusers) and optionally configured
 * exempt groups are allowed to delete pages regardless of these patterns.
 *
 * @license GPL 2 (https://www.gnu.org/licenses/gpl-2.0.html) - see LICENSE.md
 * @author  Johann Duscher <jonny.dee@posteo.net>
 * @copyright 2025 Johann Duscher
 */

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\Event;
use dokuwiki\Extension\EventHandler;

// Protect against direct call
if (!defined('DOKU_INC')) die();

/**
 * Class action_plugin_deletepageguard
 *
 * Registers a handler on COMMON_WIKIPAGE_SAVE to intercept page save
 * operations. When a deletion (empty save) is attempted on a protected page
 * by a non‑admin user, the save is prevented and an error message is shown.
 */
class action_plugin_deletepageguard extends ActionPlugin {

    /**
     * Register the plugin events
     *
     * @param EventHandler $controller
     * @return void
     */
    public function register(EventHandler $controller) {
        // Run before the page is saved so we can abort the delete
        $controller->register_hook('COMMON_WIKIPAGE_SAVE', 'BEFORE', $this, 'handle_common_wikipage_save');
    }

    /**
     * Handler for the COMMON_WIKIPAGE_SAVE event
     *
     * This method checks whether the save operation represents a deletion
     * (i.e. the new content is empty) and whether the page matches one of
     * the configured regular expressions. If so, and the current user is
     * neither an administrator nor in one of the exempt groups, the
     * deletion is prevented.
     *
     * @param Event $event The event object
     * @param mixed $param Additional parameters (unused)
     * @return void
     */
    public function handle_common_wikipage_save(Event $event, $param) {
        global $USERINFO, $conf;

        // Only take action when the event is preventable
        if (!$event->canPreventDefault) {
            return;
        }

        // Allow administrators to delete pages
        if (function_exists('auth_isadmin') && auth_isadmin()) {
            return;
        }

        // Check for exempt groups configuration
        $exemptSetting = (string)$this->getConf('exempt_groups');
        $exemptGroups  = array_filter(array_map('trim', explode(',', $exemptSetting)));

        if (!empty($exemptGroups) && isset($USERINFO['grps']) && is_array($USERINFO['grps'])) {
            foreach ($USERINFO['grps'] as $group) {
                if (in_array($group, $exemptGroups, true)) {
                    // User is in an exempt group, allow deletion
                    return;
                }
            }
        }

        // Determine if the save represents a deletion
        $newContent = isset($event->data['newContent']) ? $event->data['newContent'] : '';
        $trimMode   = (bool)$this->getConf('trim_mode');
        $isEmpty    = $trimMode ? trim($newContent) === '' : $newContent === '';

        if (!$isEmpty) {
            // Not empty – normal edit, allow saving
            return;
        }

        // Determine the matching target: page ID or relative file path
        $matchTarget = $this->getConf('match_target') === 'filepath' ?
            $this->getRelativeFilePath($event->data['file'], $conf['datadir']) :
            $event->data['id'];

        // Retrieve regex patterns from configuration
        $patternsSetting = (string)$this->getConf('patterns');
        $patternLines    = preg_split('/\R+/', $patternsSetting, -1, PREG_SPLIT_NO_EMPTY);

        $hasValidationErrors = false;
        foreach ($patternLines as $lineNumber => $rawPattern) {
            $pattern = trim($rawPattern);
            if ($pattern === '') {
                continue;
            }
            
            // Validate and secure the regex pattern
            $validationResult = $this->validateRegexPattern($pattern, $lineNumber + 1);
            if ($validationResult !== true) {
                // Show validation error to administrators
                if (function_exists('auth_isadmin') && auth_isadmin()) {
                    msg($validationResult, 2); // Warning level
                }
                $hasValidationErrors = true;
                continue;
            }
            
            // Apply the regex with timeout protection
            if ($this->matchesPattern($pattern, $matchTarget)) {
                // Match found – prevent deletion
                $event->preventDefault();
                $event->stopPropagation();
                msg($this->getLang('deny_msg'), -1);
                return;
            }
        }
        
        // If there were validation errors, show a summary message to admins
        if ($hasValidationErrors && function_exists('auth_isadmin') && auth_isadmin()) {
            msg($this->getLang('config_validation_errors'), 2);
        }
    }

    /**
     * Convert an absolute file path into a relative one below the data directory
     *
     * The COMMON_WIKIPAGE_SAVE event provides the absolute file path. When
     * matching against the file path, we want a path relative to the base
     * pages directory so users can write concise regular expressions.
     *
     * @param string $fullPath Absolute path to the file
     * @param string $dataDir  Base data directory (usually $conf['datadir'])
     * @return string Relative file path
     */
    protected function getRelativeFilePath($fullPath, $dataDir) {
        $base = rtrim($dataDir, '/');
        // DokuWiki stores pages in $datadir/pages
        $pagesDir = $base . '/pages/';
        if (strpos($fullPath, $pagesDir) === 0) {
            return substr($fullPath, strlen($pagesDir));
        }
        // Fallback: attempt to strip base datadir
        if (strpos($fullPath, $base . '/') === 0) {
            return substr($fullPath, strlen($base) + 1);
        }
        return $fullPath;
    }

    /**
     * Validate a regular expression pattern for security and correctness
     *
     * Performs basic validation to prevent ReDoS attacks and ensure the
     * pattern is syntactically correct. Returns detailed error messages.
     *
     * @param string $pattern The regex pattern to validate
     * @param int $lineNumber The line number for error reporting
     * @return string|true True if valid, error message string if invalid
     */
    protected function validateRegexPattern($pattern, $lineNumber = 0) {
        $linePrefix = $lineNumber > 0 ? "Line $lineNumber: " : "";
        
        // Check for obviously malicious patterns (basic ReDoS protection)
        if (preg_match('/(\(.*\).*\+.*\(.*\).*\+)|(\(.*\).*\*.*\(.*\).*\*)/', $pattern)) {
            return $linePrefix . sprintf($this->getLang('pattern_redos_warning'), $pattern);
        }

        // Limit pattern length to prevent extremely complex patterns
        if (strlen($pattern) > 1000) {
            return $linePrefix . sprintf($this->getLang('pattern_too_long'), $pattern);
        }

        // Test if the pattern is syntactically valid
        $escapedPattern = '/' . str_replace('/', '\/', $pattern) . '/u';
        $test = @preg_match($escapedPattern, '');
        if ($test === false) {
            $error = error_get_last();
            $errorMsg = $error && isset($error['message']) ? $error['message'] : 'Unknown regex error';
            return $linePrefix . sprintf($this->getLang('pattern_invalid_syntax'), $pattern, $errorMsg);
        }

        return true;
    }

    /**
     * Safely match a pattern against a target string with timeout protection
     *
     * Applies the regex pattern with error handling and basic timeout protection
     * to prevent ReDoS attacks.
     *
     * @param string $pattern The validated regex pattern
     * @param string $target  The string to match against
     * @return bool True if the pattern matches, false otherwise
     */
    protected function matchesPattern($pattern, $target) {
        // Escape forward slashes in pattern to use with / delimiters
        $escapedPattern = '/' . str_replace('/', '\/', $pattern) . '/u';
        
        // Set a reasonable time limit for regex execution (basic ReDoS protection)
        $oldTimeLimit = ini_get('max_execution_time');
        if ($oldTimeLimit > 5) {
            @set_time_limit(5);
        }
        
        $result = @preg_match($escapedPattern, $target);
        
        // Restore original time limit
        if ($oldTimeLimit > 5) {
            @set_time_limit($oldTimeLimit);
        }
        
        return $result === 1;
    }
}
