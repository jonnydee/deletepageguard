<?php
/**
 * Distribution Creator for Delete Page Guard Plugin
 *
 * Creates a distribution directory for DokuWiki plugin installation.
 * You can manually ZIP this directory later for upload.
 *
 * @license GPL 2 (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author  Johann Duscher <jonny.dee@posteo.net>
 */

// Change to project root directory
chdir(__DIR__ . '/..');

// Load version information
$version_info = include 'version.php';
$version = $version_info['version'];
$distDir = 'dist/deletepageguard-' . $version;
$pluginDirName = 'deletepageguard'; // Must match 'base' in plugin.info.txt

echo "Creating distribution package for version $version...\n";

// Files and directories to include
$files = [
    'action.php',
    'admin.php', 
    'plugin.info.txt',
    'LICENSE.md',
    'README.md',
    'CHANGELOG.md',
    'conf/',
    'lang/'
];

// Create dist directory structure
$targetDir = $distDir . '/' . $pluginDirName;
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// Copy files to distribution directory
function copyFileOrDir($source, $dest) {
    // Normalize paths for Windows
    $source = rtrim(str_replace('\\', '/', $source), '/');
    $dest = rtrim(str_replace('\\', '/', $dest), '/');
    
    if (is_file($source)) {
        $destDir = dirname($dest);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        copy($source, $dest);
        return true;
    } elseif (is_dir($source)) {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        
        $sourceLen = strlen($source);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $fileObj) {
            // Normalize and get relative path
            $currentPath = str_replace('\\', '/', $fileObj->getPathname());
            $relativePath = substr($currentPath, $sourceLen + 1);
            $destPath = $dest . '/' . $relativePath;
            
            if ($fileObj->isDir()) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } elseif ($fileObj->isFile()) {
                $destDir = dirname($destPath);
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                copy($currentPath, $destPath);
            }
        }
        return true;
    }
    return false;
}

// Copy all files
$fileCount = 0;
foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "⚠ Warning: File not found: $file\n";
        continue;
    }
    
    $destPath = $targetDir . '/' . $file;
    if (copyFileOrDir($file, $destPath)) {
        echo "✓ Copied: $file\n";
        $fileCount++;
    }
}

echo "\n✅ Successfully created distribution directory!\n";
echo "📁 Location: $distDir/\n";
echo "📦 Plugin directory: $pluginDirName/\n";
echo "📊 Files copied: $fileCount\n";

echo "\n💡 Next steps:\n";
echo "   1. Review the contents in: $distDir/\n";
echo "   2. Create ZIP file manually:\n";
echo "      cd dist && zip -r deletepageguard-$version.zip deletepageguard-$version/\n";
echo "   3. Upload to GitHub Release or DokuWiki Extension Manager\n";
