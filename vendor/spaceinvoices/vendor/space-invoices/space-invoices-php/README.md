# Space Invoices PHP SDK

The Space Invoices SDK provides an easy way to access Space Invoices API from application written PHP.

## Documentation

 **Detailed documentation about the API can be found at [docs.spaceinvoices.com](http://docs.spaceinvoices.com)**

**We also invite you to join our Slack community channel [Space Invaders](http://joinslack.spaceinvoices.com)**

## Installation

Install the package with [Composer](http://getcomposer.org/)
```
composer require space-invoices/space-invoices-php
composer install
```
Then you can use the Composer's [autoload](https://getcomposer.org/doc/01-basic-usage.md#autoloading)
```php
require_once('vendor/autoload.php');
```

## Usage

**TOKEN** and **ACCOUNT_ID** can be obtained by signing up for a developer account on our website: [spaceinvoices.com](http://spaceinvoices.com)

``` php
Spaceinvoices\Spaceinvoices::setAccessToken('TOKEN');
```

Example usage of SpaceInvoices SDK for creating an Organization.
``` php
$accountId = 'ACCOUNT_ID';

$create = Spaceinvoices\Organizations::create($accountId, array(
  "name" => "SpaceX",
  "country" => "USA"
));

var_dump($create);

```

Visit our website [spaceinvoices.com](http://spaceinvoices.com)