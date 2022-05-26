<?php

namespace suptime\bdxasset\Utils;

class HttpUtils
{
    /**
     * @var array
     */
    public $percent_encoded_strings;

    /**
     * HttpUtils constructor.
     */
    public function __construct()
    {
        $this->percent_encoded_strings = [];
        for ($i = 0; $i < 256; ++$i) {
            $this->percent_encoded_strings[$i] = sprintf("%%%02X", $i);
        }
        foreach (range('a', 'z') as $ch) {
            $this->percent_encoded_strings[ord($ch)] = $ch;
        }

        foreach (range('A', 'Z') as $ch) {
            $this->percent_encoded_strings[ord($ch)] = $ch;
        }

        foreach (range('0', '9') as $ch) {
            $this->percent_encoded_strings[ord($ch)] = $ch;
        }
        $this->percent_encoded_strings[ord('-')] = '-';
        $this->percent_encoded_strings[ord('.')] = '.';
        $this->percent_encoded_strings[ord('_')] = '_';
        $this->percent_encoded_strings[ord('~')] = '~';
    }

    /**
     * Normalize a string for use in url path. The algorithm is:
     * <p>
     *
     * <ol>
     *   <li>Normalize the string</li>
     *   <li>replace all "%2F" with "/"</li>
     *   <li>replace all "//" with "/%2F"</li>
     * </ol>
     *
     * <p>
     * Bos object key can contain arbitrary characters, which may result double
     * slash in the url path. Apache Http client will replace "//" in the path
     * with a single '/', which makes the object key incorrect. Thus we replace
     * "//" with "/%2F" here.
     *
     * @param $path string the path string to normalize.
     * @return string the normalized path string.
     * @see #normalize(string)
     */
    public function urlEncodeExceptSlash($path)
    {
        return str_replace("%2F", "/", $this->urlEncode($path));
    }

    /**
     * Normalize a string for use in BCE web service APIs. The normalization
     * algorithm is:
     * 1,Convert the string into a UTF-8 byte array.
     * 2,Encode all octets into percent-encoding, except all URI unreserved
     * characters per the RFC 3986.
     *
     * All letters used in the percent-encoding are in uppercase.
     *
     * @param $value string the string to normalize.
     * @return string the normalized string.
     */
    public function urlEncode($value)
    {
        $result = '';
        for ($i = 0; $i < strlen($value); ++$i) {
            $result .= $this->percent_encoded_strings[ord($value[$i])];
        }
        return $result;
    }

    /**
     * 获取规范查询字符串
     * @param $parameters array
     * @param $forSignature bool
     * @return string
     */
    public function getCanonicalQueryString(array $parameters, $forSignature)
    {
        if (count($parameters) == 0) {
            return '';
        }

        $parameterStrings = [];
        foreach ($parameters as $k => $v) {
            if ($forSignature && strcasecmp(HttpHeaders::AUTHORIZATION, $k) == 0) {
                continue;
            }
            if (!isset($k)) {
                throw new \InvalidArgumentException("parameter key should not be null");
            }
            if (isset($v)) {
                $parameterStrings[] = $this->urlEncode($k) . '=' . $this->urlEncode((string)$v);
            } else {
                if ($forSignature) {
                    $parameterStrings[] = $this->urlEncode($k) . '=';
                } else {
                    $parameterStrings[] = $this->urlEncode($k);
                }
            }
        }
        sort($parameterStrings);
        return implode('&', $parameterStrings);
    }
}
