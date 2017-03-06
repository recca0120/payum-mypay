<?php

namespace PayumTW\Mypay\Tests;

use Mockery as m;
use PayumTW\Mypay\Encrypter;
use PHPUnit\Framework\TestCase;

class EncrypterTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testEncryptAndDecrypt()
    {
        $encrypter = new Encrypter($key = md5(rand()));
        $encrypter->setKey($key);
        $encrypt = $encrypter->encrypt($params = [
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
        ]);
        $this->assertSame($params, $encrypter->decrypt($encrypt));

        if (version_compare(PHP_VERSION, '7.1', '<') === true) {
            $encrypt = $encrypter->encrypt($params);
            $this->assertSame($params, $encrypter->decryptByPHP($encrypt));

            $encrypt = $encrypter->encryptByPHP($params);
            $this->assertSame($params, $encrypter->decrypt($encrypt));

            $encrypt = $encrypter->encryptByPHP($params);
            $this->assertSame($params, $encrypter->decryptByPHP($encrypt));
        }
    }

    public function testEncrypterRequest()
    {
        $encrypter = new Encrypter($key = md5(rand()));
        $encrypter->setKey($key);
        $encrypt = $encrypter->encrypt($params = [
            'store_uid' => $storeUid = 'foo',
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
        ]);

        $this->assertSame([
            'store_uid' => $storeUid,
            'service' => $encrypter->encrypt([
                'service_name' => 'bar',
                'cmd' => 'foo',
            ]),
            'encry_data' => $encrypter->encrypt($params),
        ], $encrypter->encryptRequest($storeUid, $params, 'foo', 'bar'));
    }
}
