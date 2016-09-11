# Mypay

[![StyleCI](https://styleci.io/repos/67210412/shield?style=flat)](https://styleci.io/repos/67210412)
[![Build Status](https://travis-ci.org/recca0120/payum-mypay.svg)](https://travis-ci.org/recca0120/payum-mypay)
[![Total Downloads](https://poser.pugx.org/payum-tw/mypay/d/total.svg)](https://packagist.org/packages/payum-tw/mypay)
[![Latest Stable Version](https://poser.pugx.org/payum-tw/mypay/v/stable.svg)](https://packagist.org/packages/payum-tw/mypay)
[![Latest Unstable Version](https://poser.pugx.org/payum-tw/mypay/v/unstable.svg)](https://packagist.org/packages/payum-tw/mypay)
[![License](https://poser.pugx.org/payum-tw/mypay/license.svg)](https://packagist.org/packages/payum-tw/mypay)
[![Monthly Downloads](https://poser.pugx.org/payum-tw/mypay/d/monthly)](https://packagist.org/packages/payum-tw/mypay)
[![Daily Downloads](https://poser.pugx.org/payum-tw/mypay/d/daily)](https://packagist.org/packages/payum-tw/mypay)

The Payum extension to rapidly build new extensions.

1. Create new project

```bash
$ composer create-project payum-tw/mypay
```

2. Replace all occurrences of `payum` with your vendor name. It may be your github name, for now let's say you choose: `acme`.
3. Replace all occurrences of `mypay` with a payment gateway name. For example Stripe, Paypal etc. For now let's say you choose: `mypay`.
4. Register a gateway factory to the payum's builder and create a gateway:

```php
<?php

use Payum\Core\PayumBuilder;
use Payum\Core\GatewayFactoryInterface;

$defaultConfig = [];

$payum = (new PayumBuilder)
    ->addGatewayFactory('mypay', function(array $config, GatewayFactoryInterface $coreGatewayFactory) {
        return new \PayumTW\Allpay\AllpayGatewayFactory($config, $coreGatewayFactory);
    })

    ->addGateway('mypay', [
        'factory' => 'mypay',
        'MerCode' => null,
        'MerKey'  => null,
        'MerName' => null,
        'Account' => null,
        'sandbox' => true,
    ])

    ->getPayum()
;
```

5. While using the gateway implement all method where you get `Not implemented` exception:

```php
<?php

use Payum\Core\Request\Capture;

$mypay = $payum->getGateway('mypay');

$model = new \ArrayObject([
  // ...
]);

$mypay->execute(new Capture($model));
```

## Resources

* [Documentation](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/index.md)
* [Questions](http://stackoverflow.com/questions/tagged/payum)
* [Issue Tracker](https://github.com/Payum/Payum/issues)
* [Twitter](https://twitter.com/payumphp)

## License

Skeleton is released under the [MIT License](LICENSE).
