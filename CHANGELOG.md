# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## TODO
- http://phppackagechecklist.com/#1,2,3,4,5,6,9,10,11,12,13,14
  Complete check list items.
- Add unit tests.
- Add facade(s) for use in views.
- Detect through subscription create when a user had previously unsubscribed (churn), then resubscribes (unchurn).
(This is already detected in subscription update.)
- Filter any incoming webhook events that are in test mode.

## [0.4.10 - 0.4.12] - 28 OCt 2015
### Added
- tracking of robots, if a browser isn't detected, and it is confirmed as a robot.

### Fixed
- incorrect usage of array_filter, causing empty strings to be passed.

### Changed
- reverted from using getAttribute(), as it is redundant.
- refactored `track()` method to track: Url, Operating System, Hardware, Browser, Referring Domain, IP (For GeoLocation)

### Removed
- any "unknown" values from being passed.

## [0.4.7 - 0.4.8] - 25 Oct 2015
### Changed
- referenced to user model properties to use `getAttribute()` instead of referencing them directly.

### Added
- tracking of `name` in addition to `first name` and `last name`.
- tracking of `referrer` and `referring domain` to help get additional information for Live View (may not yet work
  fully).

### Fixed
- improper tracking of user details, which caused users' names to be blank and a `0 Object [object]` field to be tracked
  erroneously.
- fixed tracking to not depend on `$user->id` when referencing the primary key, but pulling it dynamically instead.

## [0.4.6] - 25 Sep 2015
### Fixed
- documentation references to old Mixpanel class.
- usage of first_name and last_name attributes on User classes that don't have them.

## [0.4.5] - 24 Sep 2015
### Fixed
- Exception if no `$user->created_at` attribute exists.

## [0.4.0 - 0.4.4] - 13 Sep 2015
### Fixed
- FQCN references in event handler.
- namespace for controller in routes file.
- namespace for service provider.
- path to routes file.

### Changed
- Move to new repository, change namespace.
- Change referer detection.

## [0.3.12 - 0.3.13] - 2015-06-18
### Added
- Identify user when tracking Page View if logged in.
- Update "Last Seen" timestamp when logged-in user views a page.

## [0.3.1 - 0.3.11] - 2015-06-17
### Added
- Referrer is now also recorded in Page View tracks.

### Removed
- Temporarily disabled alias() until its purpose and usefulness is better assessed.

### Changed
- Namespace HTTP/Controllers changed to Http/Controllers.
- Fixed method to detect current URL.

### Fixed
- Attempt at fixing client IP detection.
- PHP version requirement updated to >5.5.
- Fix namespace and path references.
- Fix URL and Route detection for 'Page View' tracking.
- Fixed detection of Page View elements to only be added if they exist.

## [0.3.0] - 2015-06-16
### Changed
- Upgraded to Laravel 5.1

### Added
- Page View tracking.
- MixPanel alias() on registration to enabled proper funneling.

## [0.2.13] - 2015-06-10
### Added
- Ignore transfer transactions.

## [0.2.11 - 0.2.12] - 2015-06-09
### Added
- Additional check to detect stripe customer number.
- Tightened sanity checks on subscription user detection.

## [0.2.10] - 2015-06-06
### Fixed
- Extracted logic to get stripe customer id and throw exception if not found.

## [0.2.8 - 0.2.9] - 2015-06-05
### Fixed
- Fixed logic error in customer ID parsing.

## [0.2.7] - 2015-06-04
### Fixed
- Refactored subscription update functionality to be a little more robust. Testing all aspects of this has proven
difficult, as Stripes webhook tests don't account for all variations possible in a given webhook request type.

## [0.2.6] - 2015-06-03
### Changed
- Added FromPlan and ToPlan when tracking subscription changes.

### Fixed
- Subscription updates check for previous subscription information, as not all subscription changes have it.

## [0.2.5] - 2015-06-2
### Fixed
- Checked for existence of user when logging out before identifying with MixPanel.

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
