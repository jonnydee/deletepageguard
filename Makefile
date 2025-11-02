# Delete Page Guard Plugin - Developer Tools
#
# Professional Makefile for development, testing, and release management

.PHONY: test check clean dist release version help

# Default target
help:
	@echo "Delete Page Guard Plugin - Developer Tools"
	@echo "=========================================="
	@echo ""
	@echo "Development targets:"
	@echo "  test    - Run the test suite"
	@echo "  check   - Check PHP syntax of all files"
	@echo "  clean   - Clean temporary files"
	@echo ""
	@echo "Release targets:"
	@echo "  version [VERSION=x.y.z] - Update version in all files"
	@echo "  dist    - Create distribution ZIP file"
	@echo "  release [VERSION=x.y.z]  - Complete release workflow"
	@echo ""
	@echo "Utility targets:"
	@echo "  status  - Show current version and git status"
	@echo "  help    - Show this help message"

# Run the test suite
test:
	@echo "Running test suite..."
	php tests/test_runner.php

# Check syntax of all PHP files
check:
	@echo "Checking PHP syntax..."
	@find . -name "*.php" -not -path "./tests/*" -exec php -l {} \;
	@echo "Syntax check complete."

# Clean temporary files and build artifacts
clean:
	@echo "Cleaning temporary files and build artifacts..."
	@find . -name "*~" -delete
	@find . -name "*.tmp" -delete
	@rm -rf dist/
	@echo "Clean complete."

# Update version in all files
version:
ifndef VERSION
	@echo "Error: VERSION parameter required"
	@echo "Usage: make version VERSION=1.2.3"
	@exit 1
endif
	@echo "Updating version to $(VERSION)..."
	php build/update-version.php $(VERSION)
	@echo "Version update complete."

# Create distribution ZIP file
dist: check test
	@echo "Creating distribution package..."
	php build/create-dist.php

# Complete release workflow
release: check test
ifndef VERSION
	@echo "Error: VERSION parameter required"
	@echo "Usage: make release VERSION=1.2.3"
	@exit 1
endif
	@echo "Starting release workflow for version $(VERSION)..."
	@make version VERSION=$(VERSION)
	@echo "Please update CHANGELOG.md with release notes, then run:"
	@echo "  git add -A"
	@echo "  git commit -m 'Release version $(VERSION)'"
	@echo "  git tag v$(VERSION)"
	@echo "  make dist"
	@echo "  git push origin main --tags"

# Show current status
status:
	@php build/show-status.php
	@echo "Git status:"
	@git status --short || echo "Not a git repository"