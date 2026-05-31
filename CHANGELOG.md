# Changelog

All notable changes to APLINE Simple Cookies for PrestaShop 9 are documented
here. The format is based on [Keep a Changelog](https://keepachangelog.com/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.0.0] – 2026-05-31

### Added
- Initial release.
- GDPR / UODO / ePrivacy compliant cookie consent banner, bilingual PL + EN
  (Polish by default), with Reject / Preferences / Accept at equal prominence.
- Three database tables: cookie categories, individual cookie entries and a
  consent audit log.
- Two admin screens (Cookie Categories and Cookies) with full CRUD, rejecting
  validation, drag&drop ordering and a "necessary category" singleton rule.
- Module configuration page: banner appearance & behavior, a dual PL/EN copy
  editor, audit-log settings with 30-day statistics and a streamed CSV export.
- Third-party tracker support (Google Analytics 4, Google Tag Manager,
  Facebook Pixel, Hotjar) with per-field "how to add this" help modals, plus
  custom HTML/JS snippets per consent category.
- Tracker gating: no third-party script is emitted before consent; banner.js
  injects each tracker only after the visitor consents to its category.
- Consent audit log with SHA256-hashed IP (per-install salt), retention
  cleanup, and CSV export for accountability.
- GPC (Global Privacy Control) honoring, 5-second Preferences auto-collapse,
  re-prompt on policy-version bump or consent expiry.
- "Show example template" modal with a ready PL/EN cookie-policy template.
- Optional auto-detection of APLINE Simple Google Auth (adds its g_csrf_token
  cookie to the Necessary category).
