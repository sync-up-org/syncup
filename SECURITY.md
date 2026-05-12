# Security Policy

## Supported Versions

| Version | Supported |
|---------|-----------|
| 1.0.x   | ✅ Currently supported |

## Reporting a Vulnerability

We take the security of SyncUp seriously. If you discover a security vulnerability, please follow these steps:

1. **Do not** open a public issue or pull request
2. Send a detailed report to the project maintainers describing:
   - The affected component and version
   - The nature and potential impact of the vulnerability
   - Steps to reproduce the issue
   - Any suggested remediation

You can expect an initial response within 48 hours. We will keep you informed of the progress toward a resolution.

## Remediation Process

Once a vulnerability is confirmed:
1. A fix is developed on a private branch
2. The fix is reviewed and tested against the full test suite
3. A release containing the fix is published
4. The vulnerability is publicly disclosed after the fix is available

## Scope

This security policy covers:

- The Laravel backend API (`backend/`)
- The Vue frontend application (`frontend/`)
- API authentication and authorization mechanisms
- Data storage and transmission

## Past Audits

An initial security audit identified and remediated 13 vulnerabilities across the application. See the [README](README.md#security-audit) for details.
