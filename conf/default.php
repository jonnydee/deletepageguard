<?php
/**
 * Default configuration for the Delete Guard plugin
 *
 * Each key defined here will be used as a default when the plugin is
 * installed. Administrators can override these values via the
 * Configuration Manager. See metadata.php for descriptions.
 */

// Newline separated list of PCRE regular expressions. A page whose ID
// (or relative file path, depending on match_target) matches any of
// these patterns cannot be deleted by empty save.
$conf['patterns'] = '';

// Which attribute to match against: 'id' matches against the page ID
// (colon separated), 'filepath' matches against the relative file path
// below the data directory. Default is 'id'.
$conf['match_target'] = 'id';

// Comma separated list of additional user groups that may delete
// protected pages. Administrators always bypass the block. Example:
// 'manager,editor'
$conf['exempt_groups'] = '';

// When true, whitespace‑only content is treated as empty. When false,
// only truly empty strings trigger deletion. Default is on.
$conf['trim_mode'] = 1;