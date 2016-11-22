<?php

namespace PayumTW\Mypay;

use phpseclib\Crypt\AES;

class Encrypter
{
    protected $cipher;

    protected $key;

    public function __construct(AES $cipher = null) {
        $this->cipher = is_null($cipher) === true ? new AES(AES::MODE_CBC) : $cipher;
    }

    public function setKey($key) {
        $this->key = $key;

        return $this;
    }

    public function encrypt($plaintext) {

    }
}
