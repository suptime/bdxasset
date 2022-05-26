<?php

namespace suptime\bdxasset\Auth;

class EcdsaCrypto
{
    private $binPath;

    /**
     * EcdsaCrypto constructor.
     * @param $binPath
     */
    public function __construct($binPath)
    {
        $this->binPath = $binPath;
    }

    /**
     * 私钥签名
     * @param $privtKey
     * @param $msg
     * @return string
     */
    public function signEcdsa($privtKey, $msg)
    {
        $privtKey = str_replace('"', '\\"', $privtKey);
        $sign = exec($this->binPath . ' sign ecsda -k "' . $privtKey . '" -m "' . $msg . '" -f std');
        return trim($sign);
    }

    /**
     * 支持pem格式的私钥签名
     * @param $pemPrivtKey
     * @param $oriMsg
     * @return string
     */
    public function signEcdsaPem($pemPrivtKey, $oriMsg)
    {
        $key = openssl_pkey_get_private($pemPrivtKey);
        openssl_sign($oriMsg, $sign, $key, OPENSSL_ALGO_SHA256);
        openssl_free_key($key);
        return bin2hex($sign);
    }
}
