<?php

$config = [
    'system' => 'windows', //linux, mac, windows,
    'api_domain' => '', //API接口地址
    'app_id' => '', //APPID
    'ak' => '', //AK
    'sk' => '', //SK
];
$xasset = new \suptime\bdxasset\Xasset($config);
//生成新的account
$account = $xasset->createAccount();
print_r($account);

echo '<br/>';

//xasset客户端实例
$client = $xasset->XassetClient();

//使用现有account
/*$addr = '';
$pubKey = '';
$privtKey = '';
$account = array(
    'address' => $addr,
    'public_key' => $pubKey,
    'private_key' => $privtKey,
);*/
//获取stoken
$stoken = $client->getStoken($account);
print_r($stoken);
