<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Support;

/**
 * Class GeoHash
 * @author Tongle Xu <xutongle@gmail.com>
 */
class GeoHash
{
    /**
     * @var string Base32表
     */
    private static $table = "0123456789bcdefghjkmnpqrstuvwxyz";
    private static $bits = [
        0b10000, 0b01000, 0b00100, 0b00010, 0b00001
    ];

    /**
     * 编码
     * @param double $longitude 经度
     * @param double $latitude 维度
     * @param float $prec 精确度
     * @return string
     */
    public static function encode($longitude, $latitude, $prec = 0.00001)
    {
        $minLongitude = -180;
        $maxLongitude = 180;
        $minLatitude = -90;
        $maxLatitude = 90;

        $hash = array();
        $error = 180;
        $isEven = true;
        $chr = 0b00000;
        $b = 0;

        while ($error >= $prec) {
            if ($isEven) {
                $next = ($minLongitude + $maxLongitude) / 2;
                if ($longitude > $next) {
                    $chr |= self::$bits[$b];
                    $minLongitude = $next;
                } else {
                    $maxLongitude = $next;
                }
            } else {
                $next = ($minLatitude + $maxLatitude) / 2;
                if ($latitude > $next) {
                    $chr |= self::$bits[$b];
                    $minLatitude = $next;
                } else {
                    $maxLatitude = $next;
                }
            }
            $isEven = !$isEven;

            if ($b < 4) {
                $b++;
            } else {
                $hash[] = self::$table[$chr];
                $error = max($maxLongitude - $minLongitude, $maxLatitude - $minLatitude);
                $b = 0;
                $chr = 0b00000;
            }
        }
        return join('', $hash);
    }

    /**
     * 解码
     *
     * @param string $hash geo hash
     * @return array array($minLongitude, $maxLongitude, $minLatitude, $maxLatitude);
     */
    public static function decode(string $hash)
    {
        $minLongitude = -180;
        $maxLongitude = 180;
        $minLatitude = -90;
        $maxLatitude = 90;

        for ($i = 0, $c = strlen($hash); $i < $c; $i++) {
            $v = strpos(self::$table, $hash[$i]);
            if (1 & $i) {
                if (16 & $v) {
                    $minLatitude = ($minLatitude + $maxLatitude) / 2;
                } else {
                    $maxLatitude = ($minLatitude + $maxLatitude) / 2;
                }
                if (8 & $v) {
                    $minLongitude = ($minLongitude + $maxLongitude) / 2;
                } else {
                    $maxLongitude = ($minLongitude + $maxLongitude) / 2;
                }
                if (4 & $v) {
                    $minLatitude = ($minLatitude + $maxLatitude) / 2;
                } else {
                    $maxLatitude = ($minLatitude + $maxLatitude) / 2;
                }
                if (2 & $v) {
                    $minLongitude = ($minLongitude + $maxLongitude) / 2;
                } else {
                    $maxLongitude = ($minLongitude + $maxLongitude) / 2;
                }
                if (1 & $v) {
                    $minLatitude = ($minLatitude + $maxLatitude) / 2;
                } else {
                    $maxLatitude = ($minLatitude + $maxLatitude) / 2;
                }
            } else {
                if (16 & $v) {
                    $minLongitude = ($minLongitude + $maxLongitude) / 2;
                } else {
                    $maxLongitude = ($minLongitude + $maxLongitude) / 2;
                }
                if (8 & $v) {
                    $minLatitude = ($minLatitude + $maxLatitude) / 2;
                } else {
                    $maxLatitude = ($minLatitude + $maxLatitude) / 2;
                }
                if (4 & $v) {
                    $minLongitude = ($minLongitude + $maxLongitude) / 2;
                } else {
                    $maxLongitude = ($minLongitude + $maxLongitude) / 2;
                }
                if (2 & $v) {
                    $minLatitude = ($minLatitude + $maxLatitude) / 2;
                } else {
                    $maxLatitude = ($minLatitude + $maxLatitude) / 2;
                }
                if (1 & $v) {
                    $minLongitude = ($minLongitude + $maxLongitude) / 2;
                } else {
                    $maxLongitude = ($minLongitude + $maxLongitude) / 2;
                }
            }
        }
        return [$minLongitude, $maxLongitude, $minLatitude, $maxLatitude];
    }

    /**
     * 扩展一堆坐标点
     * @param string $hash
     * @param float $prec
     * @return string[]
     */
    public static function expand(string $hash, $prec = 0.00001)
    {
        list($minLongitude, $maxLongitude, $minLatitude, $maxLatitude) = self::decode($hash);
        $dlng = ($maxLongitude - $minLongitude) / 2;
        $dlat = ($maxLatitude - $minLatitude) / 2;

        return [
            self::encode($minLongitude - $dlng, $maxLatitude + $dlat, $prec),
            self::encode($minLongitude + $dlng, $maxLatitude + $dlat, $prec),
            self::encode($maxLongitude + $dlng, $maxLatitude + $dlat, $prec),
            self::encode($minLongitude - $dlng, $maxLatitude - $dlat, $prec),
            self::encode($maxLongitude + $dlng, $maxLatitude - $dlat, $prec),
            self::encode($minLongitude - $dlng, $minLatitude - $dlat, $prec),
            self::encode($minLongitude + $dlng, $minLatitude - $dlat, $prec),
            self::encode($maxLongitude + $dlng, $minLatitude - $dlat, $prec),
        ];
    }

    public static function getRect($hash)
    {
        list($minLongitude, $maxLongitude, $minLatitude, $maxLatitude) = self::decode($hash);

        return [
            [$minLongitude, $minLatitude],
            [$minLongitude, $maxLatitude],
            [$maxLongitude, $maxLatitude],
            [$maxLongitude, $minLatitude],
        ];
    }
}