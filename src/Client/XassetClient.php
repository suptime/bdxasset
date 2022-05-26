<?php

namespace suptime\bdxasset\Client;

use suptime\bdxasset\Exceptions\XassetException;

class XassetClient extends BaseClient
{
    /**
     * @var \suptime\bdxasset\Auth\EcdsaCrypto
     */
    private $crypto;

    const XassetErrnoSucc = 0;

    const XassetApiHoraeCreate = '/xasset/horae/v1/create';
    const XassetApiHoraeAlter = '/xasset/horae/v1/alter';
    const XassetApiHoraePublish = '/xasset/horae/v1/publish';
    const XassetApiHoraeQuery = '/xasset/horae/v1/query';
    const XassetApiHoraeListByStatus = '/xasset/horae/v1/listbystatus';
    const XassetApiHoraeGrant = '/xasset/horae/v1/grant';
    const XassetApiHoraeTransfer = '/xasset/damocles/v1/transfer';
    const XassetApiHoraeListAstByAddr = '/xasset/horae/v1/listastbyaddr';
    const XassetApiHoraeQueryShard = '/xasset/horae/v1/querysds';
    const XassetApiHoraeListSdsByAddr = '/xasset/horae/v1/listsdsbyaddr';
    const XassetApiHoraeListSdsByAst = '/xasset/horae/v1/listsdsbyast';
    const XassetApiHoraeAstHistory = '/xasset/horae/v1/history';
    const XassetApiHoraeGetEvidenceInfo = '/xasset/horae/v1/getevidenceinfo';

    const XassetApiGetStoken = '/xasset/file/v1/getstoken';

    /**
     * XassetClient constructor.
     * @param array $xassetConfig
     */
    public function __construct($xassetConfig)
    {
        parent::__construct($xassetConfig);
        $this->crypto = $xassetConfig->crypto;
    }

    /**
     * 获取授权token
     * @param $account
     * @return array|bool
     * @throws XassetException
     */
    public function getStoken($account)
    {
        if (!self::isValidAccount($account)) {
            throw new XassetException('param error', self::ClientErrnoRespErr);
        }
        $nonce = gen_nonce();
        $signMsg = sprintf("%d", $nonce);
        $sign = $this->crypto->signEcdsa($account['private_key'], $signMsg);

        $body = array(
            'addr' => $account['address'],
            'pkey' => $account['public_key'],
            'sign' => $sign,
            'nonce' => $nonce,
        );

        return $this->doRequestRetry(self::XassetApiGetStoken, [], $body);
    }

    /**
     * 创建资产
     * @param $account
     * @param $assetId
     * @param $amount
     * @param $assetInfo
     * @param int $price
     * @param int $userId
     * @return array|bool
     * @throws XassetException
     */
    public function createAsset($account, $assetId, $amount, $assetInfo, $price = -1, $userId = 0)
    {
        if ($assetId < 1 || $amount < 1 || $assetInfo == '') {
            throw new XassetException('param error', self::ClientErrnoParamErr);
        }
        if (!self::isValidAccount($account)) {
            throw new XassetException('param error', self::ClientErrnoParamErr);
        }

        $nonce = gen_nonce();
        $signMsg = sprintf("%d%d", $assetId, $nonce);
        $sign = $this->crypto->signEcdsa($account['private_key'], $signMsg);

        $body = array(
            'asset_id' => $assetId,
            'amount' => $amount,
            'asset_info' => $assetInfo,
            'addr' => $account['address'],
            'sign' => $sign,
            'pkey' => $account['private_key'],
            'nonce' => $nonce,
        );
        if ($price > -1) {
            $body['price'] = $price;
        }
        if ($userId > 0) {
            $body['user_id'] = $userId;
        }

        return $this->doRequestRetry(self::XassetApiHoraeCreate, [], $body);
    }

