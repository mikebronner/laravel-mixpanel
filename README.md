# MixPanel for Laravel 5
## Installation
1. Install MixPanel via composer:
  ```sh
  composer require genealabs\mixpanel:~0.1
  ```

2. Add the service provider entry in `config\app.php`:
  ```php
          'GeneaLabs\MixPanel\MixPanelServiceProvider',
  ```

3. Add the facade alias entry in `config\app.php`:
  ```php
          'MixPanel'  => 'GeneaLabs\MixPanel\Facades\MixPanel',
  ```

## Configuration
1. Update your `.env` file with your MixPanel token:
  ```
  MIXPANEL_TOKEN=xxxxxxxxxxxxxxxxxxxxxx
  ```

2. Load the token in the services configuration (`config\services.php`):
  ```php
      'mixpanel' => [
          'token' => env('MIXPANEL_TOKEN'),
      ],
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
use GeneaLabs\MixPanel\MixPanel;

class MyClass
{
    protected $mixPanel;

    public function __construct(MixPanel $mixPanel)
    {
        $this->mixPanel = $mixPanel;
    }
}
```

If DI is impractical in certain situations, you can also manually retrieve it from the IoC:
```php
$mixPanel = App::make('GeneaLabs\MixPanel\MixPanel');
```
