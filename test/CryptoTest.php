<?php

$path = '../tools/xasset-cli/xasset-cli_mac';
$aHandle = new \nabao\bdxasset\auth\Account($path);
$account = $aHandle->createAccount();
var_dump($account);

$signer = new \nabao\bdxasset\auth\EcdsaCrypto($path);
$sign = $signer->signEcdsa($account['private_key'], '123');
var_dump($sign);

$pemPrivtKey = '-----BEGIN EC PRIVATE KEY-----
MHcCAQEEIPaBGYpfOFyaXL2nQy1CXIsDpU468bdx4TLGjD6DqjkeoAoGCCqGSM49
AwEHoUQDQgAEULUuy3k689shj4XQnfztPmkHeUA1Fl/PP0D6MJvwJYywHDHTjpJp
XWu+D7UFPltAnXoHFHqCtgxDZ55aQvNv7A==
-----END EC PRIVATE KEY-----';
$pubKey = '{"Curvname":"P-256","X":36505150171354363400464126431978257855318414556425194490762274938603757905292,"Y":79656876957602994269528255245092635964473154458596947290316223079846501380076}';
$pemSign = $signer->signEcdsaPem($pemPrivtKey, '123');
var_dump($pemSign);
