# Changelog

All notable changes to `laravel-payment-sdk` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2024-01-XX

### Added
- Initial release of BAS Payment Gateway SDK for Laravel
- Payment transaction initiation
- Transaction status checking
- Refund processing
- OAuth2 bearer token authentication
- AES-256-CBC encryption with signature verification
- Automatic service provider registration via Laravel package discovery
- `BasPayment` facade for easy access
- Comprehensive error handling with custom exceptions
- Request/response logging for debugging
- Support for PHP 8.1+ with typed properties and union types
- Laravel 10.x, 11.x, 12.x compatibility
- API-only design (no routes, controllers, or views)
- Coexistence with BAS Mini App SDK without conflicts
- Configuration publishing via artisan command
- Environment-based configuration (.env support)
- SDK version tracking in API requests

### Security
- Secure credential management via environment variables
- HMAC-SHA256 signature generation and verification
- AES-256-CBC encryption for API communication
- Sensitive data sanitization in logs
- Bearer token authentication

[Unreleased]: https://github.com/basgate/laravel-payment-sdk/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/basgate/laravel-payment-sdk/releases/tag/v1.0.0
