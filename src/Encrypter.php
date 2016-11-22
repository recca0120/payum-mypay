<?php

namespace PayumTW\Mypay;

use phpseclib\Crypt\AES;

class Encrypter
{
    protected $cipher;

    protected $key;

    public function __construct(AES $cipher = null)
    {
        $this->cipher = is_null($cipher) === true ? new AES(AES::MODE_CBC) : $cipher;
    }

    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    public function encrypt($plaintext)
    {
        $this->cipher->setKey($this->key);
        $result = $this->cipher->encrypt($plaintext);
        $iv = $this->cipher->encryptIV;

        return base64_encode($iv.$result);
    }

    public function decrypt($ciphertext)
    {
        $encryptWithIV = base64_decode($ciphertext);
        $iv = substr($encryptWithIV, 0, 16);
        $ciphertext = substr($encryptWithIV, 16);
        $this->cipher->setKey($this->key);
        $this->cipher->setIV($iv);

        return $this->cipher->decrypt($ciphertext);
    }

    public function encryptByPHP($plaintext)
    {
        $size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($size, MCRYPT_DEV_URANDOM);
        $padding = 16 - (strlen($plaintext) % 16);
        $plaintext .= str_repeat(chr($padding), $padding);
        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $plaintext, MCRYPT_MODE_CBC, $iv);

        return base64_encode($iv.$ciphertext);
    }

    public function decryptByPHP($ciphertext)
    {
        $encryptWithIV = base64_decode($ciphertext);
        $iv = substr($encryptWithIV, 0, 16);
        $ciphertext = substr($encryptWithIV, 16);

        return $this->pkcs5Unpad(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, $ciphertext, MCRYPT_MODE_CBC, $iv));
    }

    protected function pkcs5Unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }

        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }

        return substr($text, 0, -1 * $pad);
    }
}
