<?php
/**
 * Status Script - Show current version and git status
 *
 * @license GPL 2 (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author  Johann Duscher <jonny.dee@posteo.net>
 */

// Change to project root directory
chdir(__DIR__ . '/..');

// Load version information
$version_info = include 'version.php';

echo "Current version information:\n";
echo "Version: " . $version_info['version'] . "\n";
echo "Date: " . $version_info['date'] . "\n";
echo "\n";
?>