# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## TODO
- http://phppackagechecklist.com/#1,2,3,4,5,6,9,10,11,12,13,14
  Complete check list items.
- Add unit tests.
- Add facade(s) for use in views.
- Inject JS into views.
- Detect through subscription create when a user had previously unsubscribed (churn), then resubscribes (unchurn).
(This is already detected in subscription update.)
- Filter any incoming webhook events that are in test mode.

## [0.1.6 - 0.2.4] - 2015-05-30
### Added
- Webhook for tracking Stripe events.
- Documented MixPanel events in README.

### Fixed
- Sanitize People data before setting it, so that values don't get erased if something is not set.
- Fix method parameters.
- Track client IP address.
- Ignore charge updates.
- Formatting of non-existent dates during profile setting.
- Detection of stripe customer id in webhook.

## [0.1.0 - 0.1.4] - 2015-05-30
### Changed
- Updated `composer.json` details.
- Updated README details.
- Renamed events to model the formula <what + action>, i.e. "Login Succeeded" or "Page Viewed".

### Fixed
- Update profile information if not set (i.e. for prior existing users).
- Log signup date as string, not as object.
- Name detection on user model.

### Added
- Initial package development.
- User observer.
- User events handler.
- DocBlocks.
