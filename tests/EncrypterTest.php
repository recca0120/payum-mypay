<?php

use Mockery as m;
use PayumTW\Mypay\Encrypter;

class EncrypterTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_encrypt()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $key = md5(rand());
        $encrypter = new Encrypter($key);

        /*
        |------------------------------------------------------------
        | Expectation
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

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $encrypt = $encrypter->encrypt(json_encode($params));
        $this->assertSame($params, json_decode($encrypter->decrypt($encrypt), true));

        if (version_compare(PHP_VERSION, '7.1', '<') === true) {
            $this->assertSame($params, json_decode($encrypter->decryptByPHP($encrypt), true));

            $encrypt = $encrypter->encryptByPHP(json_encode($params));
            $this->assertSame($params, json_decode($encrypter->decrypt($encrypt), true));
        }
    }
}
