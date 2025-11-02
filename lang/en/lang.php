<?php
/**
 * English language file for the Delete Page Guard plugin.
 *
 * @license GPL 2 (https://www.gnu.org/licenses/gpl-2.0.html) - see LICENSE.md
 * @author  Johann Duscher <jonny.dee@posteo.net>
 * @copyright 2025 Johann Duscher
 */

$lang['deny_msg'] = 'Deleting this page is not allowed.';

// Configuration Manager labels
$lang['patterns'] = 'Protected page patterns (one PCRE per line). Examples: ^users:[^:]+:start$';
$lang['match_target'] = 'Match against (page ID or file path)';
$lang['exempt_groups'] = 'Exempt groups (comma‑separated)';
$lang['trim_mode'] = 'Treat whitespace‑only pages as empty';

// Legacy configuration keys (for backward compatibility)
$lang['cfg_patterns'] = 'Protected page patterns (one per line, as PCRE)';
$lang['cfg_match_target'] = 'Match against';
$lang['cfg_match_target_o_id'] = 'Page ID (e.g. users:john:start)';
$lang['cfg_match_target_o_filepath'] = 'Relative file path (e.g. users/john/start.txt)';
$lang['cfg_exempt_groups'] = 'Extra groups allowed to delete (comma separated)';
$lang['cfg_trim_mode'] = 'Treat whitespace‑only pages as empty';