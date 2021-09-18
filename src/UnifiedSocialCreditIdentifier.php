<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 *
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare(strict_types=1);

namespace Larva\Support;

/**
 * 统一社会信用代码
 * @author Tongle Xu <xutongle@gmail.com>
 */
class UnifiedSocialCreditIdentifier
{
    /**
     * 最大长度。
     */
    public const CHINA_CREDIT_CODE_MAX_LENGTH = 18;

    /**
     * 每位加权因子
     */
    private static array $power = [1, 3, 9, 27, 19, 26, 16, 17, 20, 29, 25, 13, 8, 24, 10, 30, 28];

    private static array $transformation = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13,
        'E' => 14, 'F' => 15, 'G' => 16, 'H' => 17, 'J' => 18, 'K' => 19, 'L' => 20, 'M' => 21, 'N' => 22, 'P' => 23,
        'Q' => 24, 'R' => 25, 'T' => 26, 'U' => 27, 'W' => 28, 'X' => 29, 'Y' => 30];

    /**
     * 获取机构信息
     * @param string $creditCode
     * @return array|false
     */
    public static function getInfo(string $creditCode)
    {
        if (static::validateCard($creditCode)) {
            $info = [
                'manage' => substr($creditCode, 0, 1),
                'type' => substr($creditCode, 1, 1),
                'province_code' => static::getProvinceCodeByCreditCode($creditCode),
                'city_code' => static::getCityCodeByCreditCode($creditCode),
                'district_code' => static::getDistrictCodeByCreditCode($creditCode),
            ];
            $info['province'] = IDCard::$locationCodes [$info['province_code']];
            $info['city'] = IDCard::$locationCodes [$info['city_code']];
            $info['district'] = IDCard::$locationCodes [$info['district_code']];
            return $info;
        } else {
            return false;
        }
    }

    /**
     * 获取省代码
     * @param string $creditCode
     * @return string
     */
    public static function getProvinceCodeByCreditCode(string $creditCode): string
    {
        return substr($creditCode, 2, 2) . '0000';
    }

    /**
     * 获取机构所在省
     * @param string $creditCode
     * @return mixed
     */
    public static function getProvinceByCreditCode(string $creditCode): string
    {
        $provinceCode = static::getProvinceCodeByCreditCode($creditCode);
        return IDCard::$locationCodes [$provinceCode];
    }

    /**
     * 获取机构所在市
     * @param string $creditCode
     * @return mixed
     */
    public static function getCityCodeByCreditCode(string $creditCode): string
    {
        return substr($creditCode, 2, 4) . '00';
    }

    /**
     * 获取机构所在市
     * @param string $creditCode
     * @return mixed
     */
    public static function getCityByCreditCode(string $creditCode): string
    {
        $cityCode = static::getCityCodeByCreditCode($creditCode);
        return IDCard::$locationCodes [$cityCode];
    }

    /**
     * 获取机构所在县
     * @param string $creditCode
     * @return mixed
     */
    public static function getDistrictCodeByCreditCode(string $creditCode): string
    {
        return substr($creditCode, 2, 6);
    }

    /**
     * 获取机构所在县
     * @param string $creditCode
     * @return mixed
     */
    public static function getDistrictByCreditCode(string $creditCode): string
    {
        $areaCode = static::getDistrictCodeByCreditCode($creditCode);
        return IDCard::$locationCodes [$areaCode];
    }

    /**
     * 验证是否合法
     * @param string $creditCode
     * @return bool
     */
    public static function validateCard(string $creditCode): bool
    {
        if (strlen($creditCode) == static::CHINA_CREDIT_CODE_MAX_LENGTH) {
            // 前17位
            $code17 = substr($creditCode, 0, 17);
            // 第18位
            $code18 = substr($creditCode, 17, 1);
            $code17Arr = str_split($code17);
            if ($code17Arr != null) {
                $iSum17 = static::getPowerSum($code17Arr);
                $val = static::getCheckCode($iSum17);// 获取校验位
                if ($val == static::$transformation[$code18]) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 将power和值与31取模获得余数进行校验码判断
     *
     * @param int $iSum
     * @return string 校验位
     */
    private static function getCheckCode(int $iSum): string
    {
        switch ($iSum % 31) {
            case 0:
                $sCode = 0;
                break;
            default:
                $sCode = 31 - $iSum % 31;
                break;
        }
        return (string)$sCode;
    }

    /**
     * 将每位和对应位的加权因子相乘之后，再得到和值
     *
     * @param array $iArr
     * @return int 编码。
     */
    private static function getPowerSum(array $iArr)
    {
        $iSum = 0;
        $powerLen = count(static::$power);
        $arrLen = count($iArr);
        if ($powerLen == $arrLen) {
            for ($i = 0; $i < $arrLen; $i++) {
                for ($j = 0; $j < $powerLen; $j++) {
                    if ($i == $j) {
                        $iSum += static::$transformation[$iArr[$i]] * static::$power[$j];
                    }
                }
            }
        }
        return $iSum;
    }
}
