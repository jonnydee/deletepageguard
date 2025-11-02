# Changelog

All notable changes to the Delete Page Guard plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-01-02

### Added
- Initial release of Delete Page Guard plugin
- Protection of pages from deletion via empty save operations
- PCRE regular expression pattern matching
- Support for matching against page ID or file path
- Administrator bypass functionality
- Configurable exempt user groups
- Whitespace-only content handling (trim mode)
- Configuration via DokuWiki Configuration Manager
- English language support
- **Pattern validation system** with real-time error detection
- **Admin interface** for testing and validating regex patterns
- **Comprehensive error messaging** for administrators

### Security
- Regex pattern validation to prevent malformed expressions
- Basic ReDoS (Regular Expression Denial of Service) protection
- Input sanitization and validation
- Execution timeout protection for regex matching
- Pattern complexity limits to prevent performance issues

### Technical
- Integration with DokuWiki's `COMMON_WIKIPAGE_SAVE` event
- Proper event handling with `preventDefault()` and `stopPropagation()`
- GPL v2 licensing with proper headers
- Standard DokuWiki plugin structure
- Comprehensive documentation and examples

[1.0.0]: https://github.com/jonnydee/deletepageguard/releases/tag/v1.0.0