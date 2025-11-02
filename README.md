# Delete Page Guard for DokuWiki

The **Delete Page Guard** plugin prevents accidental or intentional deletion of
wikipages in your DokuWiki installation by blocking the “empty save”
operation when certain pages are protected. A page is considered for
protection when its ID or relative file path matches one or more
regular expressions configured via the DokuWiki Configuration Manager.

## Features

* Prevents deletion via "empty save" on protected pages.
* Supports any number of protection patterns using PCRE syntax.
* Choose whether to match against the colon‑separated page ID or the
  relative file system path.
* Allow administrators and optional additional groups to bypass the
  protection.
* Optionally treat whitespace‑only content as empty.
* **Pattern validation**: Real-time validation with detailed error messages for administrators.
* **Admin interface**: Dedicated admin page for testing and validating patterns.
* **Security features**: Built-in ReDoS protection and input sanitization.

## Installation

1. Copy the contents of this plugin directory into
   `<dokuwiki>/lib/plugins/deletepageguard/`.
2. Ensure that the directory name matches the `base` value in
   `plugin.info.txt` (here: `deletepageguard`).
3. Visit the **Configuration Manager** in your DokuWiki and adjust the
   plugin settings under the “Delete Page Guard” section.

## Configuration

The following options are available in the Configuration Manager:

| Setting | Description |
|---|---|
| **Protected page patterns** | List of PCRE regular expressions. Each line defines a pattern. When a page matches any pattern, non‑admin users cannot delete it by empty save. Invalid patterns are automatically skipped with warnings shown to administrators. |
| **Match against** | Choose whether the patterns should match against the page ID (e.g. `users:john:home`) or the relative file path (e.g. `users/john/home.txt`). |
| **Extra groups allowed to delete** | Comma separated list of user groups that are allowed to delete protected pages, in addition to administrators. Leave empty to restrict deletion to admins only. |
| **Treat whitespace‑only pages as empty** | If enabled, pages containing only whitespace will be treated as empty and deletion will be blocked on protected pages. |

### Pattern Validation

The plugin includes comprehensive pattern validation:

* **Real-time validation**: Invalid patterns are automatically detected when pages are saved
* **Administrator feedback**: Detailed error messages are shown to administrators when invalid patterns are encountered
* **Admin interface**: Visit **Admin → Delete Page Guard** to test and validate patterns before saving them to configuration
* **Security protection**: Built-in protection against ReDoS (Regular Expression Denial of Service) attacks

### Pattern examples

* `^users:` – protect all pages in the `users` namespace.
* `^users:[^:]+:start$` – protect every user's landing page named `start` under `users:<username>`.
* `^projects:.*$` – protect everything in the `projects` namespace.
* `^private/.*\.txt$` – when matching against file paths, protect any `.txt` file in the `private` directory.

## How it works

When a page is saved, DokuWiki triggers the `COMMON_WIKIPAGE_SAVE` event just before writing to disk. For normal edits, the plugin does nothing. However, when the new content is empty (after optional trimming) the plugin checks the configured patterns against the chosen target (ID or file path). If a match occurs and the current user is not an administrator and not in one of the exempt groups, the plugin prevents the deletion by calling `$event->preventDefault()` and `$event->stopPropagation()` as documented in DokuWiki's event system. An error message is displayed to the user informing them that deletion is not allowed.

## Security Features

* **Regex Validation**: All regular expressions are validated for syntax before use.
* **ReDoS Protection**: Basic protection against Regular Expression Denial of Service attacks through pattern complexity checks and execution timeouts.
* **Input Sanitization**: User input is properly sanitized and validated.

## Development

### Developer Testing

The plugin includes a comprehensive test suite for developers:

```bash
# Run all tests
php tests/test_runner.php

# Check syntax of all files (if make is available)
make check

# See all available commands (if make is available)
make help
```

The test suite covers pattern validation, matching logic, security features, and edge cases without requiring a DokuWiki installation.

### Release Process

For maintainers and contributors, see **[RELEASE.md](RELEASE.md)** for the complete release workflow including:
- Version management and semantic versioning
- Automated testing and validation
- Distribution packaging
- Git workflow and tagging
- Quality assurance processes

### Test Coverage

- **Comprehensive tests** covering all core functionality
- **Pattern validation** (syntax, ReDoS protection, length limits)
- **Pattern matching** (simple and complex regex patterns)  
- **File path conversion** (absolute to relative paths)
- **Configuration parsing** (multi-line patterns, different line endings)
- **Security features** (escaping, unicode support, injection protection)
- **Edge cases** (empty patterns, very long inputs)
- **Real-world scenarios** (user pages, namespaces, file extensions)

## Compatibility

This plugin hooks into the `COMMON_WIKIPAGE_SAVE` event, which was introduced in DokuWiki release **"Detritus" (2016‑02‑24)** and is marked as preventable. It has been tested for compatibility with current releases such as **"Kaos" (2024‑02‑06b)**. The plugin uses only public APIs and the documented event system, so it should continue to work with future versions as long as these events remain available.

## License

This plugin is released under the terms of the
[GNU General Public License v2](https://www.gnu.org/licenses/gpl-2.0.html).
