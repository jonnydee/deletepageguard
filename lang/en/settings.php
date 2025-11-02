<?php
/**
 * English language settings file for the Delete Page Guard plugin
 *
 * These strings are used by DokuWiki’s Configuration Manager to label the
 * configuration options for this plugin and to provide helpful descriptions.
 * Each key corresponds to a setting defined in conf/default.php. When
 * translated into other languages, the keys should remain the same while
 * the values are translated.
 */

/*
 * patterns
 *
 * Provide one Perl‑compatible regular expression per line. A page will be
 * considered “protected” if its ID or relative file path matches any of the
 * configured patterns (depending on the “match against” option). When a
 * non‑admin user attempts to save a protected page with empty content, the
 * save is blocked and the page is not deleted. Example:
 *
 *   ^users:[^:]+:start$
 *
 * This pattern protects pages like “users:alice:start” and
 * “users:bob:start”.
 */
$lang['patterns'] = 'Protected page patterns (one PCRE per line). Examples: ^users:[^:]+:start$';

/*
 * match_target
 *
 * Determines which value is tested against the regular expressions above.
 * Choose “id” to match against the page ID (e.g. users:alice:start) or
 * “filepath” to match against the relative file system path below the
 * pages directory (e.g. users/alice/start.txt). Use the file path if your
 * patterns are easier to express in that form.
 */
$lang['match_target'] = 'Match against (page ID or file path)';

/*
 * exempt_groups
 *
 * Comma‑separated list of additional group names whose members are allowed
 * to delete protected pages (in addition to administrators). The group
 * names should not include the leading “@”. Example: managers,editors
 */
$lang['exempt_groups'] = 'Exempt groups (comma‑separated)';

/*
 * trim_mode
 *
 * When enabled, pages containing only whitespace (spaces, tabs or
 * newlines) are treated as empty and deletion is blocked. When disabled,
 * whitespace‑only pages are considered non‑empty and will not trigger the
 * delete guard.
 */
$lang['trim_mode'] = 'Treat whitespace‑only pages as empty';