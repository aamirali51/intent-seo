# Contributing to Intent SEO

> **Note**: This is a fork of [rumenx/php-seo](https://github.com/RumenDamyanov/php-seo) modified for Intent Framework.

Thank you for considering contributing to Intent SEO! We welcome contributions from the community.

## Code of Conduct

This project follows the [Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code.

## Getting Started

1. Fork the repository on GitHub
2. Clone your fork locally
3. Set up the development environment
4. Create a new branch for your feature or fix
5. Make your changes
6. Test your changes
7. Submit a pull request

## Development Setup

### Prerequisites

- PHP 8.2 or higher
- Composer
- Git

### Installation

```bash
# Clone your fork
git clone https://github.com/YOUR_USERNAME/intent-seo.git
cd intent-seo

# Install dependencies
composer install
```

### Development Tools

```bash
# Run tests
composer test

# Check code style
composer style

# Fix code style
composer style-fix

# Run static analysis
composer analyze

# Run all quality checks
composer quality
```

## Making Changes

### Branch Naming

- `feature/description` - for new features
- `fix/description` - for bug fixes
- `docs/description` - for documentation updates

### Commit Messages

Follow conventional commit format:

```
type(scope): description
```

Types: `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`

## Testing

All contributions must:

- ✅ Write tests for new functionality
- ✅ Ensure all existing tests pass
- ✅ Use descriptive test names

## Code Style

- Use strict types: `declare(strict_types=1);`
- Use type hints for all parameters and return types
- Follow PSR-12 standards
- Use namespace `Intent\Seo`

## Pull Request Process

1. Update documentation if needed
2. Add tests for new functionality
3. Run `composer quality` to verify everything passes
4. Submit pull request

## Reporting Issues

- **Bug Reports**: Use the issue template with steps to reproduce
- **Security Issues**: Email security concerns privately, do not create public issues

## Feature Requests

Before submitting:
1. Check if it already exists
2. Consider if it fits the Intent Framework scope
3. Provide a clear use case

---

Thank you for contributing to Intent SEO! 🚀