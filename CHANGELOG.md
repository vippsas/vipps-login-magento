---
title: Changelog
sidebar_position: 200
pagination_next: null
section: Plugins
---

# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog and this project adheres to Semantic Versioning.

## [2.6.3] - 2026-06-10

### Fixed

- `getRegion()` now returns a string, fixing a `strcasecmp()` type error — thanks @pikulsky.

### Changed

- Street address can now be collapsed to a single line: when Magento is configured to use one
  street line, the multi-line Vipps address is combined into one line (configurable via the
  `customer/address/street_lines` store setting).

## [2.6.2] - 2026-06-08

### Security

- Encrypted the cached OAuth token payload in `vipps_login_authorization` at rest and fixed the
  expiry-cleanup cutoff, so stale token rows are pruned after 5 minutes.

### Fixed

- Fixed invalid state handling, PR #65 by @jonkjenn
- Restored the validated OAuth state on the token-retry redirect so concurrent/duplicate login
  callbacks revalidate correctly after the state is consumed.
- Fixed an issue where applying Vipps address in Magento would allow applying an address not assigned to the Vipps customer.

### Changed

- Updated Logging to support both Monolog 2 and 3
- Widened the `firebase/php-jwt` constraint to `^6.0 || ^7.0` (matching Magento) so installs can use
  the patched 7.x release that addresses the weak-encryption advisory GHSA-2x45-7fc3-mxwq.

## [2.6.1] - 2026-02-09

### Fixed

- Improved phone matching regex, PR #5 by @ed007m

## [2.6.0] - 2025-12-03

### Added

- Add PHP 8.4 compatibility
- Magento 2.4.8 support

### Changed

- Updated Monolog dependency to version 3.x to support Magento 2.4.8
