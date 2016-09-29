<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Mypay\MypayGatewayFactory;

class MypayGatewayFactoryTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_create_factory()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $httpClient = m::mock('Payum\Core\HttpClientInterface');
        $message = m::mock('Http\Message\MessageFactory');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $_SERVER['REMOTE_ADDR'] = '::1';
        $gateway = new MypayGatewayFactory();
        $config = $gateway->createConfig([
            'payum.api' => false,
            'store_uid' => md5(rand()),
            'key' => md5(rand()),
            'ip' => '::1',
            'sandbox' => false,
            'payum.required_options' => [],
            'payum.http_client' => $httpClient,
            'httplug.message_factory' => $message,
        ]);

        $config['httplug.client'] = call_user_func($config['httplug.client'], ArrayObject::ensureArrayObject($config));
        $config['payum.http_client'] = call_user_func($config['payum.http_client'], ArrayObject::ensureArrayObject($config));
        $api = call_user_func($config['payum.api'], ArrayObject::ensureArrayObject($config));
        $this->assertInstanceOf('PayumTW\Mypay\Api', $api);
    }
}
