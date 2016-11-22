<?php

use Mockery as m;
use PayumTW\Mypay\Api;

class ApiTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_production_endpoint()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $httpClient = m::mock('Payum\Core\HttpClientInterface');
        $message = m::mock('Http\Message\MessageFactory');
        $request = m::mock('Psr\Http\Message\RequestInterface');
        $response = m::mock('stdClass');
        $options = [
            'store_uid' => '123',
            'key' => md5(rand()),
            'ip' => '::1',
            'sandbox' => false,
        ];
        $api = new Api($options, $httpClient, $message);

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
        $this->assertSame('https://mypay.tw/api/init', $api->getApiEndpoint());
    }

    public function test_sand_endpoint()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $httpClient = m::mock('Payum\Core\HttpClientInterface');
        $message = m::mock('Http\Message\MessageFactory');
        $request = m::mock('Psr\Http\Message\RequestInterface');
        $response = m::mock('stdClass');
        $options = [
            'store_uid' => '123',
            'key' => md5(rand()),
            'ip' => '::1',
            'sandbox' => true,
        ];
        $api = new Api($options, $httpClient, $message);

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
        $this->assertSame('https://pay.usecase.cc/api/init', $api->getApiEndpoint());
    }

    public function test_create_transaction()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $httpClient = m::mock('Payum\Core\HttpClientInterface');
        $message = m::mock('Http\Message\MessageFactory');
        $request = m::mock('Psr\Http\Message\RequestInterface');
        $encrypter = m::mock('PayumTW\Mypay\Encrypter');
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $options = [
            'store_uid' => '123',
            'key' => md5(rand()),
            'ip' => '::1',
            'sandbox' => true,
        ];

        $params = [
            'user_id' => 'phper',
            'item' => 1,
            'items' => [
                [
                    'id' => '0886449',
                    'name' => '商品名稱',
                    'cost' => 10,
                    'amount' => '1',
                    'total' => 10,
                ],
            ],
            'order_id' => '1234567890',
            'ip' => '::1',
        ];

        $apiParams = json_encode([
            'service_name' => 'api',
            'cmd' => 'api/orders',
        ]);

        $postData = [
            'store_uid' => $options['store_uid'],
            'service' => 'foo.api-params',
            'encry_data' => 'foo.encrypt',
        ];

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $encrypter
            ->shouldReceive('setKey')->with($options['key'])->twice()->andReturnSelf()
            ->shouldReceive('encrypt')->with($apiParams)->once()->andReturn('foo.api-params')
            ->shouldReceive('encrypt')->once()->andReturn('foo.encrypt');

        $message->shouldReceive('createRequest')->andReturn($request);

        $httpClient->shouldReceive('send')->andReturn($response);

        $response
            ->shouldReceive('getStatusCode')->andReturn(200)
            ->shouldReceive('getBody->getContents')->andReturn(json_encode(['foo' => 'bar']));

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $api = new Api($options, $httpClient, $message, $encrypter);
        $result = $api->createTransaction($params);
    }

    public function test_get_transaction_data()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $httpClient = m::mock('Payum\Core\HttpClientInterface');
        $message = m::mock('Http\Message\MessageFactory');
        $request = m::mock('Psr\Http\Message\RequestInterface');
        $encrypter = m::mock('PayumTW\Mypay\Encrypter');
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $options = [
            'store_uid' => '123',
            'key' => md5(rand()),
            'ip' => '::1',
            'sandbox' => true,
        ];

        $params = [
            'key' => md5(rand()),
            'uid' => md5(rand()),
        ];

        $apiParams = json_encode([
            'service_name' => 'api',
            'cmd' => 'api/queryorder',
        ]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $encrypter
            ->shouldReceive('setKey')->with($options['key'])->twice()->andReturnSelf()
            ->shouldReceive('encrypt')->with($apiParams)->once()->andReturn('foo.api-params')
            ->shouldReceive('encrypt')->once()->andReturn('foo.encrypt');

        $message->shouldReceive('createRequest')->andReturn($request);

        $httpClient->shouldReceive('send')->andReturn($response);

        $response
            ->shouldReceive('getStatusCode')->andReturn(200)
            ->shouldReceive('getBody->getContents')->andReturn(json_encode(['foo' => 'bar']));

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $api = new Api($options, $httpClient, $message, $encrypter);
        $result = $api->getTransactionData($params);
    }

    public function test_get_transaction_data_with_response()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $httpClient = m::mock('Payum\Core\HttpClientInterface');
        $message = m::mock('Http\Message\MessageFactory');
        $request = m::mock('Psr\Http\Message\RequestInterface');
        $encrypter = m::mock('PayumTW\Mypay\Encrypter');
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $options = [
            'store_uid' => '123',
            'key' => md5(rand()),
            'ip' => '::1',
            'sandbox' => true,
        ];

        $key = md5(rand());

        $params = [
            'key' => $key,
            'response' => [
                'key' => $key,
                'uid' => md5(rand()),
            ],
        ];

        $apiParams = json_encode([
            'service_name' => 'api',
            'cmd' => 'api/queryorder',
        ]);

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

        $api = new Api($options, $httpClient, $message, $encrypter);
        $result = $api->getTransactionData($params);
    }
}
