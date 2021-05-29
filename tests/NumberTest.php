<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Support\Tests;

use Larva\Support\Number;

class NumberTest extends TestCase
{
    public function testFloat()
    {
        $a = Number::float(5.325);
        $this->assertIsFloat($a);
        $this->assertTrue($a == 5.33);

        $b = Number::float(5.323);
        $this->assertIsFloat($b);
        $this->assertTrue($b == 5.32);
    }

    public function testCNY()
    {
        $a = Number::cny(5.325);
        $this->assertIsString($a);
        $this->assertTrue($a == '¥5.32');

        $b = Number::cny(5.323);
        $this->assertIsString($a);
        $this->assertTrue($b == '¥5.32');
    }
}