    /**
     * 更改资产
     * @param $account
     * @param $assetId
     * @param $amount
     * @param $assetInfo
     * @param int $price
     * @return array|bool
     * @throws XassetException
     */
    public function alterAsset($account, $assetId, $amount, $assetInfo, $price = -1)
    {
        if ($assetId < 1 || $assetInfo == '') {
            throw new XassetException('param error', self::ClientErrnoParamErr);
        }
        if (!self::isValidAccount($account)) {
            throw new XassetException('param error', self::ClientErrnoParamErr);
        }
        $nonce = gen_nonce();
        $signMsg = sprintf("%d%d", $assetId, $nonce);
        $sign = $this->crypto->signEcdsa($account['private_key'], $signMsg);

        $body = array(
            'asset_id' => $assetId,
            'addr' => $account['address'],
            'sign' => $sign,
            'pkey' => $account['public_key'],
            'nonce' => $nonce,
        );
        if ($amount > -1) {
            $body['amount'] = $amount;
        }
        if ($assetInfo != '') {
            $body['asset_info'] = $assetInfo;
        }
        if ($price > -1) {
            $body['price'] = $price;
        }

        return $this->doRequestRetry(self::XassetApiHoraeAlter, [], $body);
    }

    /**
     * 发布资产
     * @param $account
     * @param $assetId
     * @return array|bool
     * @throws XassetException
     */
    public function publishAsset($account, $assetId)
    {
        if ($assetId < 1) {
            throw new XassetException('param error', self::ClientErrnoParamErr);
        }
        if (!self::isValidAccount($account)) {
            throw new XassetException('param error', self::ClientErrnoParamErr);
        }

        $nonce = gen_nonce();
        $signMsg = sprintf("%d%d", $assetId, $nonce);
        $sign = $this->crypto->signEcdsa($account['private_key'], $signMsg);

        $body = array(
            'asset_id' => $assetId,
            'addr' => $account['address'],
            'sign' => $sign,
            'pkey' => $account['public_key'],
            'nonce' => $nonce,
        );

        return $this->doRequestRetry(self::XassetApiHoraePublish, [], $body);
    }

    /**
     * 查询资产
     * @param $assetId
     * @return array|bool
     * @throws XassetException
     */
    public function queryAsset($assetId)
    {
        if ($assetId < 1) {
            throw new XassetException('param error', self::ClientErrnoParamErr);
        }

        $body = ['asset_id' => $assetId];
        return $this->doRequestRetry(self::XassetApiHoraeQuery, [], $body);
    }

    /**
     * 按状态列出的时间列表
     * @param $account
     * @param $status
     * @param $page
     * @param $limit
     * @return array|bool
     * @throws XassetException
     */
    public function horaeListbystatus($account, $status, $page, $limit)
    {
        if (!self::isValidAccount($account)) {
            throw new XassetException('param error', self::ClientErrnoParamErr);
        }

        $nonce = gen_nonce();
        $signMsg = sprintf("%d", $nonce);
        $sign = $this->crypto->signEcdsa($account['private_key'], $signMsg);

        $body = array(
            'status' => $status,
            'nonce' => $nonce,
            'addr' => $account['address'],
            'sign' => $sign,
            'pkey' => $account['public_key'],
            'page' => $page,
        );
        if ($limit > 0) {
            $body['limit'] = $limit;
        }

        return $this->doRequestRetry(self::XassetApiHoraeListByStatus, [], $body);
    }

    /**
     * 碎片补助金
     * @param $account
     * @param $assetId
     * @param $shardId
     * @param $toAddr
     * @param int $price
     * @param int $toUserId
     * @return array|bool
     * @throws XassetException
     */
    public function grantShard($account, $assetId, $shardId, $toAddr, $price = -1, $toUserId = 0)
    {
        if ($assetId < 1 || $shardId < 1 || $toAddr == "") {
            throw new XassetException('param error', self::ClientErrnoParamErr);
        }
        if (!self::isValidAccount($account)) {
            throw new XassetException('param error', self::ClientErrnoParamErr);
        }

        $nonce = gen_nonce();
        $signMsg = sprintf("%d%d", $assetId, $nonce);
        $sign = $this->crypto->signEcdsa($account['private_key'], $signMsg);

        $body = array(
            'asset_id' => $assetId,
            'shard_id' => $shardId,
            'addr' => $account['address'],
            'sign' => $sign,
            'pkey' => $account['public_key'],
            'nonce' => $nonce,
            'to_addr' => $toAddr,
            'to_userid' => $toUserId,
        );
        if ($price > -1) {
            $body['price'] = $price;
        }
        if ($toUserId > 0) {
            $body['to_userid'] = $toUserId;
        }

        return $this->doRequestRetry(self::XassetApiHoraeGrant, [], $body);
    }

