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

// Pattern validation messages for administrators
$lang['pattern_redos_warning'] = 'Pattern "%s" may cause performance issues (potential ReDoS attack)';
$lang['pattern_too_long'] = 'Pattern "%s" is too long (maximum 1000 characters allowed)';
$lang['pattern_invalid_syntax'] = 'Pattern "%s" has invalid regex syntax: %s';
$lang['config_validation_errors'] = 'Some regex patterns in Delete Page Guard configuration have validation errors. Please check the configuration.';

// Admin interface
$lang['menu'] = 'Delete Page Guard';
$lang['admin_title'] = 'Delete Page Guard - Pattern Validation';
$lang['test_patterns_title'] = 'Test Pattern Configuration';
$lang['test_patterns_help'] = 'Enter one regular expression pattern per line to validate them. This helps you test patterns before saving them to the configuration.';
$lang['validate_button'] = 'Validate Patterns';
$lang['validation_results_title'] = 'Validation Results';
$lang['validation_results'] = 'Pattern Validation Results';
$lang['no_patterns'] = 'No patterns to validate.';
$lang['all_patterns_valid'] = 'All %d patterns are valid!';
$lang['some_patterns_invalid'] = 'Some patterns have issues. Please fix them before using.';

// Detailed error messages for admin interface
$lang['error_pattern_too_long'] = 'Pattern is too long (maximum 1000 characters allowed)';
$lang['error_pattern_redos'] = 'Pattern may cause performance issues (potential ReDoS attack)';
$lang['error_pattern_syntax'] = 'Invalid regular expression syntax';

// Legacy configuration keys (for backward compatibility)
$lang['cfg_patterns'] = 'Protected page patterns (one per line, as PCRE)';
$lang['cfg_match_target'] = 'Match against';
$lang['cfg_match_target_o_id'] = 'Page ID (e.g. users:john:start)';
$lang['cfg_match_target_o_filepath'] = 'Relative file path (e.g. users/john/start.txt)';
$lang['cfg_exempt_groups'] = 'Extra groups allowed to delete (comma separated)';
$lang['cfg_trim_mode'] = 'Treat whitespace‑only pages as empty';