<?php


namespace suptime\bdxasset;


use suptime\bdxasset\Auth\EcdsaCrypto;
use suptime\bdxasset\Client\XassetClient;
use suptime\bdxasset\Exceptions\XassetException;
use suptime\bdxasset\Utils\XassetConfig;

class Xasset
{
    private $binPath;
    private $config;

    /**
     * Xasset constructor.
     * @throws XassetException
     */
    public function __construct()
    {
        $this->getXassetConfig();

        $this->binPath = $this->getBinPath();

        $xassetConfig = new XassetConfig(
            new EcdsaCrypto($this->binPath)
        );

        //设置凭据
        $xassetConfig->setCredentials($this->config['app_id'], $this->config['ak'], $this->config['sk']);
        if ($this->config['api_domain']) {
            $xassetConfig->endPoint = $this->config['api_domain'];
        }

        return new XassetClient($xassetConfig);
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
     * 读取配置
     * @throws XassetException
     */
    private function getXassetConfig()
    {
        $this->config = config('xasset', null);
        if (!$this->config) {
            throw new XassetException('The xasset required configuration does not exist');
        }
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

        return __DIR__ . './Bin/xasset-cli-' . $platform;
    }
}
