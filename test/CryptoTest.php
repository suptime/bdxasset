<?php

$config = [
    'system' => 'windows', //linux, mac, windows,
    'api_domain' => '', //API接口地址
    'app_id' => '', //APPID
    'ak' => '', //AK
    'sk' => '', //SK
];
$xasset = new \suptime\bdxasset\Xasset($config);
$xHandle = $xasset->XassetClient();

var_dump($xHandle->getStoken());

$account = $xasset->createAccount();
var_dump($account);

$sign = $xasset->signEcdsa($account['private_key'], '123');
var_dump($sign);

$pemPrivtKey = '-----BEGIN EC PRIVATE KEY-----
MHcCAQEEIPaBGYpfOFyaXL2nQy1CXIsDpU468bdx4TLGjD6DqjkeoAoGCCqGSM49
AwEHoUQDQgAEULUuy3k689shj4XQnfztPmkHeUA1Fl/PP0D6MJvwJYywHDHTjpJp
XWu+D7UFPltAnXoHFHqCtgxDZ55aQvNv7A==
-----END EC PRIVATE KEY-----';
$pubKey = '{"Curvname":"P-256","X":36505150171354363400464126431978257855318414556425194490762274938603757905292,"Y":79656876957602994269528255245092635964473154458596947290316223079846501380076}';
$pemSign = $xasset->signEcdsaPem($pemPrivtKey, '123');
var_dump($pemSign);
