<?php
/**
 * Delete Page Guard Plugin - Developer Test Suite
 *
 * This is a standalone test runner for developers to verify the plugin's
 * core functionality without requiring DokuWiki integration.
 *
 * Usage: php tests/test_runner.php
 *
 * @license GPL 2 (https://www.gnu.org/licenses/gpl-2.0.html) - see LICENSE.md
 * @author  Johann Duscher <jonny.dee@posteo.net>
 * @copyright 2025 Johann Duscher
 */

// Simple test framework
class TestRunner {
    private $tests = [];
    private $passed = 0;
    private $failed = 0;
    
    public function addTest($name, $callback) {
        $this->tests[] = ['name' => $name, 'callback' => $callback];
    }
    
    public function run() {
        echo "Delete Page Guard Plugin - Developer Test Suite\n";
        echo "================================================\n\n";
        
        foreach ($this->tests as $test) {
            echo "Testing: " . $test['name'] . " ... ";
            
            try {
                $result = call_user_func($test['callback']);
                if ($result === true) {
                    echo "✅ PASS\n";
                    $this->passed++;
                } else {
                    echo "❌ FAIL: " . ($result ?: 'Unknown error') . "\n";
                    $this->failed++;
                }
            } catch (Exception $e) {
                echo "❌ ERROR: " . $e->getMessage() . "\n";
                $this->failed++;
            }
        }
        
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "Results: {$this->passed} passed, {$this->failed} failed\n";
        
        if ($this->failed === 0) {
            echo "🎉 All tests passed!\n";
            exit(0);
        } else {
            echo "💥 Some tests failed!\n";
            exit(1);
        }
    }
}

// Include the test adapter
require_once __DIR__ . '/plugin_test_adapter.php';

// Initialize test runner
$runner = new TestRunner();

// Test 1: Pattern Validation - Valid Patterns
$runner->addTest('Pattern Validation - Valid Simple Pattern', function() {
    $plugin = new TestableDeletePageGuard();
    $result = $plugin->validateRegexPattern('^start$');
    return $result === true;
});

$runner->addTest('Pattern Validation - Valid Complex Pattern', function() {
    $plugin = new TestableDeletePageGuard();
    $result = $plugin->validateRegexPattern('^users:[^:]+:(start|profile)$');
    return $result === true;
});

$runner->addTest('Pattern Validation - Valid Namespace Pattern', function() {
    $plugin = new TestableDeletePageGuard();
    $result = $plugin->validateRegexPattern('^wiki:.*$');
    return $result === true;
});

// Test 2: Pattern Validation - Invalid Patterns
$runner->addTest('Pattern Validation - Invalid Syntax', function() {
    $plugin = new TestableDeletePageGuard();
    $result = $plugin->validateRegexPattern('[invalid');
    return is_string($result) && strpos($result, 'invalid syntax') !== false;
});

$runner->addTest('Pattern Validation - ReDoS Protection', function() {
    $plugin = new TestableDeletePageGuard();
    $result = $plugin->validateRegexPattern('(a+)+b');
    return is_string($result) && strpos($result, 'performance issues') !== false;
});

$runner->addTest('Pattern Validation - Another ReDoS Pattern', function() {
    $plugin = new TestableDeletePageGuard();
    $result = $plugin->validateRegexPattern('(x+)*y');
    return is_string($result) && strpos($result, 'performance issues') !== false;
});

$runner->addTest('Pattern Validation - ReDoS Simple Plus Pattern', function() {
    $plugin = new TestableDeletePageGuard();
    $result = $plugin->validateRegexPattern('(a+)+');
    return is_string($result) && strpos($result, 'performance issues') !== false;
});

$runner->addTest('Pattern Validation - ReDoS Simple Star Pattern', function() {
    $plugin = new TestableDeletePageGuard();
    $result = $plugin->validateRegexPattern('(x*)*');
    return is_string($result) && strpos($result, 'performance issues') !== false;
});

$runner->addTest('Pattern Validation - Length Limit', function() {
    $plugin = new TestableDeletePageGuard();
    $longPattern = str_repeat('a', 1001);
    $result = $plugin->validateRegexPattern($longPattern);
    return is_string($result) && strpos($result, 'too long') !== false;
});

$runner->addTest('Pattern Validation - Line Number Reporting', function() {
    $plugin = new TestableDeletePageGuard();
    $result = $plugin->validateRegexPattern('[invalid', 5);
    return is_string($result) && strpos($result, 'Line 5:') !== false;
});

// Test 3: Pattern Matching
$runner->addTest('Pattern Matching - Exact Match', function() {
    $plugin = new TestableDeletePageGuard();
    return $plugin->matchesPattern('^start$', 'start') === true;
});

$runner->addTest('Pattern Matching - No Match', function() {
    $plugin = new TestableDeletePageGuard();
    return $plugin->matchesPattern('^start$', 'other') === false;
});

$runner->addTest('Pattern Matching - Complex Pattern Match', function() {
    $plugin = new TestableDeletePageGuard();
    return $plugin->matchesPattern('^users:[^:]+:start$', 'users:alice:start') === true;
});

$runner->addTest('Pattern Matching - Complex Pattern No Match', function() {
    $plugin = new TestableDeletePageGuard();
    return $plugin->matchesPattern('^users:[^:]+:start$', 'users:alice:profile') === false;
});

$runner->addTest('Pattern Matching - Partial Match', function() {
    $plugin = new TestableDeletePageGuard();
    return $plugin->matchesPattern('wiki', 'wiki:syntax') === true;
});

