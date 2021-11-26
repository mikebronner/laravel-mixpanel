# MixPanel for Laravel

[![Scrutinizer](https://img.shields.io/scrutinizer/g/GeneaLabs/laravel-mixpanel.svg)](https://scrutinizer-ci.com/g/GeneaLabs/laravel-mixpanel)
[![Coveralls](https://img.shields.io/coveralls/GeneaLabs/laravel-mixpanel.svg)](https://coveralls.io/github/GeneaLabs/laravel-mixpanel)
[![GitHub (pre-)release](https://img.shields.io/github/release/GeneaLabs/laravel-mixpanel/all.svg)](https://github.com/GeneaLabs/laravel-mixpanel)
[![Packagist](https://img.shields.io/packagist/dt/GeneaLabs/laravel-mixpanel.svg)](https://packagist.org/packages/genealabs/laravel-mixpanel)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/GeneaLabs/laravel-mixpanel/master/LICENSE)

![Mixpanel for Laravel masthead image.](https://repository-images.githubusercontent.com/42419266/0f534200-f1b5-11e9-9ca7-57b0e1fe7764)

## Sponsors
We like to thank the following sponsors for their generosity. Please take a moment to check them out.

- [LIX](https://lix-it.com)

## Features
- Asynchronous data transmission to Mixpanel's services. This prevents any
 delays to your application if Mixpanel is down, or slow to respond.
- Drop-in installation and configuration into your Laravel app, tracking the
 most common events out of the box.
- Simple Stripe integration allowing you to track revenues at the user level.
- Front-end-ready Mixpanel JS library, both for Laravel Elixir inclusion or
 Blade template use.

## Requirements and Compatibility
- PHP >= 7.2
- Laravel >= 8.0

### Legacy Versions
- [Laravel 5.2](https://github.com/GeneaLabs/laravel-mixpanel/tree/afcf3737412c1aebfa9dd1d7687001f78bdb3956)
- [Laravel 5.0](https://github.com/GeneaLabs/laravel-mixpanel/tree/ce110ebd89658cbf8a91f2cfb5db57e2b449e7f3)

## Installation
1. Install the package:
    ```sh
    composer require genealabs/laravel-mixpanel
    ```
2. Add your Mixpanel API Token to your `.env` file:
    ```env
    MIXPANEL_TOKEN=xxxxxxxxxxxxxxxxxxxxxx
    ```
3. Add the MixPanel Host domain only if you need to change your MixPanel host from the default:
    ```env
    MIXPANEL_TOKEN=xxxxxxxxxxxxxxxxxxxxxx
    ```

## Configuration
### Default Values
- `services.mixpanel.host`: pulls the 'MIXPANEL_HOST' value from your `.env`
    file.
- `services.mixpanel.token`: pulls the 'MIXPANEL_TOKEN' value from your `.env`
    file.
- `services.mixpanel.enable-default-tracking`: (default: true) enable or disable
    Laravel user event tracking.
- `services.mixpanel.consumer`: (default: socket) set the Guzzle adapter you
    want to use.
- `services.mixpanel.connect-timeout`: (default: 2) set the number of seconds
    after which connections timeout.
- `services.mixpanel.timeout`: (default: 2) set the number of seconds after
    which event tracking times out.
- `services.mixpanel.data_callback_class`: (default: null) manipulate the data
    being passed back to mixpanel for the track events.

## Upgrade Notes
### Version 0.7.0 for Laravel 5.5
- Remove the service provider from `/config/app.php`. The service provider is
    now auto-discovered in Laravel 5.5.

### Page Views
- Page view tracking has been removed in favor of Mixpanels in-built Autotrack
    functionality, which tracks all page views. To turn it on, visit your
    Mixpanel dashboard, click *Applications > Autotrack > Web > etc.* and enable
    Autotracking.

## Usage
MixPanel is loaded into the IoC as a singleton. This means you don't have to
    manually call $mixPanel::getInstance() as described in the MixPanel docs.
    This is already done for you in the ServiceProvider.

Common user events are automatically recorded:
- User Registration
- User Deletion
- User Login
- User Login Failed
- User Logoff
- Cashier Subscribed
- Cashier Payment Information Submitted
- Cashier Subscription Plan Changed
- Cashier Unsubscribed

To make custom events, simple get MixPanel from the IoC using DI:
```php
use GeneaLabs\LaravelMixpanel\LaravelMixpanel;

class MyClass
{
    protected $mixPanel;

    public function __construct(LaravelMixPanel $mixPanel)
    {
        $this->mixPanel = $mixPanel;
    }
}
```

If DI is impractical in certain situations, you can also manually retrieve it
    from the IoC:
```php
$mixPanel = app('mixpanel'); // using app helper
$mixPanel = Mixpanel::getFacadeRoot(); // using facade
```

After that you can make the usual calls to the MixPanel API:
- `$mixPanel->identify($user->id);`
- `$mixPanel->track('User just paid!');`
- `$mixPanel->people->trackCharge($user->id, '9.99');`
- `$mixPanel->people->set($user->id, [$data]);`

  And so on ...

  ### Stripe Web-Hook
  If you wish to take advantage of the Stripe web-hook and track revenue per
    user, you should install Cashier: https://www.laravel.com/docs/5.5/billing

  Once that has been completed, exempt the web-hook endpoint from CSRF-validation
   in `/app/Http/Middleware/VerifyCsrfToken.php`:
  ```php
      protected $except = [
          'genealabs/laravel-mixpanel/stripe',
      ];
  ```

  The only other step remaining is to register the web-hook with Stripe:
    Log into your Stripe account: https://dashboard.stripe.com/dashboard, and open
     your account settings' webhook tab:

    Enter your MixPanel web-hook URL, similar to the following: `http://<your server.com>/genealabs/laravel-mixpanel/stripe`:
     ![screen shot 2015-05-31 at 1 35 01 pm](https://cloud.githubusercontent.com/assets/1791050/7903765/53ba6fe4-079b-11e5-9f92-a588bd81641d.png)

    Be sure to select "Live" if you are actually running live (otherwise put into test mode and update when you go live).
     Also, choose "Send me all events" to make sure Laravel Mixpanel can make full use of the Stripe data.

  ### JavaScript Events & Auto-Track
  #### Blade Template (Recommended)
  First publish the necessary assets:
  ```sh
  php artisan mixpanel:publish --assets
  ```

  Then add the following to the head section of your layout template (already does
   the init call for you, using the token from your .env file):
  ```blade
  @include('genealabs-laravel-mixpanel::partials.mixpanel')
  ```

  #### Laravel Elixir
  Add the following lines to your `/resources/js/app.js` (or equivalent), and
   don't forget to replace `YOUR_MIXPANEL_TOKEN` with your actual token:
  ```js
  require('./../../../public/genealabs-laravel-mixpanel/js/mixpanel.js');
  mixpanel.init("YOUR_MIXPANEL_TOKEN");
  ```

### Laravel Integration
Out of the box it will record the common events anyone would want to track. Also, if the default `$user->name` field is
used that comes with Laravel, it will split up the name and use the last word as the last name, and everything prior for
the first name. Otherwise it will look for `first_name` and `last_name` fields in the users table.

- User registers:
  ```
  Track:
    User:
      - Status: Registered
  People:
    - $first_name: <user's first name>
    - $last_name: <user's last name>
    - $email: <user's email address>
    - $created: <date user registered>
  ```

- User is updated:
  ```
  People:
    - $first_name: <user's first name>
    - $last_name: <user's last name>
    - $email: <user's email address>
    - $created: <date user registered>
  ```

- User is deleted:
  ```
  Track:
    User:
      - Status: Deactivated
  ```

- User is restored (from soft-deletes):
  ```
  Track:
    User:
      - Status: Reactivated
  ```

- User logs in:
  ```
  Track:
    Session:
      - Status: Logged In
  People:
    - $first_name: <user's first name>
    - $last_name: <user's last name>
    - $email: <user's email address>
    - $created: <date user registered>
  ```

- User login fails:
  ```
  Track:
    Session:
      - Status: Login Failed
  People:
    - $first_name: <user's first name>
    - $last_name: <user's last name>
    - $email: <user's email address>
    - $created: <date user registered>
  ```

- User logs out:
  ```
  Track:
    Session:
      - Status: Logged Out
  ```
### Tracking Data Manipulation
If you need to make changes or additions to the data being tracked, create a
  class that implements `\GeneaLabs\LaravelMixpanel\Interfaces\DataCallback`:

```php
<?php

namespace App;

use GeneaLabs\LaravelMixpanel\Interfaces\DataCallback;

class MixpanelUserData implements DataCallback
{
    public function process(array $data = []) : array
    {
        $data["test"] = "value";

        return $data;
    }
}
```

Then register this class in your `services` configuration:

```php
    'mixpanel' => [
      // ...
        "data_callback_class" => \App\MixpanelUserData::class,
    ]
```

### Stripe Integration
Many L5 sites are running Cashier to manage their subscriptions. This package creates an API webhook endpoint that keeps
 vital payment analytics recorded in MixPanel to help identify customer churn.

Out of the box it will record the following Stripe events in MixPanel for you:

#### Charges
- Authorized Charge (when only authorizing a payment for a later charge date):
  ```
  Track:
    Payment:
      - Status: Authorized
      - Amount: <amount authorized>
  ```

- Captured Charge (when completing a previously authorized charge):
  ```
  Track:
    Payment:
      - Status: Captured
      - Amount: <amount of payment>
  People TrackCharge: <amount of intended payment>
  ```

- Completed Charge:
  ```
  Track:
    Payment:
      - Status: Successful
      - Amount: <amount of payment>
  People TrackCharge: <amount of payment>
  ```

- Refunded Charge:
  ```
  Track:
    Payment:
      - Status: Refunded
      - Amount: <amount of refund>
  People TrackCharge: -<amount of refund>
  ```

- Failed Charge:
  ```
  Track:
    Payment:
      - Status: Failed
      - Amount: <amount of intended payment>
  ```

### Subscriptions
- Customer subscribed:
  ```
  Track:
    Subscription:
      - Status: Created
  People:
    - Subscription: <plan name>
  ```

- Customer unsubscribed:
  ```
  Track:
    Subscription:
      - Status: Canceled
      - Upgraded: false
    Churn! :(
  People:
    - Subscription: None
    - Churned: <date canceled>
    - Plan When Churned: <subscribed plan when canceled>
    - Paid Lifetime: <number of days from subscription to cancelation> days
  ```

- Customer started trial:
  ```
  Track:
    Subscription:
      - Status: Trial
  People:
    - Subscription: Trial
  ```

- Customer upgraded plan:
  ```
  Track:
    Subscription:
      - Upgraded: true
    Unchurn! :-)
  People:
    - Subscription: <new plan name>
  ```

- Customer downgraded plan (based on dollar value compared to previous plan):
  ```
  Track:
    Subscription:
      - Upgraded: false
    Churn! :-(
  People:
    - Subscription: <new plan name>
    - Churned: <date plan was downgraded>
    - Plan When Churned: <plan name prior to downgrading>
  ```

# The Fine Print
## Commitment to Quality
During package development I try as best as possible to embrace good design and
development practices to try to ensure that this package is as good as it can
be. My checklist for package development includes:

-   ✅ Achieve as close to 100% code coverage as possible using unit tests.
-   ✅ Eliminate any issues identified by SensioLabs Insight and Scrutinizer.
-   ✅ Be fully PSR1, PSR2, and PSR4 compliant.
-   ✅ Include comprehensive documentation in README.md.
-   ✅ Provide an up-to-date CHANGELOG.md which adheres to the format outlined
    at <http://keepachangelog.com>.
-   ✅ Have no PHPMD or PHPCS warnings throughout all code.

## Contributing
Please observe and respect all aspects of the included Code of Conduct <https://github.com/GeneaLabs/laravel-model-caching/blob/master/CODE_OF_CONDUCT.md>.

### Reporting Issues
When reporting issues, please fill out the included template as completely as
possible. Incomplete issues may be ignored or closed if there is not enough
information included to be actionable.

### Submitting Pull Requests
Please review the Contribution Guidelines <https://github.com/GeneaLabs/laravel-model-caching/blob/master/CONTRIBUTING.md>.
Only PRs that meet all criterium will be accepted.

## ❤️ Open-Source Software - Give ⭐️
We have included the awesome `symfony/thanks` composer package as a dev
dependency. Let your OS package maintainers know you appreciate them by starring
the packages you use. Simply run composer thanks after installing this package.
(And not to worry, since it's a dev-dependency it won't be installed in your
live environment.)