    /**
     * 转移碎片
     * @param $account
     * @param $assetId
     * @param $shardId
     * @param $toAddr
     * @param int $price
     * @param int $toUserId
     * @return array|bool
     * @throws XassetException
     */
    public function transferShard($account, $assetId, $shardId, $toAddr, $price = -1, $toUserId = 0)
    {
        if ($assetId < 1 || $shardId < 1 || $toAddr == "") {
            throw new XassetException('param error', self::ClientErrnoParamErr);
        }
        if (!self::isValidAccount($account)) {
            throw new XassetException('param error', self::ClientErrnoParamErr);
        }
        $nonce = gen_nonce();
        $signMsg = sprintf("%d%d", $assetId, $nonce);
        $sign = $this->crypto->signEcdsa($account['private_key'], $signMsg);

        $body = array(
            'asset_id' => $assetId,
            'shard_id' => $shardId,
            'addr' => $account['address'],
            'sign' => $sign,
            'pkey' => $account['public_key'],
            'nonce' => $nonce,
            'to_addr' => $toAddr,
        );
        if ($price > -1) {
            $body['price'] = $price;
        }
        if ($toUserId > 0) {
            $body['to_userid'] = $toUserId;
        }

        return $this->doRequestRetry(self::XassetApiHoraeTransfer, [], $body);
    }

    /**
     * 按地址列出资产
     * @param $addr
     * @param $status
     * @param $page
     * @param $limit
     * @return array|bool
     * @throws XassetException
     */
    public function listAssetsByAddr($addr, $status, $page, $limit)
    {
        if ($addr == "" || $page < 1 || $limit < 1) {
            throw new XassetException('param error', self::ClientErrnoParamErr);
        }

        $body = array(
            'addr' => $addr,
            'status' => $status,
            'page' => $page,
            'limit' => $limit,
        );

        return $this->doRequestRetry(self::XassetApiHoraeListAstByAddr, [], $body);
    }

    /**
     * 查询碎片
     * @param $assetId
     * @param $shardId
     * @return array|bool
     * @throws XassetException
     */
    public function queryShard($assetId, $shardId)
    {
        if ($assetId < 1 || $shardId < 1) {
            throw new XassetException('param error', self::ClientErrnoParamErr);
        }

        $body = array(
            'asset_id' => $assetId,
            'shard_id' => $shardId,
        );

        return $this->doRequestRetry(self::XassetApiHoraeQueryShard, [], $body);
    }

    /**
     * 按地址列出碎片
     * @param $addr
     * @param $page
     * @param $limit
     * @return array|bool
     * @throws XassetException
     */
    public function listShardsByAddr($addr, $page, $limit)
    {
        if ($addr == "" || $page < 1 || $limit < 1) {
            throw new XassetException('param error', self::ClientErrnoParamErr);
        }

        $body = array(
            'addr' => $addr,
            'page' => $page,
            'limit' => $limit,
        );

        return $this->doRequestRetry(self::XassetApiHoraeListSdsByAddr, [], $body);
    }

    /**
     * 按资产列出碎片
     * @param $assetId
     * @param $cursor
     * @param $limit
     * @return array|bool
     * @throws XassetException
     */
    public function listShardsByAsset($assetId, $cursor, $limit)
    {
        if ($assetId < 1 || $limit < 1) {
            throw new XassetException('param error', self::ClientErrnoParamErr);
        }

        $body = array(
            'asset_id' => $assetId,
            'cursor' => $cursor,
            'limit' => $limit,
        );

        return $this->doRequestRetry(self::XassetApiHoraeListSdsByAst, [], $body);
    }

    /**
     * 列出资产历史记录
     * @param $assetId
     * @param $page
     * @param $limit
     * @return array|bool
     * @throws XassetException
     */
    public function listAssetHistory($assetId, $page, $limit)
    {
        if ($assetId < 1 || $page < 1 || $limit < 1) {
            throw new XassetException('param error', self::ClientErrnoParamErr);
        }

        $body = array(
            'asset_id' => $assetId,
            'page' => $page,
            'limit' => $limit,
        );

        return $this->doRequestRetry(self::XassetApiHoraeAstHistory, [], $body);
    }

    /**
     * 获取证据信息
     * @param $assetId
     * @return array|bool
     * @throws XassetException
     */
    public function getEvidenceInfo($assetId)
    {
        if ($assetId < 1) {
            throw new XassetException('param error', self::ClientErrnoParamErr);
        }

        $body = array(
            'asset_id' => $assetId,
        );

        return $this->doRequestRetry(self::XassetApiHoraeGetEvidenceInfo, [], $body);
    }
}
