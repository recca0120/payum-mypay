<?php

namespace PayumTW\Mypay\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Mypay\MypayGatewayFactory;

class MypayGatewayFactoryTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testCreateConfig()
    {
        $gateway = new MypayGatewayFactory();
        $config = $gateway->createConfig([
            'payum.http_client' => $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            'httplug.message_factory' => $messageFactory = m::mock('Http\Message\MessageFactory'),
            'store_uid' => $storeUid = md5(rand()),
            'key' => $key = md5(rand()),
            'ip' => '::1',
            'server' => [],
            'sandbox' => true,
        ]);
        $this->assertInstanceOf(
            'PayumTW\Mypay\Api',
            $config['payum.api'](ArrayObject::ensureArrayObject($config))
        );
    }
}
