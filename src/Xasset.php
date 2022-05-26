<?php


namespace suptime\bdxasset;


use suptime\bdxasset\Auth\Account;
use suptime\bdxasset\Auth\EcdsaCrypto;
use suptime\bdxasset\Client\XassetClient;
use suptime\bdxasset\Exceptions\XassetException;
use suptime\bdxasset\Utils\XassetConfig;

class Xasset
{
    private $binPath;
    private $config;
    private $xassetConfig;

    /**
     * Xasset constructor.
     * @throws XassetException
     */
    public function __construct($config = [])
    {
        $this->config = $config ? $config : $this->getXassetConfig();
        $this->binPath = $this->getBinPath();
        $this->xassetConfig = new XassetConfig(new EcdsaCrypto($this->binPath));

        //设置凭据
        $this->xassetConfig->setCredentials($this->config['app_id'], $this->config['ak'], $this->config['sk']);
        if ($this->config['api_domain']) {
            $this->xassetConfig->endPoint = $this->config['api_domain'];
        }
    }

    /**
     * instance
     * @return XassetClient
     */
    public function XassetClient()
    {
        return new XassetClient($this->xassetConfig);
    }

    /**
     * 获取账户
     * @param $addr
     * @param $pubKey
     * @param $privtKey
     * @return array
     */
    public function getAccount($addr, $pubKey, $privtKey)
    {
        return [
            'address' => $addr,
            'public_key' => $pubKey,
            'private_key' => $privtKey,
        ];
    }

    /**
     * 生成一个新账号
     * @return array|bool|mixed
     */
    public function createAccount()
    {
        $account = new Account($this->binPath);
        return $account->createAccount();
    }

    /**
     * 私钥签名
     * @param $privtKey
     * @param $msg
     * @return string
     */
    public function signEcdsa($privtKey, $msg)
    {
        $crypto = new EcdsaCrypto($this->binPath);
        return $crypto->signEcdsa($privtKey, $msg);
    }

    /**
     * 支持pem格式的私钥签名
     * @param $pemPrivtKey
     * @param $oriMsg
     * @return string
     */
    public function signEcdsaPem($pemPrivtKey, $oriMsg)
    {
        $crypto = new EcdsaCrypto($this->binPath);
        return $crypto->signEcdsaPem($pemPrivtKey, $oriMsg);
    }

    /**
     * 读取配置
     * @return mixed
     * @throws XassetException
     */
    private function getXassetConfig()
    {
        if (!function_exists('config')) {
            $config = require_once __DIR__ . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'xasset.php';
        } else {
            $config = config('xasset', null);
        }
        if (!$config) {
            throw new XassetException('The xasset required configuration does not exist');
        }
        return $config;
    }

    /**
     * 获取可执行文件路径
     * @return string
     */
    private function getBinPath()
    {
        switch ($this->config['system']) {
            case 'windows':
                $platform = 'win';
                break;
            case 'mac':
                $platform = 'mac';
                break;
            default:
                $platform = 'linux';
        }
        return __DIR__ . DIRECTORY_SEPARATOR . 'Bin' . DIRECTORY_SEPARATOR . 'xasset-cli-' . $platform;
    }
}
