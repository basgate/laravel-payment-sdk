# Contributing to Laravel Payment SDK

Thank you for considering contributing to the BAS Laravel Payment SDK! We welcome contributions from the community.

## How to Contribute

### Reporting Bugs

If you find a bug, please create an issue on GitHub with:
- A clear title and description
- Steps to reproduce the issue
- Expected vs actual behavior
- Your environment (PHP version, Laravel version, etc.)
- Any relevant error messages or logs

### Suggesting Enhancements

We welcome feature requests! Please create an issue with:
- A clear description of the feature
- Use cases and benefits
- Any implementation ideas you have

### Pull Requests

1. **Fork the repository** and create your branch from `main`
2. **Write clear commit messages** following conventional commits format:
   - `feat:` for new features
   - `fix:` for bug fixes
   - `docs:` for documentation changes
   - `refactor:` for code refactoring
   - `test:` for adding tests
   - `chore:` for maintenance tasks

3. **Add tests** for any new functionality (when test suite is available)
4. **Update documentation** if you're changing functionality
5. **Ensure code quality**:
   - Follow PSR-12 coding standards
   - Add proper type hints and return types
   - Write clear comments for complex logic
   - Use meaningful variable and method names

6. **Test your changes** thoroughly:
   - Test with different PHP versions (8.1+)
   - Test with different Laravel versions (10.x, 11.x, 12.x)
   - Ensure backward compatibility

7. **Update the CHANGELOG.md** with your changes under the `[Unreleased]` section

## Code Style

This project follows PSR-12 coding standards. Please ensure your code adheres to these standards.

## Development Setup

1. Clone the repository
2. Install dependencies: `composer install`
3. Copy `.env.example` to `.env` and configure your test credentials
4. Run tests: `composer test` (when available)

## Questions?

Feel free to create an issue for any questions about contributing.

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
