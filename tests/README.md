# Delete Page Guard - Developer Test Suite

This directory contains a standalone test suite for developers to verify the Delete Page Guard plugin's core functionality without requiring a full DokuWiki installation.

## Running Tests

```bash
# From the plugin root directory
php tests/test_runner.php
```

## Requirements

- PHP 7.4+ (same as the plugin)
- No external dependencies

## Test Coverage

The test suite covers:

### ✅ Pattern Validation
- Valid regex syntax validation
- ReDoS (Regular Expression Denial of Service) protection
- Pattern length limits (1000 character max)
- Line number reporting for errors

### ✅ Pattern Matching
- Simple and complex regex patterns
- Exact matches and partial matches
- Case sensitivity
- Unicode character support

### ✅ File Path Conversion
- Converting absolute paths to relative paths
- Windows and Unix path handling
- Nested directory structures
- Edge cases with non-standard paths

### ✅ Configuration Parsing
- Multi-line pattern configuration
- Empty line handling
- Different line ending formats (Unix/Windows)
- Whitespace trimming

### ✅ Security Features
- Forward slash escaping in patterns
- Unicode support and safety
- Special regex character handling
- Injection attempt protection

### ✅ Edge Cases
- Empty patterns and targets
- Very long input strings
- Whitespace-only content
- Malformed input handling

### ✅ Real-world Scenarios
- User page protection patterns
- Namespace-based protection
- File extension matching
- Complex multi-part patterns

## Test Structure

- **`test_runner.php`** - Main test framework and all test cases
- **`plugin_test_adapter.php`** - Makes plugin methods testable by mocking DokuWiki dependencies
- **`README.md`** - This documentation

## Adding New Tests

To add a new test, edit `test_runner.php` and add:

```php
$runner->addTest('Test Description', function() {
    $plugin = new TestableDeletePageGuard();
    // Your test logic here
    $result = $plugin->someMethod($input);
    return $result === $expected; // Return true for pass, false/string for fail
});
```

## Test Output

- ✅ **PASS** - Test succeeded
- ❌ **FAIL** - Test failed with optional error message  
- ❌ **ERROR** - Test threw an exception

The runner exits with code 0 on success, 1 on failure (suitable for CI/CD).

## Mocked Dependencies

The test adapter mocks these DokuWiki components:

- `dokuwiki\Extension\ActionPlugin` - Base plugin class
- `dokuwiki\Extension\Event` - Event system
- `dokuwiki\Extension\EventHandler` - Event registration
- Plugin configuration (`getConf()`)
- Language strings (`getLang()`)

## Example Usage

```bash
# Run tests and see detailed output
php tests/test_runner.php

# Check if tests pass (for scripts)
php tests/test_runner.php && echo "All tests passed!"

# Run tests and capture output
php tests/test_runner.php > test_results.txt 2>&1
```

## Integration with Development Workflow

This test suite is designed for:

- **Pre-commit testing** - Verify changes before committing
- **Continuous Integration** - Automated testing in CI/CD pipelines  
- **Regression testing** - Ensure new features don't break existing functionality
- **Development confidence** - Rapid feedback during development

## Performance

The test suite typically runs in under 1 second and includes 30+ test cases covering all critical functionality.