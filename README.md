# MixPanel for Laravel 5
## Considerations
This package adds the multiple routes under `genealabs/laravel-mixpanel/*`. Please verify that these don't collide with your
existing routes.

## Installation
1. Install via composer
  - Laravel 5.3.x: `composer require genealabs/laravel-mixpanel:0.6.*`
  - Laravel 5.2.x: `composer require genealabs/laravel-mixpanel:0.5.*`
  - Laravel 5.1.x: `composer require genealabs/laravel-mixpanel:0.4.*`
  - Laravel 5.0.x: `composer require genealabs/laravel-mixpanel:0.2.*`

2. Add the service provider entry in `config\app.php`:
  ```php
  GeneaLabs\LaravelMixpanel\Providers\LaravelMixpanelServiceProvider::class,
  ```

## Configuration
1. If you are using Laravel 5.2 or above, add the following entry to `config/auth.php`. Be sure to use the correct
   namespace for your application. (Yes, this is a duplicate of `providers.users.model`, but necessary for now, in case
   a different driver is used.
  ```php
      'model' => App\User::class,
  ```

2. Update your `.env` file with your MixPanel token (it will automatically be
 picked up by the in-built configuration):
  ```
  MIXPANEL_TOKEN=xxxxxxxxxxxxxxxxxxxxxx
  ```
3. If you are running Laravel < 5.2, or if you want to disable the in-built default tracking and implement your own,
 add the following to your services configuration (`config\services.php`):
  ```php
      'mixpanel' => [
        'token' => env('MIXPANEL_TOKEN'),
        'enable-default-tracking' => false|true,
      ],
  ```
  Disabling the default hooks will not disable the Stripe web-hook functionality.

4. If to track the user's names, make sure a `name` attribute is available on your user model. For example, if you only
  have a `username` field that contains the users' first and last names, you could add the following to your user model:
  ```php
      public function getNameAttribute()
      {
          return $this->username;
      }
  ```

5. We need to disable CSRF checking for the stripe webhook endpoints. To do that, open
 `app/HTTP/Middleware/VerifyCsrfToken.php` and add the following above the return statement:
  ```php
          if ($request->is('genealabs/laravel-mixpanel/*')) {
              return $next($request);
          }
  ```

  For Laravel 5.2+:
  ```php
      protected $except = [
        'genealabs/laravel-mixpanel/*',
        // your other CSRF token exceptions
    ];
  ```

6. Configure Stripe webhook (if you're using Stripe):
  Log into your Stripe account: https://dashboard.stripe.com/dashboard, and open your account settings' webhook tab:

  Enter your MixPanel webhook URL, similar to the following: `http://<your server.com>/genealabs/laravel-mixpanel/stripe/transaction`:
  ![screen shot 2015-05-31 at 1 35 01 pm](https://cloud.githubusercontent.com/assets/1791050/7903765/53ba6fe4-079b-11e5-9f92-a588bd81641d.png)

  Be sure to select "Live" if you are actually running live (otherwise put into test mode and update when you go live).
  Also, choose "Send me all events" to make sure the mixpanel endpoint can make full use of the Stripe data.

### Front-end Tracking (Mixpanel JS and Autotrack)
Firt publish the required assets:
```sh
php artisan mixpanel:publish --assets
```

#### Laravel Elixir (recommended)
Add the following lines to your `/resources/js/app.js` (or equivalent), and
 don't forget to replace `YOUR_MIXPANEL_TOKEN` with your actual token:
```js
global.mixpanel = require('./../../../public/vendor/genealabs-laravel-mixpanel/js/mixpanel.js');
mixpanel.init("YOUR_MIXPANEL_TOKEN");
```

#### Standalone Script
Add the following _Blade_ directive in the `<head></head>` section of your
 layout file (they will load asynchronously and not cause render-blocking):
```html
@include ('genealabs-laravel-mixpanel::partials.mixpanel')
```

## Usage
MixPanel is loaded into the IoC as a singleton. This means you don't have to manually call $mixPanel::getInstance() as
described in the MixPanel docs. This is already done for you in the ServiceProvider.

Common user events are automatically recorded:
- Page View
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
$mixPanel = App::make('GeneaLabs\LaravelMixPanel\LaravelMixPanel');
```

After that you can make the usual calls to the MixPanel API:
- `$mixPanel->identify($user->id);`
- `$mixPanel->track('User just paid!');`
- `$mixPanel->people->trackCharge($user->id, '9.99');`
- `$mixPanel->people->set($user->id, [$data]);`

  And so on ...

## Laravel Integration
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

- View loaded:
  ```
  Track:
    Page View:
      - Url: <page URL>
      - Route: <route name>
      - Referrer: <referring URL>
      - Referring Domain: <referring domain>
      - IP (for geolocation)
      - Browser
      - Operating System
      - Hardware
  ```

## Stripe Integration
Many L5 sites are running Cashier to manage their subscriptions. This package creates an API webhook endpoint that keeps
 vital payment analytics recorded in MixPanel to help identify customer churn.

Out of the box it will record the following Stripe events in MixPanel for you:

### Charges
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
