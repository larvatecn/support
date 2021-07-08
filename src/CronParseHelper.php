<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare(strict_types=1);

namespace Larva\Support;

use Larva\Support\Exception\Exception;

/**
 * crontab格式解析工具类
 * @see https://gitee.com/jianglibin/codes/nfo3y04l6p1q9tu7xdske90
 * @author jlb <497012571@qq.com>
 */
class CronParseHelper
{
    protected static $tags = [];

    protected static $weekMap = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    /**
     * 检查crontab格式是否支持
     * @param string $cronstr
     * @param bool $checkCount
     * @return boolean true|false
     */
    public static function check(string $cronstr, bool $checkCount = true): bool
    {
        $cronstr = trim($cronstr);

        $splitTags = preg_split('#\s+#', $cronstr);

        if ($checkCount && count($splitTags) !== 5) {
            return false;
        }

        foreach ($splitTags as $tag) {
            $r = '#^\*(\/\d+)?|\d+([\-\/]\d+(\/\d+)?)?(,\d+([\-\/]\d+(\/\d+)?)?)*$#';
            if (preg_match($r, $tag) == false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 格式化crontab格式字符串
     * @param string $cronStr
     * @param int $maxSize 设置返回符合条件的时间数量, 默认为1
     * @return array 返回符合格式的时间
     * @throws Exception
     */
    public static function formatToDate(string $cronStr, int $maxSize = 1)
    {
        if (!static::check($cronStr)) {
            throw new Exception("wrong format: $cronStr", 1);
        }
        self::$tags = preg_split('#\s+#', $cronStr);
        $crons = [
            'minutes' => static::parseTag(self::$tags[0], 0, 59), //分钟
            'hours' => static::parseTag(self::$tags[1], 0, 23), //小时
            'day' => static::parseTag(self::$tags[2], 1, 31), //一个月中的第几天
            'month' => static::parseTag(self::$tags[3], 1, 12), //月份
            'week' => static::parseTag(self::$tags[4], 0, 6), // 星期
        ];
        $crons['week'] = array_map(function ($item) {
            return static::$weekMap[$item];
        }, $crons['week']);
        return self::getDateList($crons, $maxSize);
    }

    /**
     * 递归获取符合格式的日期,直到取到满足$maxSize的数为止
     * @param  array $crons 解析crontab字符串后的数组
     * @param int $maxSize 最多返回多少数据的时间
     * @param int|null $year 指定年
     * @return array|null 符合条件的日期
     */
    private static function getDateList(array $crons, int $maxSize, int $year = null)
    {
        $dates = [];

        // 年份基点
        $nowyear = ($year) ?: date('Y');

        // 时间基点已当前为准,用于过滤小于当前时间的日期
        $nowtime = strtotime(date("Y-m-d H:i"));

        foreach ($crons['month'] as $month) {
            // 获取此月最大天数
            $maxDay = cal_days_in_month(CAL_GREGORIAN, $month, $nowyear);
            foreach (range(1, $maxDay) as $day) {
                foreach ($crons['hours'] as $hours) {
                    foreach ($crons['minutes'] as $minutes) {
                        $i = mktime($hours, $minutes, 0, $month, $day, $nowyear);
                        if ($nowtime >= $i) {
                            continue;
                        }

                        $date = getdate($i);

                        // 解析是第几天
                        if (self::$tags[2] != '*' && in_array($date['mday'], $crons['day'])) {
                            $dates[] = date('Y-m-d H:i', $i);
                        }

                        // 解析星期几
                        if (self::$tags[4] != '*' && in_array($date['weekday'], $crons['week'])) {
                            $dates[] = date('Y-m-d H:i', $i);
                        }

                        // 天与星期几
                        if (self::$tags[2] == '*' && self::$tags[4] == '*') {
                            $dates[] = date('Y-m-d H:i', $i);
                        }

                        $dates = array_unique($dates);

                        if (isset($dates) && count($dates) == $maxSize) {
                            break 4;
                        }
                    }
                }
            }
        }

        // 已经递归获取了.但是还是没拿到符合的日期时间,说明指定的时期时间有问题
        if ($year && !count($dates)) {
            return [];
        }

        if (count($dates) != $maxSize) {
            // 向下一年递归
            $dates = array_merge(self::getDateList($crons, $maxSize, ($nowyear + 1)), $dates);
        }

        return $dates;
    }

    /**
     * 解析元素
     * @param string $tag 元素标签
     * @param integer $tmin 最小值
     * @param integer $tmax 最大值
     * @return array
     * @throws Exception
     */
    private static function parseTag(string $tag, int $tmin, int $tmax): array
    {
        if ($tag == '*') {
            return range($tmin, $tmax);
        }

        $step = 1;
        $dateList = [];

        // x-x/2 情况
        if (false !== strpos($tag, ',')) {
            $tmp = explode(',', $tag);
            // 处理 xxx-xxx/x,x,x-x
            foreach ($tmp as $t) {
                if (self::checkExp($t)) {// 递归处理
                    $dateList = array_merge(self::parseTag($t, $tmin, $tmax), $dateList);
                } else {
                    $dateList[] = $t;
                }
            }
        } elseif (false !== strpos($tag, '/') && false !== strpos($tag, '-')) {
            list($number, $mod) = explode('/', $tag);
            list($left, $right) = explode('-', $number);
            if ($left > $right) {
                throw new Exception("$tag not support.");
            }
            foreach (range($left, $right) as $n) {
                if ($n % $mod === 0) {
                    $dateList[] = $n;
                }
            }
        } elseif (false !== strpos($tag, '/')) {
            $tmp = explode('/', $tag);
            $step = $tmp[1] ?? 1;
            $dateList = range($tmin, $tmax, $step);
        } elseif (false !== strpos($tag, '-')) {
            list($left, $right) = explode('-', $tag);
            if ($left > $right) {
                throw new Exception("$tag not support.");
            }
            $dateList = range($left, $right, $step);
        } else {
            $dateList = array($tag);
        }

        // 越界判断
        foreach ($dateList as $num) {
            if ($num < $tmin || $num > $tmax) {
                throw new Exception('Out of bounds.');
            }
        }

        sort($dateList);

        return array_unique($dateList);
    }

    /**
     * 判断tag是否可再次切割
     * @return bool 需要切割的标识符|null
     */
    private static function checkExp($tag): bool
    {
        return (false !== strpos($tag, ',')) || (false !== strpos($tag, '-')) || (false !== strpos($tag, '/'));
    }
}
