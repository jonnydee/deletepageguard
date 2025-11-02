# Delete Page Guard Plugin - Developer Tools
#
# Simple Makefile for common development tasks

.PHONY: test check clean help

# Default target
help:
	@echo "Delete Page Guard Plugin - Developer Tools"
	@echo "=========================================="
	@echo ""
	@echo "Available targets:"
	@echo "  test    - Run the test suite"
	@echo "  check   - Check PHP syntax of all files"
	@echo "  clean   - Clean temporary files"
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

# Clean temporary files
clean:
	@echo "Cleaning temporary files..."
	@find . -name "*~" -delete
	@find . -name "*.tmp" -delete
	@echo "Clean complete."