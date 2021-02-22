<?php
/**
 * @copyright Copyright (c) 2018 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larvacent.com/
 * @license http://www.larvacent.com/license/
 */

namespace Larva\Support;

use HTMLPurifier_Config;

/**
 * HTML 助手
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class HtmlHelper
{
    /**
     * 净化HTML
     * @param string $html
     * @return string
     */
    public static function purify(string $html): string
    {
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new \HTMLPurifier($config);
        return $purifier->purify($html);
    }

    /**
     * 清理UTF-8字符串以确保格式正确和SGML有效性
     * @param string $string
     * @return string
     */
    public static function cleanUtf8(string $string): string
    {
        return \HTMLPurifier_Encoder::cleanUTF8($string);
    }

    /**
     * Encodes special characters into HTML entities.
     *
     * @param string $content the content to be encoded
     * @param bool $doubleEncode whether to encode HTML entities in `$content`. If false,
     * HTML entities in `$content` will not be further encoded.
     * @return string the encoded content
     * @see decode()
     * @see http://www.php.net/manual/en/function.htmlspecialchars.php
     */
    public static function encode(string $content, $doubleEncode = true): string
    {
        return htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode);
    }

    /**
     * Decodes special HTML entities back to the corresponding characters.
     * This is the opposite of [[encode()]].
     * @param string $content the content to be decoded
     * @return string the decoded content
     * @see encode()
     * @see http://www.php.net/manual/en/function.htmlspecialchars-decode.php
     */
    public static function decode(string $content): string
    {
        return htmlspecialchars_decode($content, ENT_QUOTES);
    }

    /**
     * Will take an HTML string and an associative array of key=>value pairs, HTML encode the values and swap them back
     * into the original string using the keys as tokens.
     *
     * @param string $html The HTML string.
     * @param array $variables An associative array of key => value pairs to be applied to the HTML string using `strtr`.
     * @return string The HTML string with the encoded variable values swapped in.
     */
    public static function encodeParams(string $html, array $variables = []): string
    {
        // Normalize the param keys
        $normalizedVariables = [];
        if (is_array($variables)) {
            foreach ($variables as $key => $value) {
                $key = '{' . trim($key, '{}') . '}';
                $normalizedVariables[$key] = static::encode($value);
            }
            $html = strtr($html, $normalizedVariables);
        }
        return $html;
    }

    /**
     * 检测 Html 编码
     * @param string $content
     * @return string
     */
    public static function getCharSet(string $content): string
    {
        if (preg_match("/<meta.+?charset=[^\\w]?([-\\w]+)/i", $content, $match)) {
            return strtoupper($match [1]);
        } else { // 检测中文常用编码
            return strtoupper(mb_detect_encoding($content, ['ASCII', 'CP936', 'GB2312', 'GBK', 'GB18030', 'UTF-8', 'BIG-5']));
        }
    }

    /**
     * 提取所有的 Head 标签返回一个数组
     * @param string $content
     * @return array
     */
    public static function getHeadTags(string $content): array
    {
        $result = [];
        if (is_string($content) && !empty ($content)) {
            if (($chatSet = static::getCharSet($content)) != 'UTF-8') { // 转码
                $content = mb_convert_encoding($content, 'UTF-8', $chatSet);
            }
            // 解析title
            if (preg_match('#<title[^>]*>(.*?)</title>#si', $content, $match)) {
                $result ['title'] = trim(strip_tags($match [1]));
            }

            // 解析meta
            if (preg_match_all('/<[\s]*meta[\s]*name="?' . '([^>"]*)"?[\s]*' . 'content="?([^>"]*)"?[\s]*[\/]?[\s]*>/si', $content, $match)) {
                // name转小写
                $names = array_map('strtolower', $match [1]);
                $values = $match [2];
                $nameTotal = count($names);
                for ($i = 0; $i < $nameTotal; $i++) {
                    $result ['metaTags'] [$names [$i]] = $values [$i];
                }
            }
            if (isset ($result ['metaTags'] ['keywords'])) {//将关键词切成数组
                $keywords = str_replace(['，', '|', '、', ' '], ',', $result ['metaTags'] ['keywords']);
                $result ['keywords'] = explode(',', $keywords);
            }
        }
        return $result;
    }

    /**
     * 从内容获取外链
     * @param string $content
     * @param string $hostname
     * @return array
     */
    public static function getHtmlOutLink(string $content, string $hostname): array
    {
        if (preg_match_all('/<a(.*?)href="(.*?)"(.*?)>(.*?)<\/a>/i', $content, $document)) {
            $links = [];
            $outLinks = [];
            $inLink = 0;
            foreach ($document [2] as $key => $link) {
                $matches = parse_url($link);
                if (!isset ($matches ['host']) || $matches ['host'] == $hostname) { // 内联
                    $inLink++;
                    continue;
                }
                if (!in_array($matches ['host'], $outLinks) && (stripos($link, 'http:') !== false || stripos($link, 'https:') !== false)) {
                    $outLinks [] = $matches ['host'];
                    $links [] = ['title' => $document [4] [$key], 'nofollow' => !strpos($document [1] [$key], 'nofollow') ? 0 : 1, 'url' => $link, 'host' => $matches ['host']];
                } else {
                    continue;
                }
            }
            return ['count' => count($links) + $inLink, 'inlink' => $inLink, 'outlink' => count($links), 'dataList' => $links];
        }
        return ['count' => 0, 'inlink' => 0, 'outlink' => 0, 'dataList' => []];
    }

    /**
     * 获取简介
     * @param string $content 内容
     * @param int $len 长度
     * @return string
     */
    public static function getSummary(string $content, $len = 200): string
    {
        $description = str_replace(["\r\n", "\n", "\t", '&ldquo;', '&rdquo;', '&nbsp;', ' '], '', strip_tags($content));
        return StringHelper::limit($description, $len, '');
    }

    /**
     * 抽取 Html 所有的图片
     * @param string $content HTML 内容
     * @return false|array
     */
    public static function getImages(string $content): array
    {
        if (preg_match_all('/<img.*[\s]src=[\"|\'](.*)[\"|\'].*>/iU', $content, $matches)) {
            return $matches[1];
        }
        return false;
    }

    /**
     * 获取缩略图
     * @param string $content
     * @return null|string
     */
    public static function getThumb(string $content): ?string
    {
        //自动提取缩略图
        $matches = [];
        if (preg_match_all("/(src)=([\"|']?)([^ \"'>]+\.(gif|jpg|jpeg|bmp|png))\\2/i", $content, $matches)) {
            return $matches[3][0];
        }
        return null;
    }

    /**
     * 删除HTML指定标签
     * @param string $content
     * @param string|array $tags
     * @return string
     */
    public static function stripHtmlTags(string $content, $tags): string
    {
        $patterns = [];
        if (!is_array($tags)) {
            $tags = [$tags];
        }
        foreach ($tags as $tag) {
            $patterns[] = "/(<(?:\/" . $tag . "|" . $tag . ")[^>]*>)/i";
        }
        return preg_replace($patterns, '', $content);
    }

    /**
     * 删除所有IMG标签
     * @param string $content
     * @return string
     */
    public static function stripHtmlImg(string $content): string
    {
        return static::stripHtmlTags($content, ['img']);
    }

    /**
     * 转换到 MIP
     * @param string $content
     * @return string
     */
    public static function convertToMIP(string $content): string
    {
        $content = preg_replace('/<img[^>]*src=[\'"]?([^>\'"\s]*)[\'"]?[^>]*>/ie', "<mip-img data-carousel=\"carousel\"  class=\"mip-element mip-img\"  src=\"$1\"></mip-img>", $content);
        return $content;
    }

    /**
     * 转换到 AMP
     * @param string $content
     * @return string
     */
    public static function convertToAMP(string $content): string
    {
        $content = preg_replace('/<img[^>]*src=[\'"]?([^>\'"\s]*)[\'"]?[^>]*>/ie', "<amp-img data-carousel=\"carousel\"  class=\"amp-element amp-img\"  src=\"$1\"></amp-img>", $content);
        return $content;
    }
}
