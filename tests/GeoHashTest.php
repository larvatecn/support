<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Tests;

use Larva\Support\GeoHash;

class GeoHashTest extends TestCase
{
    public function testEncode()
    {
        $hash = GeoHash::encode(114.5149, 38.0428, 0.001);
        $this->assertIsString($hash);
    }

    public function testDecode()
    {
        $loc = GeoHash::decode('wwc2m');
        $this->assertIsArray($loc);
    }
}
