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

        foreach ($patternLines as $rawPattern) {
            $pattern = trim($rawPattern);
            if ($pattern === '') {
                continue;
            }
            
            // Validate and secure the regex pattern
            if (!$this->validateRegexPattern($pattern)) {
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
     * pattern is syntactically correct. Logs warnings for invalid patterns.
     *
     * @param string $pattern The regex pattern to validate
     * @return bool True if the pattern is valid and safe, false otherwise
     */
    protected function validateRegexPattern($pattern) {
        // Check for obviously malicious patterns (basic ReDoS protection)
        if (preg_match('/(\(.*\).*\+.*\(.*\).*\+)|(\(.*\).*\*.*\(.*\).*\*)/', $pattern)) {
            // Pattern looks like it could cause catastrophic backtracking
            return false;
        }

        // Limit pattern length to prevent extremely complex patterns
        if (strlen($pattern) > 1000) {
            return false;
        }

        // Test if the pattern is syntactically valid
        $test = @preg_match('/' . str_replace('/', '\/', $pattern) . '/u', '');
        if ($test === false) {
            return false;
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
