# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.4.10] - 2025-13-08
### Changed
- Add null checking in liability/exemption chain

## [1.4.9] - 2025-11-08
### Changed
- Add missing translations

## [1.4.8] - 2025-24-07
### Added
- Remove extension validation for template filename
- Implement omit order item details button and logic
- Fix order total webhook issue

## [1.4.7] - 2025-30-06
### Added
- Add mealvoucher and CVCO payment methods

## [1.4.6] - 2025-13-06
### Fixed
- Fix issue when order is paid using GooglePay and fails to render in the backoffice

## [1.4.5] - 2025-14-04
### Added
- Update plugin translations

## [1.4.4] - 2025-31-03
### Added
- Add 3DS exemption types to the plugin

## [1.4.3] - 2025-21-03
### Changed
- Fix issue with submit button not being deactivated when card fields are not added on checkout

## [1.4.2] - 2024-17-06
### Added
- Improvement to webhook response time

## [1.4.1] - 2023-11-23
### Fixed
- Fix fraud display
- Fix amount verification
- Fix lock in processor

## [1.4.0] - 2023-04-24
### Added

- Add Intersolve payment method
- Add Meal Vouchers payment method
- Add amount surcharging feature
- Add message about paid & displayed amounts
- Handle AUS transaction reference format

### Fixed

- Fix currency management
- Fix context control for redirect payments
- Fix token storage logic
- Fix credentials length control
- Fix process of older webhooks
- Fix order reference in native table
- Fix session storage exception
- Fix LineItems & Shipping lines
- Fix check on amounts when validating orders

## [1.3.2] - 2023-01-05
### Added

- Add COF parameters
- Add address indicator
- Add liability details
- Add subsequent payment parameters
- Add 3DS exemption & challenge enforcement

### Fixed

- Fix iframe payment logs
- Fix context (amount & currency) control

## [1.3.1] - 2022-12-02
### Added

- Add a copy button on webhooks URL
- Add option to group card payment options

### Changed

- Update default safety delay to 12 sec.
- Remove default iframe template value
- Update SDK to v4.5.0

### Fixed

- Remove spaces & other characters in phone fields
- Support currencies other than EUR in line items details
- Fix order status workflow in case of multiple webhooks calls
- Fix rounded prices in line items

## [1.3.0] - 2022-08-10
### Added

- Line Items (prices & cart details)
- Klarna & Oney payment methods

### Fixed

- Cart checksum calculation now includes vouchers

## [1.2.1] - 2022-07-11
### Changed

- Move & transform endpoint logo to generic logo in the payment methods tab
- Add a refresh link when iframe payment is rejected

## [1.2.0] - 2022-04-12
### Added

- UID value to log lines
- Customizable endpoints
- New return page after pending payment rejected

### Changed

- Update SDK
- Payment methods are refreshed after saving credentials
- Better order validation mechanism
- Update payment logos

## [1.1.1] - 2022-02-24
### Fixed

- Split orders are now associated with the module
- Order statuses of split orders are updated properly
- Simultaneous webhooks are processed correctly

## [1.1.0] - 2022-02-22
### Added

- First major version
