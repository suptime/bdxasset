<?php

namespace nabao\bdxasset\Auth;

class Account
{
    /**
     * @var
     */
    private $binPath;

    /**
     * Account constructor.
     * @param $binPath
     */
    public function __construct($binPath)
    {
        $this->binPath = $binPath;
    }

    /**
     * 生成区块链账户
     * @return array|bool|mixed
     */
    public function createAccount()
    {
        $s = exec($this->binPath . ' account create -l 1 -s 1 -f std');
        $arrAccount = json_decode(trim($s), true);
        if (empty($arrAccount) || !is_array($arrAccount)) {
            return false;
        }
        return $arrAccount;
    }
}
