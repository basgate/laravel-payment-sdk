# Security Policy

## Supported Versions

We release patches for security vulnerabilities for the following versions:

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |

## Reporting a Vulnerability

**Please do not report security vulnerabilities through public GitHub issues.**

If you discover a security vulnerability within this SDK, please send an email to Ahmed Alshikh. We take all security vulnerabilities seriously and will address them promptly.

Please include the following information in your report:
- Type of vulnerability
- Full paths of source file(s) related to the vulnerability
- The location of the affected source code (tag/branch/commit or direct URL)
- Any special configuration required to reproduce the issue
- Step-by-step instructions to reproduce the issue
- Proof-of-concept or exploit code (if possible)
- Impact of the issue, including how an attacker might exploit it

We will acknowledge your email within 48 hours and send a more detailed response within 7 days indicating the next steps in handling your report.

## Security Best Practices

When using this SDK:

1. **Never commit credentials** to version control
   - Use `.env` files for all sensitive configuration
   - Add `.env` to `.gitignore`

2. **Keep credentials secure**
   - Rotate client secrets regularly
   - Use different credentials for staging and production
   - Limit access to production credentials

3. **Keep the SDK updated**
   - Subscribe to releases on GitHub
   - Update to the latest version regularly
   - Review CHANGELOG.md for security-related updates

4. **Monitor logs**
   - Review application logs regularly
   - Be aware that sensitive data is sanitized in logs
   - Set up alerts for unusual API activity

5. **Use HTTPS**
   - Always use HTTPS endpoints in production
   - Verify SSL certificates are valid

6. **Validate webhooks**
   - If implementing webhooks, always verify signatures
   - Use the built-in signature verification methods

## Disclosure Policy

When we receive a security bug report, we will:

1. Confirm the problem and determine affected versions
2. Audit code to find similar problems
3. Prepare fixes for all supported versions
4. Release new versions as soon as possible

## Comments on this Policy

If you have suggestions on how this process could be improved, please submit a pull request.
