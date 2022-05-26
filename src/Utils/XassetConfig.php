<?php

namespace suptime\bdxasset\Utils;

use suptime\bdxasset\Auth\BceV1Signer;

class XassetConfig
{
    public $endPoint = "http://120.48.16.137:8360";
    public $userAgent = "xasset-sdk-php";
    public $credentials = [];
    public $signer;
    public $crypto;
    public $connTimeout = 1000;
    public $rwTimeout = 3000;

    /**
     * XassetConfig constructor.
     * @param $crypto
     */
    public function __construct($crypto)
    {
        $this->signer = new BceV1Signer();
        $this->crypto = $crypto;
    }

    /**
     * 设置调用凭据
     * @param string $appId
     * @param string $ak
     * @param string $sk
     */
    public function setCredentials(string $appId, string $ak, string $sk)
    {
        $this->credentials = [
            "app_id" => $appId,
            "ak" => $ak,
            "sk" => $sk
        ];
    }
}
