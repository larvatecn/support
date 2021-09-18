<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 *
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Tests;

use Larva\Support\IDCard;

class IDCardTest extends TestCase
{
    public function testProvince()
    {
        $this->assertSame('370000', IDCard::getProvinceCodeByIdCard('370481199502010714'));
        $this->assertSame('山东省', IDCard::getProvinceByIdCard('370481199502010714'));
    }

    public function testCity()
    {
        $this->assertSame('370400', IDCard::getCityCodeByIdCard('370481199502010714'));
        $this->assertSame('枣庄市', IDCard::getCityByIdCard('370481199502010714'));
    }

    public function testDistrict()
    {
        $this->assertSame('370481', IDCard::getDistrictCodeByIdCard('370481199502010714'));
        $this->assertSame('滕州市', IDCard::getDistrictByIdCard('370481199502010714'));
    }
}