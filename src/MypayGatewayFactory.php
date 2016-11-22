<?php

namespace PayumTW\Mypay;

use Http\Adapter\Buzz\Client as HttpBuzzClient;
use Http\Adapter\Guzzle5\Client as HttpGuzzle5Client;
use Http\Adapter\Guzzle6\Client as HttpGuzzle6Client;
use Http\Client\Curl\Client as HttpCurlClient;
use Http\Client\Socket\Client as HttpSocketClient;
use LogicException;
use Payum\Core\Bridge\Httplug\HttplugClient;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use PayumTW\Mypay\Action\Api\CreateTransactionAction;
use PayumTW\Mypay\Action\Api\GetTransactionDataAction;
use PayumTW\Mypay\Action\CaptureAction;
use PayumTW\Mypay\Action\ConvertPaymentAction;
use PayumTW\Mypay\Action\NotifyAction;
use PayumTW\Mypay\Action\NotifyNullAction;
use PayumTW\Mypay\Action\StatusAction;
use PayumTW\Mypay\Action\SyncAction;

class MypayGatewayFactory extends GatewayFactory
{
    public function getHttpClient($class)
    {
        switch ($class) {
            case HttpGuzzle6Client::class:
                $client = HttpGuzzle6Client::createWithConfig([
                    'verify' => false,
                ]);
                break;

            case HttpGuzzle5Client::class:
                $client = new HttpGuzzle5Client([
                    'defaults' => [
                        'verify' => false,
                    ],
                ]);
                break;

            case HttpSocketClient::class:
                $client = new HttpSocketClient(null, [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ]);
                break;

            case HttpCurlClient::class:
                $client = new HttpCurlClient($config['httplug.message_factory'], $config['httplug.stream_factory'], [
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);
                break;

            case HttpBuzzClient::class:
                $client = new HttpBuzzClient();
                $client->setVerifyPeer(false);
                break;

            default:
                throw new LogicException('The httplug.message_factory could not be guessed. Install one of the following packages: php-http/guzzle6-adapter, zendframework/zend-diactoros. You can also overwrite the config option with your implementation.');
                break;
        }

        return $client;
    }

    public function getDefaultHttpClient()
    {
        $classes = [
            HttpGuzzle6Client::class,
            HttpGuzzle5Client::class,
            HttpSocketClient::class,
            HttpCurlClient::class,
            HttpBuzzClient::class,
        ];

        foreach ($classes as $class) {
            if (class_exists($class) === true) {
                return $this->getHttpClient($class);
            }
        }

        throw new LogicException('The httplug.message_factory could not be guessed. Install one of the following packages: php-http/guzzle6-adapter, zendframework/zend-diactoros. You can also overwrite the config option with your implementation.');
    }

    public function getClientIp()
    {
        $keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                return $_SERVER[$key];
            }
        }

        return '::1';
    }

    /**
     * {@inheritdoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'mypay',
            'payum.factory_title' => 'Mypay',

            'payum.action.capture' => new CaptureAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.notify_null' => new NotifyNullAction(),
            'payum.action.sync' => new SyncAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),

            'payum.action.api.create_transaction' => new CreateTransactionAction(),
            'payum.action.api.get_transaction_data' => new GetTransactionDataAction(),
        ]);

        $httpClient = $this->getDefaultHttpClient();
        $config->replace([
            'httplug.client' => $httpClient,
            'payum.http_client' => new HttplugClient($httpClient),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'store_uid' => null,
                'key' => null,
                'ip' => $this->getClientIp(),
                'sandbox' => true,
            ];

            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['store_uid', 'key'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
