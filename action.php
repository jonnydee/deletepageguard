<?php
/**
 * Delete Page Guard for DokuWiki
 *
 * This action plugin prevents the deletion of pages by blocking "empty save"
 * operations on pages whose IDs or file paths match a set of user‑defined
 * regular expressions. Administrators (superusers) and optionally configured
 * exempt groups are allowed to delete pages regardless of these patterns.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Johann Duscher <jonny.dee@posteo.net>
 */

use dokuwiki\Extension\ActionPlugin;
// Import the correct namespaced classes for event handling
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
     * @param Event      $event The event object
     * @param mixed      $param Additional parameters (unused)
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
            // Try to apply the regex; invalid patterns are ignored
            if (@preg_match('/' . $pattern . '/u', '') === false) {
                continue;
            }
            if (preg_match('/' . $pattern . '/u', $matchTarget)) {
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
}