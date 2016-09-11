<?php

use Payum\Core\HttpClientInterface;
use PayumTW\Mypay\Api;
use Http\Message\MessageFactory;
use Mockery as m;
use Psr\Http\Message\RequestInterface;

class ApiTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_apiendpoint()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $httpClient = m::mock(HttpClientInterface::class);
        $message = m::mock(MessageFactory::class);
        $request = m::mock(RequestInterface::class);
        $response = m::mock(stdClass::class);
        $options = [
            'store_uid' => '123',
            'key' => md5(rand()),
            'ip' => '::1',
        ];

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $httpClient->shouldReceive('send')->andReturn($response);

        $response
            ->shouldReceive('getStatusCode')->andReturn(200)
            ->shouldReceive('getBody->getContents')->andReturn(json_encode(['foo' => 'bar']));

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $params = [
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
            'user_id' => 'phper',
            'order_id' => '1234567890',
            'ip' => '::1',
        ];

        $message->shouldReceive('createRequest')->andReturnUsing(function ($method, $uri, $headers, $query) use ($request, $options, $params) {
            $post = [];
            parse_str($query, $post);
            $result = $this->decode($post['encry_data'], $options['key']);
            $this->assertSame($options['store_uid'], $result['store_uid']);
            $this->assertSame($options['ip'], $result['ip']);
            $this->assertSame($params['item'], $result['item']);
            $this->assertSame($params['item'], $result['item']);
            $this->assertSame($params['user_id'], $result['user_id']);
            $this->assertSame($params['order_id'], $result['order_id']);

            foreach ($params['items'] as $key => $item) {
                foreach ($item as $name => $value) {
                    $this->assertSame($value, $result['i_'.$key.'_'.$name]);
                }
            }

            return $request;
        });

        $api = new Api($options, $httpClient, $message);
        $result = $api->call($params);
    }

    protected function decode($data, $key)
    {
        $result = base64_decode($data);
        $iv = substr($result, 0, 16);
        $result = substr($result, 16);
        $result = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $result, MCRYPT_MODE_CBC, $iv);

        return json_decode(substr($result, 0, strrpos($result, '}') + 1), true);
    }
}
