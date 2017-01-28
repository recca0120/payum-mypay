<?php

namespace PayumTW\Mypay;

use phpseclib\Crypt\AES;

class Encrypter
{
    /**
     * $cipher.
     *
     * @var phpseclib\Crypt\AES
     */
    protected $cipher;

    /**
     * $key.
     *
     * @var string
     */
    protected $key;

    /**
     * __construct.
     *
     * @method __construct
     *
     * @param string               $key
     * @param phpseclib\Crypt\AES  $cipher
     */
    public function __construct($key, AES $cipher = null)
    {
        $this->key = $key;
        $this->cipher = $cipher ?: new AES(AES::MODE_CBC);
    }

    /**
     * setKey.
     *
     * @method setKey
     *
     * @param string    $key
     *
     * @return self
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * encrypt.
     *
     * @method encrypt
     *
     * @param string    $plaintext
     *
     * @return string
     */
    public function encrypt($plaintext)
    {
        $this->cipher->setKey($this->key);
        $result = $this->cipher->encrypt($plaintext);
        $iv = $this->cipher->encryptIV;

        return base64_encode($iv.$result);
    }

    /**
     * decrypt.
     *
     * @method decrypt
     *
     * @param string    $ciphertext
     *
     * @return string
     */
    public function decrypt($ciphertext)
    {
        $encryptWithIV = base64_decode($ciphertext);
        $iv = substr($encryptWithIV, 0, 16);
        $ciphertext = substr($encryptWithIV, 16);
        $this->cipher->setKey($this->key);
        $this->cipher->setIV($iv);

        return $this->cipher->decrypt($ciphertext);
    }

    /**
     * encryptByPHP.
     *
     * @method encryptByPHP
     *
     * @param string    $plaintext
     *
     * @return string
     */
    public function encryptByPHP($plaintext)
    {
        $size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($size, MCRYPT_DEV_URANDOM);
        $padding = 16 - (strlen($plaintext) % 16);
        $plaintext .= str_repeat(chr($padding), $padding);
        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $plaintext, MCRYPT_MODE_CBC, $iv);

        return base64_encode($iv.$ciphertext);
    }

    /**
     * decryptByPHP.
     *
     * @method decryptByPHP
     *
     * @param string    $ciphertext
     *
     * @return string
     */
    public function decryptByPHP($ciphertext)
    {
        $encryptWithIV = base64_decode($ciphertext);
        $iv = substr($encryptWithIV, 0, 16);
        $ciphertext = substr($encryptWithIV, 16);

        return $this->pkcs5Unpad(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, $ciphertext, MCRYPT_MODE_CBC, $iv));
    }

    /**
     * pkcs5Unpad.
     *
     * @method pkcs5Unpad
     *
     * @param string   $text
     *
     * @return string
     */
    protected function pkcs5Unpad($plaintext)
    {
        $pad = ord($plaintext[strlen($plaintext) - 1]);
        if ($pad > strlen($plaintext)) {
            return false;
        }

        if (strspn($plaintext, chr($pad), strlen($plaintext) - $pad) != $pad) {
            return false;
        }

        return substr($plaintext, 0, -1 * $pad);
    }
}
