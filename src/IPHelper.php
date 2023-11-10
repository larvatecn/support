<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Support;

/**
 * IP助手
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class IPHelper
{
    public const IPV4 = 4;
    public const IPV6 = 6;

    /**
     * The length of IPv6 address in bits
     */
    public const IPV6_ADDRESS_LENGTH = 128;

    /**
     * The length of IPv4 address in bits
     */
    public const IPV4_ADDRESS_LENGTH = 32;

    /**
     * 模糊掉IP后两段
     * @param string $ip
     * @return string
     */
    public static function fuzzyIpV4(string $ip): string
    {
        $ipArray = explode('.', $ip);
        return $ipArray[0] . '.' . $ipArray[1] . '.*.*';
    }

    /**
     * 模糊掉IP后1段
     * @param string $ip
     * @return string
     */
    public static function fuzzyIpv4End(string $ip): string
    {
        $ipArray = explode('.', $ip);
        return $ipArray[0] . '.' . $ipArray[1] . '.' . $ipArray[2] . '.*';
    }

    /**
     * 获取开始IP段
     * @param string $ip
     * @return string
     */
    public static function startIpv4(string $ip): string
    {
        $ipArray = explode('.', $ip);
        return $ipArray[0] . '.' . $ipArray[1] . '.' . $ipArray[2] . '.0';
    }

    /**
     * 获取结束IP段
     * @param string $ip
     * @return string
     */
    public static function endIpv4(string $ip): string
    {
        $ipArray = explode('.', $ip);
        return $ipArray[0] . '.' . $ipArray[1] . '.' . $ipArray[2] . '.255';
    }

    /**
     * 获取起始IP段的整形
     * @param string $ip
     * @return float|int
     */
    public static function startIpv4Long(string $ip)
    {
        return static::ip2long(static::startIpv4($ip));
    }

    /**
     * 获取结束IP段的整形
     * @param string $ip
     * @return float|int
     */
    public static function endIpv4Long(string $ip)
    {
        return static::ip2long(static::endIpv4($ip));
    }

    /**
     * ip2long
     * @param string $ip
     * @return float|int
     */
    public static function ip2Long(string $ip)
    {
        return bindec(decbin(ip2long($ip)));
    }

    /**
     * 获取起始IP段
     * @param string $ip
     * @return array
     */
    public static function segmentForIpv4(string $ip): array
    {
        return [static::startIpv4Long($ip), static::endIpv4Long($ip)];
    }

    /**
     * 是否是私有IP
     * @param string $ip
     * @return bool
     */
    public static function isPrivateForIpV4(string $ip): bool
    {
        $ipLong = static::startIpv4Long($ip);
        if (($ipLong >= 167772160 && $ipLong <= 184549375)//10.0.0.0/8 //局域网
            || ($ipLong >= 2130706432 && $ipLong <= 2147483647)//127.0.0.0/8 //局域网
            || ($ipLong >= 2851995648 && $ipLong <= 2852061183)//169.254.0.0/16 本地环路
            || ($ipLong >= 3221225472 && $ipLong <= 3221291007)//192.0.0.0/24  //局域网
            || ($ipLong >= 3221225984 && $ipLong <= 3221226239)//192.0.2.0/24 保留地址
            || ($ipLong >= 3232235520 && $ipLong <= 3232301055)//192.168.0.0/16 //局域网
            || ($ipLong >= 3323068416 && $ipLong <= 3323199487)//198.18.0.0/15 保留地址
            || ($ipLong >= 3223307264 && $ipLong <= 3223307519)//192.31.196.0/24 该 IP 段为 ANYCAST IP 段 该 IP 段为 IDC 机房使用，可能包括部分骨干网数据。
            || ($ipLong >= 3325256704 && $ipLong <= 3325256959)//198.51.100.0/24 保留地址
            || ($ipLong >= 3224682752 && $ipLong <= 3224683007)//192.52.193.0/24 //保留地址
            || ($ipLong >= 3227017984 && $ipLong <= 3227018239)//192.88.99.0/24 //保留地址
            || ($ipLong >= 3232706560 && $ipLong <= 3232706815)//192.175.48.0/24 任播地址
            || ($ipLong >= 2886729728 && $ipLong <= 2887778303)//172.16.0.0-172.31.0.0 //局域网
            || ($ipLong >= 1681915904 && $ipLong <= 1686110207)//100.64.0.0-100.127.0.0.0 城域网NAT
            || ($ipLong >= 3405803776 && $ipLong <= 3405804031)//203.0.113.0/24 保留地址
            || ($ipLong >= 3758096384 && $ipLong <= 4026531839)//224.0.0.0-239.255.255.255 组播地址
            || ($ipLong >= 4026531840 && $ipLong <= 4294967295)//240.0.0.0-255.255.255.255 保留地址
        ) {
            return true;
        }
        return false;
    }

    /**
     * 获取IP版本。 不执行IP地址验证。
     *
     * @param string $ip the valid IPv4 or IPv6 address.
     * @return int [[IPV4]] or [[IPV6]]
     */
    public static function getIpVersion(string $ip): int
    {
        return !str_contains($ip, ':') ? self::IPV4 : self::IPV6;
    }

    /**
     * Checks whether IP address or subnet $subnet is contained by $subnet.
     *
     * For example, the following code checks whether subnet `192.168.1.0/24` is in subnet `192.168.0.0/22`:
     *
     * ```php
     * Ip::inRange('192.168.1.0/24', '192.168.0.0/22'); // true
     * ```
     *
     * In case you need to check whether a single IP address `192.168.1.21` is in the subnet `192.168.1.0/24`,
     * you can use any of theses examples:
     *
     * ```php
     * Ip::inRange('192.168.1.21', '192.168.1.0/24'); // true
     * Ip::inRange('192.168.1.21/32', '192.168.1.0/24'); // true
     * ```
     *
     * @param string $subnet the valid IPv4 or IPv6 address or CIDR range, e.g.: `10.0.0.0/8` or `2001:af::/64`
     * @param string $range the valid IPv4 or IPv6 CIDR range, e.g. `10.0.0.0/8` or `2001:af::/64`
     * @return bool whether $subnet is contained by $range
     *
     * @see https://en.wikipedia.org/wiki/Classless_Inter-Domain_Routing
     */
    public static function inRange(string $subnet, string $range): bool
    {
        [$ip, $mask] = array_pad(explode('/', $subnet), 2, null);
        [$net, $netMask] = array_pad(explode('/', $range), 2, null);
        $ipVersion = static::getIpVersion($ip);
        $netVersion = static::getIpVersion($net);
        if ($ipVersion !== $netVersion) {
            return false;
        }
        $maxMask = $ipVersion === self::IPV4 ? self::IPV4_ADDRESS_LENGTH : self::IPV6_ADDRESS_LENGTH;
        $mask = $mask ?? $maxMask;
        $netMask = $netMask ?? $maxMask;
        $binIp = static::ip2bin($ip);
        $binNet = static::ip2bin($net);
        return substr($binIp, 0, $netMask) === substr($binNet, 0, $netMask) && $mask >= $netMask;
    }

    /**
     * 将IPv6地址扩展为完整的表示法。
     *
     * For example `2001:db8::1` will be expanded to `2001:0db8:0000:0000:0000:0000:0000:0001`
     *
     * @param string $ip the original valid IPv6 address
     * @return string|false the expanded IPv6 address; or boolean false, if IP address parsing failed
     */
    public static function expandIPv6(string $ip): bool|string
    {
        $addr = inet_pton($ip);
        if ($addr === false) {
            return false;
        }
        $hex = unpack('H*hex', $addr);
        return substr(preg_replace('/([a-f0-9]{4})/i', '$1:', $hex['hex']), 0, -1);
    }

    /**
     * 将IP地址转换为位表示。
     *
     * @param string $ip the valid IPv4 or IPv6 address
     * @return string bits as a string
     */
    public static function ip2bin(string $ip): string
    {
        if (static::getIpVersion($ip) === self::IPV4) {
            return str_pad(base_convert(sprintf('%u', ip2long($ip)), 10, 2), self::IPV4_ADDRESS_LENGTH, '0', STR_PAD_LEFT);
        }
        $unpack = unpack('A16', inet_pton($ip));
        $binStr = array_shift($unpack);
        $bytes = self::IPV6_ADDRESS_LENGTH / 8; // 128 bit / 8 = 16 bytes
        $result = '';
        while ($bytes-- > 0) {
            $result = sprintf('%08b', isset($binStr[$bytes]) ? ord($binStr[$bytes]) : '0') . $result;
        }
        return $result;
    }

    /**
     * DNS解析
     * @param string $host
     * @param int $type 解析类型
     * @param boolean $onlyIp 仅获取IP
     * @return array|bool
     */
    public static function dnsRecord(string $host, int $type = DNS_A, bool $onlyIp = false): array|bool
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return [$host];
        }
        $dnsRecord = @dns_get_record($host, $type);
        if ($dnsRecord) {
            if ($onlyIp) {
                return array_column($dnsRecord, 'ip');
            }
            return $dnsRecord;
        }
        return false;
    }

    /**
     * 获取主机IPV4地址
     * @param string $host
     * @return false|string
     */
    public static function getHostIpV4(string $host): bool|string
    {
        $ips = IPHelper::dnsRecord($host, DNS_A, true);
        if ($ips) {
            return array_shift($ips);
        }
        return false;
    }

    /**
     * 获取主机IPV6地址
     * @param string $host
     * @return false|string
     */
    public static function getHostIpV6(string $host): bool|string
    {
        $ips = IPHelper::dnsRecord($host, DNS_AAAA, true);
        if ($ips) {
            return array_shift($ips);
        }
        return false;
    }
}
