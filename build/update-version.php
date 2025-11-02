#!/usr/bin/env php
<?php
/**
 * Version Update Script for Delete Page Guard Plugin
 *
 * Updates version information across all plugin files.
 * Usage: php build/update-version.php [new-version] [new-date]
 *
 * @license GPL 2 (https://www.gnu.org/licenses/gpl-2.0.html) - see LICENSE.md
 * @author  Johann Duscher <jonny.dee@posteo.net>
 * @copyright 2025 Johann Duscher
 */

$baseDir = dirname(__DIR__);
$versionFile = $baseDir . '/version.php';

// Load current version info
if (!file_exists($versionFile)) {
    die("Error: version.php not found\n");
}

$versionInfo = include $versionFile;
$currentVersion = $versionInfo['version'];
$currentDate = $versionInfo['date'];

// Parse command line arguments
$newVersion = $argv[1] ?? null;
$newDate = $argv[2] ?? date('Y-m-d');

if (!$newVersion) {
    echo "Delete Page Guard Plugin - Version Update Script\n";
    echo "Current version: {$currentVersion} ({$currentDate})\n\n";
    echo "Usage: php build/update-version.php <new-version> [new-date]\n";
    echo "Example: php build/update-version.php 1.1.0 2025-02-01\n";
    exit(1);
}

// Validate version format (semantic versioning)
if (!preg_match('/^\d+\.\d+\.\d+(-[a-zA-Z0-9\-\.]+)?$/', $newVersion)) {
    die("Error: Invalid version format. Use semantic versioning (e.g., 1.0.0)\n");
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $newDate)) {
    die("Error: Invalid date format. Use YYYY-MM-DD\n");
}

echo "Updating version from {$currentVersion} to {$newVersion}\n";
echo "Updating date from {$currentDate} to {$newDate}\n\n";

// Update version.php
$newVersionInfo = $versionInfo;
$newVersionInfo['version'] = $newVersion;
$newVersionInfo['date'] = $newDate;

$versionContent = "<?php\n/**\n * Delete Page Guard Plugin - Version Information\n *\n * Centralized version management for the plugin.\n * This file is used by build scripts to maintain consistent versioning.\n *\n * @license GPL 2 (https://www.gnu.org/licenses/gpl-2.0.html) - see LICENSE.md\n * @author  Johann Duscher <jonny.dee@posteo.net>\n * @copyright 2025 Johann Duscher\n */\n\n// Protect against direct call, but allow build scripts\nif (!defined('DOKU_INC') && php_sapi_name() !== 'cli') die();\n\nreturn " . var_export($newVersionInfo, true) . ";\n";

file_put_contents($versionFile, $versionContent);
echo "✓ Updated version.php\n";

// Update plugin.info.txt
$pluginInfoFile = $baseDir . '/plugin.info.txt';
if (file_exists($pluginInfoFile)) {
    $content = file_get_contents($pluginInfoFile);
    $content = preg_replace('/^version\s+.*$/m', "version {$newVersion}", $content);
    $content = preg_replace('/^date\s+.*$/m', "date    {$newDate}", $content);
    file_put_contents($pluginInfoFile, $content);
    echo "✓ Updated plugin.info.txt\n";
}

// Update CHANGELOG.md header (if it exists and is unreleased)
$changelogFile = $baseDir . '/CHANGELOG.md';
if (file_exists($changelogFile)) {
    $content = file_get_contents($changelogFile);
    // Replace [Unreleased] with actual version
    if (strpos($content, '## [Unreleased]') !== false) {
        $content = str_replace('## [Unreleased]', "## [{$newVersion}] - {$newDate}", $content);
        file_put_contents($changelogFile, $content);
        echo "✓ Updated CHANGELOG.md (marked release)\n";
    } else {
        echo "ℹ CHANGELOG.md - no [Unreleased] section found\n";
    }
}

// Update dokuwiki-plugin-page.txt (plugin template)
$pluginPageFile = $baseDir . '/dokuwiki-plugin-page.txt';
if (file_exists($pluginPageFile)) {
    $content = file_get_contents($pluginPageFile);
    
    // Update lastupdate date
    $content = preg_replace('/^lastupdate\s*:\s*\d{4}-\d{2}-\d{2}\s*$/m', "lastupdate : {$newDate}", $content);
    
    // Update downloadurl with new version (more robust pattern)
    $content = preg_replace(
        '#downloadurl:\s*https://github\.com/jonnydee/deletepageguard/releases/download/v\d+\.\d+\.\d+/deletepageguard-\d+\.\d+\.\d+\.zip#',
        "downloadurl: https://github.com/jonnydee/deletepageguard/releases/download/v{$newVersion}/deletepageguard-{$newVersion}.zip",
        $content
    );
    
    // Update changelog section with new version entry (add at top of changelog)
    $changelogEntry = "  * **{$newDate}**\n    * Release v{$newVersion}\n    * ";
    if (strpos($content, "Release v{$newVersion}") === false) {
        // Find the changelog section and add new entry
        $content = preg_replace(
            '/(===== Changelog =====.*?)(  \* \*\*\d{4}-\d{2}-\d{2}\*\*)/s',
            "\$1{$changelogEntry}See CHANGELOG.md for details\n\n\$2",
            $content
        );
    }
    
    file_put_contents($pluginPageFile, $content);
    echo "✓ Updated dokuwiki-plugin-page.txt (download URL and date)\n";
}

echo "\n✅ Version update completed successfully!\n";
echo "\nNext steps:\n";
echo "1. Review the changes\n";
echo "2. Update CHANGELOG.md if needed\n";
echo "3. Run 'make dist' to create distribution package\n";
echo "4. Commit and tag the release\n";