$runner->addTest('Pattern Matching - Case Sensitive', function() {
    $plugin = new TestableDeletePageGuard();
    return $plugin->matchesPattern('^Wiki$', 'wiki') === false;
});

// Test 4: File Path Conversion
$runner->addTest('File Path Conversion - Standard Path', function() {
    $plugin = new TestableDeletePageGuard();
    $result = $plugin->getRelativeFilePath('/var/www/data/pages/namespace/page.txt', '/var/www/data');
    return $result === 'namespace/page.txt';
});

$runner->addTest('File Path Conversion - Nested Path', function() {
    $plugin = new TestableDeletePageGuard();
    $result = $plugin->getRelativeFilePath('/var/www/data/pages/ns1/ns2/page.txt', '/var/www/data');
    return $result === 'ns1/ns2/page.txt';
});

$runner->addTest('File Path Conversion - Windows Path', function() {
    $plugin = new TestableDeletePageGuard();
    $result = $plugin->getRelativeFilePath('C:\\dokuwiki\\data\\pages\\test\\page.txt', 'C:\\dokuwiki\\data');
    return $result === 'test/page.txt';
});

$runner->addTest('File Path Conversion - No Pages Subdirectory', function() {
    $plugin = new TestableDeletePageGuard();
    $result = $plugin->getRelativeFilePath('/var/www/data/other/file.txt', '/var/www/data');
    return $result === 'other/file.txt';
});

// Test 5: Configuration Parsing
$runner->addTest('Configuration Parsing - Multiple Patterns', function() {
    $plugin = new TestableDeletePageGuard();
    $patterns = "^start$\n^sidebar$\n^users:[^:]+:start$";
    $lines = preg_split('/\R+/', $patterns, -1, PREG_SPLIT_NO_EMPTY);
    return count($lines) === 3;
});

$runner->addTest('Configuration Parsing - Empty Lines Ignored', function() {
    $plugin = new TestableDeletePageGuard();
    $patterns = "^start$\n\n\n^sidebar$\n   \n^end$";
    $lines = preg_split('/\R+/', $patterns, -1, PREG_SPLIT_NO_EMPTY);
    $nonEmpty = array_filter($lines, function($line) { return trim($line) !== ''; });
    return count($nonEmpty) === 3;
});

$runner->addTest('Configuration Parsing - Windows Line Endings', function() {
    $plugin = new TestableDeletePageGuard();
    $patterns = "^start$\r\n^sidebar$\r\n^end$";
    $lines = preg_split('/\R+/', $patterns, -1, PREG_SPLIT_NO_EMPTY);
    return count($lines) === 3;
});

// Test 6: Security Features
$runner->addTest('Security - Forward Slash Escaping', function() {
    $plugin = new TestableDeletePageGuard();
    // Pattern with forward slashes should be properly escaped
    return $plugin->matchesPattern('path/to/file', 'path/to/file') === true;
});

$runner->addTest('Security - Unicode Support', function() {
    $plugin = new TestableDeletePageGuard();
    // Test unicode pattern matching
    return $plugin->matchesPattern('^café$', 'café') === true;
});

$runner->addTest('Security - Special Regex Characters', function() {
    $plugin = new TestableDeletePageGuard();
    // Test that dots are treated as literal dots when escaped
    return $plugin->matchesPattern('file\.txt$', 'file.txt') === true;
});

$runner->addTest('Security - Injection Protection', function() {
    $plugin = new TestableDeletePageGuard();
    // Ensure patterns with potential injection attempts are handled safely
    $result = $plugin->validateRegexPattern('(?{`ls`})');
    return is_string($result); // Should fail validation, not execute code
});

// Test 7: Edge Cases
$runner->addTest('Edge Cases - Empty Pattern', function() {
    $plugin = new TestableDeletePageGuard();
    $result = $plugin->validateRegexPattern('');
    // Empty patterns should be considered invalid
    return is_string($result) || $plugin->matchesPattern('', 'anything') === false;
});

$runner->addTest('Edge Cases - Empty Target', function() {
    $plugin = new TestableDeletePageGuard();
    return $plugin->matchesPattern('^$', '') === true;
});

$runner->addTest('Edge Cases - Whitespace Pattern', function() {
    $plugin = new TestableDeletePageGuard();
    return $plugin->matchesPattern('^\s+$', '   ') === true;
});

$runner->addTest('Edge Cases - Very Long Target', function() {
    $plugin = new TestableDeletePageGuard();
    $longTarget = str_repeat('a', 10000);
    return $plugin->matchesPattern('^a+$', $longTarget) === true;
});

// Test 8: Real-world Patterns
$runner->addTest('Real-world - User Page Protection', function() {
    $plugin = new TestableDeletePageGuard();
    $pattern = '^users:[^:]+:start$';
    return $plugin->matchesPattern($pattern, 'users:john:start') === true &&
           $plugin->matchesPattern($pattern, 'users:mary:start') === true &&
           $plugin->matchesPattern($pattern, 'users:admin:profile') === false;
});

$runner->addTest('Real-world - Namespace Protection', function() {
    $plugin = new TestableDeletePageGuard();
    $pattern = '^admin:.*$';
    return $plugin->matchesPattern($pattern, 'admin:config') === true &&
           $plugin->matchesPattern($pattern, 'admin:users:list') === true &&
           $plugin->matchesPattern($pattern, 'public:page') === false;
});

$runner->addTest('Real-world - File Extension Pattern', function() {
    $plugin = new TestableDeletePageGuard();
    $pattern = '\.txt$';
    return $plugin->matchesPattern($pattern, 'document.txt') === true &&
           $plugin->matchesPattern($pattern, 'image.png') === false;
});

// Run all tests
$runner->run();