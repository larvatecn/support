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
    public function testValidateCard()
    {
        $this->assertTrue(IDCard::validateCard('370481199502010714'));
        $this->assertFalse(IDCard::validateCard('370481199502010712'));
    }

    public function testGenerateCard()
    {
        $idCard = IDCard::generateCard('370481', 'M', (int)date('Ymd', time()));
        $this->assertTrue(IDCard::validateCard($idCard));
    }

    public function testGetAgeByIdCard()
    {
        $idCard = IDCard::generateCard('370481', 'M', (int)date('Ymd', time() - 31536000));
        $this->assertSame(1, IDCard::getAgeByIdCard($idCard));
    }

    public function testGetBirthdayByIdCard()
    {
        $idCard = IDCard::generateCard('370481', 'M', (int)date('Ymd', time()));
        $this->assertSame(date('Y-m-d', time()), IDCard::getBirthdayByIdCard($idCard));
    }

    public function testGetGenderByIdCard()
    {
        $idCard = IDCard::generateCard('370481', 'M', (int)date('Ymd', time()));
        $this->assertSame('M', IDCard::getGenderByIdCard($idCard));
        $idCard = IDCard::generateCard('370481', 'F', (int)date('Ymd', time()));
        $this->assertSame('F', IDCard::getGenderByIdCard($idCard));
    }

    public function testGetProvinceCodeByIdCard()
    {
        $this->assertSame('370000', IDCard::getProvinceCodeByIdCard('370481199502010714'));
    }

    public function testGetProvinceByIdCard()
    {
        $this->assertSame('山东省', IDCard::getProvinceByIdCard('370481199502010714'));
    }

    public function testGetCityCodeByIdCard()
    {
        $this->assertSame('370400', IDCard::getCityCodeByIdCard('370481199502010714'));
    }

    public function testGetCityByIdCard()
    {
        $this->assertSame('枣庄市', IDCard::getCityByIdCard('370481199502010714'));
    }

    public function testGetDistrictCodeByIdCard()
    {
        $this->assertSame('370481', IDCard::getDistrictCodeByIdCard('370481199502010714'));
    }

    public function testGetDistrictByIdCard()
    {
        $this->assertSame('滕州市', IDCard::getDistrictByIdCard('370481199502010714'));
    }
}
