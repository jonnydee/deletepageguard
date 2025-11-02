# Release Process for Delete Page Guard Plugin

This document describes the complete release process for the Delete Page Guard DokuWiki plugin, including development workflow, testing, versioning, and distribution.

## Overview

The plugin uses a professional release workflow with:

- Centralized version management
- Automated testing and validation
- Distribution packaging
- Git integration
- Semantic versioning

## Prerequisites

Before starting a release, ensure you have:

- PHP 7.4+ installed
- Git repository properly configured
- All changes committed and pushed
- Tests passing

## Release Workflow

### 1. Prepare for Release

First, ensure the codebase is ready for release:

```bash
# Check current status
make status

# Run all tests to ensure quality
make test

# Check PHP syntax of all files
make check
```

All tests should pass and syntax should be clean before proceeding.

### 2. Update Version

Use the automated version update system:

```bash
# Update to new version (use semantic versioning)
make version VERSION=x.y.z
```

**Version numbering guidelines:**

- **Patch version** (x.y.Z): Bug fixes, security patches, minor improvements
- **Minor version** (x.Y.z): New features, backward-compatible changes
- **Major version** (X.y.z): Breaking changes, major refactoring

**Examples:**

```bash
make version VERSION=1.0.1  # Bug fix release
make version VERSION=1.1.0  # Feature release
make version VERSION=2.0.0  # Major release with breaking changes
```

**Note:** The current version is 1.0.0, which represents the initial stable release.

This command will:

- Update `version.php` with new version and current date
- Update `plugin.info.txt` with version information
- Validate the new version format
- Show what files were modified

### 3. Update Documentation

After version update, manually update release documentation:

#### 3.1 Update CHANGELOG.md

Create or update `CHANGELOG.md` with release notes:

```markdown
# Changelog

## [x.y.z] - YYYY-MM-DD

### Added

- New features

### Changed

- Modified functionality

### Fixed

- Bug fixes

### Security

- Security improvements
```

#### 3.2 Review README.md

Ensure the README.md is up to date with:

- Current feature list
- Updated installation instructions
- Correct version references

### 4. Complete Release Workflow

Use the automated release workflow:

```bash
# Complete release preparation
make release VERSION=x.y.z
```

This command will:

1. Run syntax checks
2. Execute all tests
3. Update version information
4. Provide git commands for next steps

### 5. Git Operations

Follow the provided git workflow:

```bash
# Review all changes
git status
git diff

# Stage all changes
git add -A

# Commit the release
git commit -m "Release version x.y.z"

# Tag the release
git tag vx.y.z

# Create distribution package
make dist

# Push to repository with tags
git push origin main --tags
```

### 6. Create Distribution Package

Generate the distribution package:

```bash
make dist
```

This will:

- Run tests and syntax checks
- Create a clean distribution directory in `dist/deletepageguard-x.y.z/`
- Include only necessary files for DokuWiki installation
- Provide installation instructions

**Distribution contents:**

- `action.php` - Core plugin functionality
- `admin.php` - Administrative interface
- `plugin.info.txt` - Plugin metadata
- `LICENSE.md` - GPL v2 license
- `conf/` - Configuration files
- `lang/` - Language files

### 7. Verify Distribution

Test the distribution package:

```bash
# Check distribution contents
ls -la dist/deletepageguard-x.y.z/

# Verify all required files are present
# Test installation in a DokuWiki instance (recommended)
```

## Makefile Commands Reference

The plugin includes a comprehensive Makefile with the following targets:

### Development Commands

```bash
make test      # Run tests
make check     # Check PHP syntax of all files
make clean     # Clean temporary files and build artifacts
```

### Release Commands

```bash
make version VERSION=x.y.z  # Update version in all files
make dist                   # Create distribution package
make release VERSION=x.y.z  # Complete release workflow
```

### Utility Commands

```bash
make status    # Show current version and git status
make help      # Show all available commands
```

## Version Management

### Centralized Version Control

The plugin uses centralized version management through `version.php`:

```php
return [
    'version' => 'x.y.z',
    'date' => 'YYYY-MM-DD',
    'name' => 'Delete Page Guard',
    'author' => 'Johann Duscher',
    'email' => 'jonny.dee@posteo.net',
    'url' => 'https://github.com/jonnydee/deletepageguard'
];
```

### Automated Updates

The `build/update-version.php` script automatically:

- Updates version across all relevant files
- Sets current date
- Validates semantic version format
- Reports all changes made

## Quality Assurance

### Testing

The plugin includes comprehensive testing:

- Test cases covering all functionality
- Pattern validation and matching
- Security features and edge cases
- Cross-platform compatibility
- No DokuWiki installation required for testing

### Validation

Each release is validated through:

- PHP syntax checking
- Comprehensive test suite execution
- Pattern validation testing
- Security vulnerability checks

## Distribution

### Package Contents

The distribution package includes only files necessary for DokuWiki installation:

- Core plugin files (`action.php`, `admin.php`)
- Configuration files (`conf/`)
- Language files (`lang/`)
- Documentation (`LICENSE.md`)
- Plugin metadata (`plugin.info.txt`)

### Installation

Users install the plugin by:

1. Extracting the distribution package
2. Copying contents to `<dokuwiki>/lib/plugins/deletepageguard/`
3. Configuring via DokuWiki Configuration Manager

## Troubleshooting

### Common Issues

**Tests failing:**

```bash
# Run tests with verbose output
php tests/test_runner.php

# Check specific test failures and fix code
```

**Syntax errors:**

```bash
# Find syntax errors
make check

# Fix reported syntax issues
```

**Version update issues:**

```bash
# Manually check version.php format
php -r "var_dump(include 'version.php');"

# Ensure proper semantic versioning (x.y.z)
```

**Distribution problems:**

```bash
# Clean and rebuild
make clean
make dist

# Check file permissions and paths
```

## Best Practices

1. **Always test before release**: Run `make test` and `make check`
2. **Use semantic versioning**: Follow x.y.z format consistently
3. **Update documentation**: Keep CHANGELOG.md and README.md current
4. **Tag releases**: Use `git tag vx.y.z` for version tracking
5. **Verify distribution**: Test installation in actual DokuWiki instance
6. **Backup before major releases**: Ensure git repository is safely backed up

## Release Checklist

- [ ] All tests pass (`make test`)
- [ ] Syntax check clean (`make check`)
- [ ] Version updated (`make version VERSION=x.y.z`)
- [ ] CHANGELOG.md updated with release notes
- [ ] README.md reviewed and current
- [ ] Changes committed and pushed
- [ ] Release tagged (`git tag vx.y.z`)
- [ ] Distribution package created (`make dist`)
- [ ] Distribution verified and tested
- [ ] Release pushed with tags (`git push origin main --tags`)

## Support

For questions about the release process:

- Review this documentation
- Check Makefile help (`make help`)
- Examine test output for issues
- Refer to git history for previous releases
