<?php
require_once("./index.php");

//binary file path
$binPath = XASSET_PATH . 'tools/xasset-cli/xasset-cli';
$config = new \suptime\bdxasset\common\config\XassetConfig(
    new \suptime\bdxasset\auth\EcdsaCrypto($binPath)
);

$appId = 0;
$ak = 'xxx';
$sk = 'xxx';
$config->setCredentials($appId, $ak, $sk);

$config->endPoint = "http://120.48.16.137:8360";
$xHandle = new XassetClient($config);

//生成新的account
$ac = new Account($binPath);
$account = $ac->createAccount();

//使用现有account
/*$addr = '';
$pubKey = '';
$privtKey = '';
$account = array(
    'address' => $addr,
    'public_key' => $pubKey,
    'private_key' => $privtKey,
);*/

//文件相关接口
$stoken = $xHandle->getStoken($account);
var_dump($stoken);
