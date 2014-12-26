<?php
namespace Application\Service;

use Zend\Crypt\Hash;
use Zend\Filter\Decrypt;
use Zend\Filter\Encrypt;
use Zend\Mime\Mime;

class EncryptDecryptService
{

    protected $privateKey = './private/keys/myprivkey.pem';
    protected $publicKey = './private/keys/mypubkey.pem';

    public function encrypt($text)
    {
        if (strlen($text)==0) return '';

        $filter = new Encrypt(array(
            'adapter' => 'openssl',
            'private' => $this->privateKey,
            'public'  => $this->publicKey,
            'package' => true
        ));

        $encrypted = $filter->filter($text);
        return Mime::encodeBase64($encrypted);
    }

    public function decrypt($cryptedText)
    {
        if (strlen($cryptedText)==0) return '';

        $filter = new Decrypt(array(
            'adapter' => 'openssl',
            'private' => $this->privateKey,
            'public'  => $this->publicKey,
            'package' => true
        ));

        //$filter->setEnvelopeKey($this->publicKey);
        $decrypted = $filter->filter(base64_decode($cryptedText));
        return $decrypted;
    }

    /**
     * Secure hash
     *
     * @param string $value
     * @return string
     */
    public function hashIt($value)
    {
        $static_salt = 't0rum33s';
        $key = Hash::compute("sha256", $value.$static_salt);
        //$key = SaltedS2k::calc("sha256", $value, $salt, 64);
        return Hash::compute("sha256", $key);
    }


}