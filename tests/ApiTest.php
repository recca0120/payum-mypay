<?php

namespace PayumTW\Mypay\Tests;

use Mockery as m;
use PayumTW\Mypay\Api;
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testGetApiEndpoint()
    {
        $api = new Api(
            $options = ['sandbox' => false, 'key' => md5(rand())],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter = m::mock('PayumTW\Mypay\Encrypter')
        );
        $this->assertSame('https://mypay.tw/api/init', $api->getApiEndpoint());

        $api = new Api(
            $options = ['sandbox' => true, 'key' => md5(rand())],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter = m::mock('PayumTW\Mypay\Encrypter')
        );
        $this->assertSame('https://pay.usecase.cc/api/init', $api->getApiEndpoint());
    }

    public function testCreateTransaction()
    {
        $api = new Api(
            $options = [
                'store_uid' => $storeUid = '123',
                'key' => $key = md5(rand()),
                'ip' => $ip = '::1',
                'sandbox' => true,
            ],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter = m::mock('PayumTW\Mypay\Encrypter')
        );

        $params = [
            'user_id' => $userId = 'phper',
            'item' => $count = 1,
            'items' => [
                [
                    'id' => $itemId = '0886449',
                    'name' => $itemName = '商品名稱',
                    'price' => $cost = 10,
                    'quantity' => $quantity = 1,
                    'total' => $total = 10,
                ],
            ],
            'order_id' => $orderId = '1234567890',
        ];

        $encrypter->shouldReceive('encryptRequest')->once()->with($storeUid, [
            'store_uid' => $storeUid,
            'user_id' => $userId,
            'user_cellphone_code' => '886',
            'cost' => 10,
            'currency' => 'TWD',
            'order_id' => $orderId,
            'ip' => $ip,
            'item' => $count,
            'pfn' => 'CREDITCARD',
            'enable_quickpay' => 1,
            'ewallet_type' => 1,
            'i_0_id' => $itemId,
            'i_0_name' => $itemName,
            'i_0_cost' => $cost,
            'i_0_amount' => $quantity,
            'i_0_total' => $total,
        ], 'api/orders')->andReturn($encryptParams = ['foo' => 'bar']);

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            $api->getApiEndpoint(),
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            http_build_query($encryptParams)
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('send')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getStatusCode')->once()->andReturn(200);
        $response->shouldReceive('getBody->getContents')->andReturn(
            json_encode($content = ['foo' => 'bar'])
        );

        $this->assertSame($content, $api->createTransaction($params));
    }

    public function testGetTransactionData()
    {
        $api = new Api(
            $options = [
                'store_uid' => $storeUid = '123',
                'key' => $key = md5(rand()),
                'ip' => $ip = '::1',
                'sandbox' => true,
            ],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter = m::mock('PayumTW\Mypay\Encrypter')
        );

        $params = [
            'uid' => $uid = md5(rand()),
            'key' => $key = md5(rand()),
        ];

        $encrypter->shouldReceive('encryptRequest')->once()->with($storeUid, $params, 'api/queryorder')->andReturn($encryptParams = ['foo' => 'bar']);

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            $api->getApiEndpoint(),
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            http_build_query($encryptParams)
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('send')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getStatusCode')->once()->andReturn(200);
        $response->shouldReceive('getBody->getContents')->andReturn(
            json_encode($content = ['foo' => 'bar'])
        );

        $this->assertSame($content, $api->getTransactionData($params));
    }

    public function testRefundTransactionData()
    {
        $api = new Api(
            $options = [
                'store_uid' => $storeUid = '123',
                'key' => $key = md5(rand()),
                'ip' => $ip = '::1',
                'sandbox' => true,
            ],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter = m::mock('PayumTW\Mypay\Encrypter')
        );

        $params = [
            'store_uid' => $storeUid,
            'uid' => $uid = md5(rand()),
            'key' => $key = md5(rand()),
            'cost' => $cost = 10,
        ];

        $encrypter->shouldReceive('encryptRequest')->once()->with($storeUid, $params, 'api/refund')->andReturn($encryptParams = ['foo' => 'bar']);

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            $api->getApiEndpoint(),
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            http_build_query($encryptParams)
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('send')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getStatusCode')->once()->andReturn(200);
        $response->shouldReceive('getBody->getContents')->andReturn(
            json_encode($content = ['foo' => 'bar'])
        );

        $this->assertSame($content, $api->refundTransaction($params));
    }

    public function testCancelTransactionData()
    {
        $api = new Api(
            $options = [
                'store_uid' => $storeUid = '123',
                'key' => $key = md5(rand()),
                'ip' => $ip = '::1',
                'sandbox' => true,
            ],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter = m::mock('PayumTW\Mypay\Encrypter')
        );

        $params = [
            'store_uid' => $storeUid,
            'uid' => $uid = md5(rand()),
            'key' => $key = md5(rand()),
        ];

        $encrypter->shouldReceive('encryptRequest')->once()->with($storeUid, $params, 'api/refundcancel')->andReturn($encryptParams = ['foo' => 'bar']);

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            $api->getApiEndpoint(),
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            http_build_query($encryptParams)
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('send')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getStatusCode')->once()->andReturn(200);
        $response->shouldReceive('getBody->getContents')->andReturn(
            json_encode($content = ['foo' => 'bar'])
        );

        $this->assertSame($content, $api->cancelTransaction($params));
    }

    public function testVerifyHash()
    {
        $api = new Api(
            $options = [
                'store_uid' => $storeUid = '123',
                'key' => $key = md5(rand()),
                'ip' => $ip = '::1',
                'sandbox' => true,
            ],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter = m::mock('PayumTW\Mypay\Encrypter')
        );
        $this->assertTrue($api->verifyHash(['key' => 'key'], ['key' => 'key']));
    }
}
