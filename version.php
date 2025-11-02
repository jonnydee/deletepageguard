<?php
/**
 * Delete Page Guard Plugin - Version Information
 *
 * Centralized version management for the plugin.
 * This file is used by build scripts to maintain consistent versioning.
 *
 * @license GPL 2 (https://www.gnu.org/licenses/gpl-2.0.html) - see LICENSE.md
 * @author  Johann Duscher <jonny.dee@posteo.net>
 * @copyright 2025 Johann Duscher
 */

// Protect against direct call, but allow build scripts
if (!defined('DOKU_INC') && php_sapi_name() !== 'cli') die();

return array (
  'version' => '1.0.0',
  'date' => '2025-11-02',
  'name' => 'Delete Page Guard',
  'author' => 'Johann Duscher',
  'email' => 'jonny.dee@posteo.net',
  'url' => 'https://github.com/jonnydee/deletepageguard',
);
