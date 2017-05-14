# MixPanel for Laravel 5
## Features
- Asynchronous data transmission to Mixpanel's services. This prevents any
 delays to your application if Mixpanel is down, or slow to respond.
- Drop-in installation and configuration into your Laravel app, tracking the
 most common events out of the box.
- Simple Stripe integration allowing you to track revenues at the user level.
- Front-end-ready Mixpanel JS library, both for Laravel Elixir inclusion or
 Blade template use.

## Requirements and Compatibility
- PHP 7
- Laravel 5.1 (LTS)
- Laravel 5.3
- Laravel 5.4 (current)

### Legacy Versions
- [Laravel 5.2](https://github.com/GeneaLabs/laravel-mixpanel/tree/afcf3737412c1aebfa9dd1d7687001f78bdb3956)
- [Laravel 5.0](https://github.com/GeneaLabs/laravel-mixpanel/tree/ce110ebd89658cbf8a91f2cfb5db57e2b449e7f3)

## Installation
```sh
composer require genealabs/laravel-mixpanel
```

Add the service provider entry in `config\app.php`:
```php
GeneaLabs\LaravelMixpanel\Providers\LaravelMixpanelService::class,
```

Verify that your auth configuration file `config/auth.php` has the user model
 specified in `auth.providers.users.model` (or in `auth.model` for L5.1). If
 that entry is missing, go ahead and add it.
```php
// Laravel 5.3
'providers' => [
    'users' => [
        'driver' => '...',
        'model' => App\User::class,
    ],

// Laravel 5.1
'model' => App\User::class,
```

Lastly, add your Mixpanel API token to your `.env` file:
```env
MIXPANEL_TOKEN=xxxxxxxxxxxxxxxxxxxxxx
```

## Configuration
### Default Values
- `services.mixpanel.token`: pulls the 'MIXPANEL_TOKEN' value from your `.env`
 file.
- `services.mixpanel.enable-default-tracking`: (default: true) enable or disable Laravel user
 event tracking.
- `services.mixpanel.consumer`: (default: socket) set the Guzzle adapter you want to use.
- `services.mixpanel.connect-timeout`: (default: 2) set the number of seconds after which
 connections timeout.
- `services.mixpanel.timeout`: (default: 2) set the number of seconds after which event tracking
 times out.

## Upgrade Notes
### Page Views
- Page view tracking has been removed in favor of Mixpanels in-built Autotrack functionality, which tracks all page views. To turn it on, visit your Mixpanel dashboard, click *Applications > Autotrack > Web > etc.* and enable Autotracking.

## Usage
### PHP Events

### Stripe Web-Hook
If you wish to take advantage of the Stripe web-hook and track revenue per user,
 you should install Cashier:
- [Laravel 5.4](https://www.laravel.com/docs/5.4/billing)
- [Laravel 5.3](https://www.laravel.com/docs/5.3/billing)
- [Laravel 5.1](https://www.laravel.com/docs/5.1/billing)

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
@include ('genealabs-laravel-mixpanel::partials.mixpanel')

<script>
    mixpanel.init("YOUR_MIXPANEL_TOKEN");
</script>
```

#### Laravel Elixir
Add the following lines to your `/resources/js/app.js` (or equivalent), and
 don't forget to replace `YOUR_MIXPANEL_TOKEN` with your actual token:
```js
require('./../../../public/genealabs-laravel-mixpanel/js/mixpanel.js');
mixpanel.init("YOUR_MIXPANEL_TOKEN");
```

## Usage
MixPanel is loaded into the IoC as a singleton. This means you don't have to manually call $mixPanel::getInstance() as
described in the MixPanel docs. This is already done for you in the ServiceProvider.

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
use GeneaLabs\LaravelMixPanel\LaravelMixPanel;

class MyClass
{
    protected $mixPanel;

    public function __construct(LaravelMixPanel $mixPanel)
    {
        $this->mixPanel = $mixPanel;
    }
}
```

If DI is impractical in certain situations, you can also manually retrieve it from the IoC:
```php
$mixPanel = app('mixpanel');
```

After that you can make the usual calls to the MixPanel API:
- `$mixPanel->identify($user->id);`
- `$mixPanel->track('User just paid!');`
- `$mixPanel->people->trackCharge($user->id, '9.99');`
- `$mixPanel->people->set($user->id, [$data]);`

  And so on ...

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
