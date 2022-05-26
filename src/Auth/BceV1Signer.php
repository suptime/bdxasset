<?php

namespace suptime\bdxasset\Auth;

use suptime\bdxasset\Utils\SignOptions;
use suptime\bdxasset\Utils\HttpHeaders;
use suptime\bdxasset\Utils\HttpUtils;
use suptime\bdxasset\Utils\DateUtils;

/**
 * The V1 implementation of Signer with the BCE signing protocol.
 */
class BceV1Signer implements SignerInterface
{

    const BCE_AUTH_VERSION = "bce-auth-v1";

    // Default headers to sign with the BCE signing protocol.
    private $defaultHeadersToSign;

    /**
     * BceV1Signer constructor.
     */
    public function __construct()
    {
        $this->defaultHeadersToSign = [
            strtolower(HttpHeaders::HOST),
            strtolower(HttpHeaders::CONTENT_LENGTH),
            strtolower(HttpHeaders::CONTENT_TYPE),
            strtolower(HttpHeaders::CONTENT_MD5),
        ];
    }

    /**
     * Sign the given request with the given set of credentials. Modifies the passed-in request to apply the signature.
     *
     * @param array $credentials the credentials to sign the request with.
     * @param string $httpMethod
     * @param string $path
     * @param array $headers
     * @param array $params
     * @param array $options the options for signing.
     * @return string The signed authorization string.
     */
    public function sign(array $credentials, $httpMethod, $path, $headers, $params, $options = []): string
    {
        if (!isset($options[SignOptions::EXPIRATION_IN_SECONDS])) {
            $expirationInSeconds = SignOptions::DEFAULT_EXPIRATION_IN_SECONDS;
        } else {
            $expirationInSeconds = $options[SignOptions::EXPIRATION_IN_SECONDS];
        }

        // to compatible with ak/sk or accessKeyId/secretAccessKey
        if (isset($credentials['ak'])) {
            $accessKeyId = $credentials['ak'];
        }
        if (isset($credentials['sk'])) {
            $secretAccessKey = $credentials['sk'];
        }
        if (isset($credentials['accessKeyId'])) {
            $accessKeyId = $credentials['accessKeyId'];
        }
        if (isset($credentials['secretAccessKey'])) {
            $secretAccessKey = $credentials['secretAccessKey'];
        }

        if (isset($options[SignOptions::TIMESTAMP])) {
            $timestamp = $options[SignOptions::TIMESTAMP];
        } else {
            $timestamp = new \DateTime();
        }
        $timestamp->setTimezone(DateUtils::$UTC_TIMEZONE);

        $iso8601Date = DateUtils::formatAlternateIso8601Date($timestamp);
        $authString = self::BCE_AUTH_VERSION . '/' . $accessKeyId . '/' . $iso8601Date . '/' . $expirationInSeconds;
        $signingKey = hash_hmac('sha256', $authString, $secretAccessKey);

        // Formatting the URL with signing protocol.
        $canonicalURI = $this->getCanonicalURIPath($path);
        // Formatting the query string with signing protocol.
        $canonicalQueryString = (new HttpUtils)->getCanonicalQueryString($params, true);

        // Sorted the headers should be signed from the request.
        $headersToSignOption = null;
        if (isset($options[SignOptions::HEADERS_TO_SIGN])) {
            $headersToSignOption = $options[SignOptions::HEADERS_TO_SIGN];
        }
        $headersToSign = $this->getHeadersToSign($headers, $headersToSignOption);

        // Formatting the headers from the request based on signing protocol.
        $canonicalHeader = $this->getCanonicalHeaders($headersToSign);

        $headersToSign = array_keys($headersToSign);
        sort($headersToSign);
        $signedHeaders = '';
        if ($headersToSignOption !== null) {
            $signedHeaders = strtolower(trim(implode(";", $headersToSign)));
        }

        $canonicalRequest = $httpMethod . "\n{$canonicalURI}\n" . "{$canonicalQueryString}\n{$canonicalHeader}";

        // Signing the canonical request using key with sha-256 algorithm.
        $signature = hash_hmac('sha256', $canonicalRequest, $signingKey);

        $authorizationHeader = "{$authString}/{$signedHeaders}/{$signature}";
        return $authorizationHeader;
    }

    /**
     * 获取规范URI路径
     * @param string $path
     * @return string
     */
    private function getCanonicalURIPath(string $path): string
    {
        if (empty($path)) {
            return '/';
        } else {
            $httpUtils = new HttpUtils();
            if ($path[0] == '/') {
                return $httpUtils->urlEncodeExceptSlash($path);
            } else {
                return '/' . $httpUtils->urlEncodeExceptSlash($path);
            }
        }
    }

    /**
     * 获取规范标头
     * @param $headers array
     * @return string
     */
    private static function getCanonicalHeaders($headers)
    {
        if (count($headers) == 0) {
            return '';
        }

        $headerStrings = [];
        foreach ($headers as $k => $v) {
            if ($k === null) {
                continue;
            }
            if ($v === null) {
                $v = '';
            }
            $headerStrings[] = rawurlencode(strtolower(trim($k))) . ':' . rawurlencode(trim($v));
        }
        sort($headerStrings);

        return implode("\n", $headerStrings);
    }

    /**
     * 获取要签名的header头
     * @param array $headers
     * @param array $headersToSign
     * @return array
     */
    private function getHeadersToSign(array $headers, array $headersToSign): array
    {
        $ret = [];
        if ($headersToSign !== null) {
            $tmp = [];
            foreach ($headersToSign as $header) {
                $tmp[] = strtolower(trim($header));
            }
            $headersToSign = $tmp;
        }
        foreach ($headers as $k => $v) {
            if (trim((string)$v) !== '') {
                if ($headersToSign !== null) {
                    if (in_array(strtolower(trim($k)), $headersToSign)) {
                        $ret[$k] = $v;
                    }
                } else {
                    if ($this->isDefaultHeaderToSign($k)
                    ) {
                        $ret[$k] = $v;
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * 检查是否是默认请求头
     * @param string $header
     * @return bool
     */
    private function isDefaultHeaderToSign(string $header)
    {
        $header = strtolower(trim($header));
        if (in_array($header, $this->defaultHeadersToSign)) {
            return true;
        }
        $prefix = substr($header, 0, strlen(HttpHeaders::BCE_PREFIX));
        if ($prefix === HttpHeaders::BCE_PREFIX) {
            return true;
        } else {
            return false;
        }
    }
}
