<?php

namespace PayumTW\Mypay;

use phpseclib\Crypt\AES;

class Encrypter
{
    /**
     * $cipher.
     *
     * @var \phpseclib\Crypt\AES
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
     * @param string $key
     * @param \phpseclib\Crypt\AES $cipher
     */
    public function __construct($key, AES $cipher = null)
    {
        $this->key = $key;
        $this->cipher = $cipher ?: new AES(AES::MODE_CBC);
    }

    /**
     * setKey.
     *
     * @param string $key
     * @return static
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * encrypt.
     *
     * @param array $params
     * @return string
     */
    public function encrypt($params)
    {
        $plaintext = json_encode($params);
        $this->cipher->setKey($this->key);
        $result = $this->cipher->encrypt($plaintext);
        $iv = $this->cipher->encryptIV;

        return base64_encode($iv.$result);
    }

    /**
     * encryptByPHP.
     *
     * @param array $params
     * @return string
     */
    public function encryptByPHP($params)
    {
        $plaintext = json_encode($params);
        $size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($size, MCRYPT_DEV_URANDOM);
        $padding = 16 - (strlen($plaintext) % 16);
        $plaintext .= str_repeat(chr($padding), $padding);
        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $plaintext, MCRYPT_MODE_CBC, $iv);

        return base64_encode($iv.$ciphertext);
    }

    /**
     * decrypt.
     *
     * @param string $ciphertext
     * @return array
     */
    public function decrypt($ciphertext)
    {
        $encryptWithIV = base64_decode($ciphertext);
        $iv = substr($encryptWithIV, 0, 16);
        $ciphertext = substr($encryptWithIV, 16);
        $this->cipher->setKey($this->key);
        $this->cipher->setIV($iv);

        return json_decode($this->cipher->decrypt($ciphertext), true);
    }

    /**
     * decryptByPHP.
     *
     * @param string $ciphertext
     * @return array
     */
    public function decryptByPHP($ciphertext)
    {
        $encryptWithIV = base64_decode($ciphertext);
        $iv = substr($encryptWithIV, 0, 16);
        $ciphertext = substr($encryptWithIV, 16);

        return json_decode(
            $this->pkcs5Unpad(
                mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, $ciphertext, MCRYPT_MODE_CBC, $iv)
            ),
            true
        );
    }

    /**
     * encryptRequest.
     *
     * @param string $storeUid
     * @param array $params
     * @param string $cmd
     * @param string $serviceName
     * @return array
     */
    public function encryptRequest($storeUid, $params, $cmd = 'api/orders', $serviceName = 'api')
    {
        return [
            'store_uid' => $storeUid,
            'service' => $this->encrypt([
                'service_name' => $serviceName,
                'cmd' => $cmd,
            ]),
            'encry_data' => $this->encrypt($params),
        ];
    }

    /**
     * pkcs5Unpad.
     *
     * @param string $plaintext
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
