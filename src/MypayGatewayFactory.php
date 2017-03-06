<?php

namespace PayumTW\Mypay;

use LogicException;
use Payum\Core\GatewayFactory;
use PayumTW\Mypay\Action\SyncAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Mypay\Action\NotifyAction;
use PayumTW\Mypay\Action\StatusAction;
use PayumTW\Mypay\Action\CaptureAction;
use PayumTW\Mypay\Action\NotifyNullAction;
use Payum\Core\Bridge\Httplug\HttplugClient;
use Http\Client\Curl\Client as HttpCurlClient;
use PayumTW\Mypay\Action\ConvertPaymentAction;
use Http\Adapter\Buzz\Client as HttpBuzzClient;
use Http\Client\Socket\Client as HttpSocketClient;
use Http\Adapter\Guzzle5\Client as HttpGuzzle5Client;
use Http\Adapter\Guzzle6\Client as HttpGuzzle6Client;
use PayumTW\Mypay\Action\Api\CreateTransactionAction;
use PayumTW\Mypay\Action\Api\GetTransactionDataAction;

class MypayGatewayFactory extends GatewayFactory
{
    /**
     * getDefaultHttpClient.
     *
     * @param \Payum\Core\Bridge\Spl\ArrayObject $config
     * @return \Http\Client\HttpClient
     */
    public function getDefaultHttpClient($config)
    {
        $classes = [
            HttpGuzzle6Client::class => function () {
                return HttpGuzzle6Client::createWithConfig([
                    'verify' => false,
                ]);
            },
            HttpGuzzle5Client::class => function () {
                return new HttpGuzzle5Client([
                    'defaults' => [
                        'verify' => false,
                    ],
                ]);
            },
            HttpSocketClient::class => function () {
                return new HttpSocketClient(null, [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ]);
            },
            HttpCurlClient::class => function () use ($config) {
                return new HttpCurlClient($config['httplug.message_factory'], $config['httplug.stream_factory'], [
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);
            },
            HttpBuzzClient::class => function () {
                $client = new HttpBuzzClient();
                $client->setVerifyPeer(false);

                return $client;
            },
        ];

        foreach ($classes as $className => $closure) {
            if (class_exists($className) === true) {
                return $closure();
            }
        }

        throw new LogicException('The httplug.message_factory could not be guessed. Install one of the following packages: php-http/guzzle6-adapter, zendframework/zend-diactoros. You can also overwrite the config option with your implementation.');
    }

    /**
     * getClientIp.
     *
     * @param array $server
     * @return bool
     */
    public function getClientIp($server)
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
            if (array_key_exists($key, $server) === true) {
                return $server[$key];
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

        $httpClient = $this->getDefaultHttpClient($config);
        $config->replace([
            'httplug.client' => $httpClient,
            'payum.http_client' => new HttplugClient($httpClient),
        ]);

        $server = isset($config['server']) === true ? $config['server'] : $_SERVER;
        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'store_uid' => null,
                'key' => null,
                'ip' => $this->getClientIp($server),
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
