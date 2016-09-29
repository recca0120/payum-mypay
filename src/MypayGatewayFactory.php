<?php

namespace PayumTW\Mypay;

use Http\Adapter\Buzz\Client as HttpBuzzClient;
use Http\Adapter\Guzzle5\Client as HttpGuzzle5Client;
use Http\Adapter\Guzzle6\Client as HttpGuzzle6Client;
use Http\Client\Curl\Client as HttpCurlClient;
use Http\Client\Socket\Client as HttpSocketClient;
use Http\Discovery\HttpClientDiscovery;
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

        $config->replace([
            'httplug.client' => function (ArrayObject $config) {
                // if (class_exists(HttpClientDiscovery::class)) {
                //     return HttpClientDiscovery::find();
                // }

                if (class_exists(HttpGuzzle6Client::class)) {
                    return HttpGuzzle6Client::createWithConfig([
                        'verify' => false,
                    ]);
                }

                if (class_exists(HttpGuzzle5Client::class)) {
                    return new HttpGuzzle5Client([
                        'defaults' => [
                            'verify' => false,
                        ],
                    ]);
                }

                if (class_exists(HttpSocketClient::class)) {
                    return new HttpSocketClient(null, [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                        ],
                    ]);
                }

                if (class_exists(HttpCurlClient::class)) {
                    return new HttpCurlClient($config['httplug.message_factory'], $config['httplug.stream_factory'], [
                        CURLOPT_SSL_VERIFYHOST => false,
                        CURLOPT_SSL_VERIFYPEER => false,
                    ]);
                }

                if (class_exists(HttpBuzzClient::class)) {
                    $client = new HttpBuzzClient();
                    $client->setVerifyPeer(false);

                    return $client;
                }

                throw new \LogicException('The httplug.client could not be guessed. Install one of the following packages: php-http/guzzle6-adapter. You can also overwrite the config option with your implementation.');
            },
            'payum.http_client' => function (ArrayObject $config) {
                return new HttplugClient($config['httplug.client']);
            },
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'store_uid' => null,
                'key' => null,
                'ip' => $_SERVER['REMOTE_ADDR'],
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
