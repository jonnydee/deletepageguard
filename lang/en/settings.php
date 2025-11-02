<?php
/**
 * English language file for Delete Page Guard Plugin - Configuration Settings
 *
 * @license GPL 2 (https://www.gnu.org/licenses/gpl-2.0.html) - see LICENSE.md
 * @author  Johann Duscher <jonny.dee@posteo.net>
 * @copyright 2025 Johann Duscher
 */

// Protect against direct call
if (!defined('DOKU_INC')) die();

// Configuration Manager setting descriptions
$lang['patterns'] = 'Regular expression patterns to protect pages from deletion (one pattern per line). Pages matching any pattern cannot be deleted via empty save.<br><br>Examples:<br><code>^start$</code> — protects the main start page<br><code>^sidebar$</code> — protects sidebar pages<br><code>^users:[^:]+:start$</code> — protects user start pages<br><code>^wiki:(syntax|dokuwiki)$</code> — protects specific wiki pages';

$lang['match_target'] = 'What to match patterns against: page ID (e.g., <code>wiki:syntax</code>) or file path (e.g., <code>wiki/syntax.txt</code>)';

$lang['exempt_groups'] = 'User groups that can delete protected pages, in addition to administrators (comma-separated). Leave empty to allow only administrators.<br>Example: <code>manager,editor</code>';

$lang['trim_mode'] = 'Treat pages containing only whitespace as empty and subject to deletion protection';
