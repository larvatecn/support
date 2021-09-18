<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 *
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Tests;

use Larva\Support\UnifiedSocialCreditIdentifier;

/**
 * 测试企业信用代码
 * @author Tongle Xu <xutongle@gmail.com>
 */
class UnifiedSocialCreditIdentifierTest extends TestCase
{
    public function testLen()
    {
        $this->assertFalse(UnifiedSocialCreditIdentifier::validateCard('9111000080210'));
        $this->assertFalse(UnifiedSocialCreditIdentifier::validateCard('91110000802100433B1'));
        $this->assertTrue(UnifiedSocialCreditIdentifier::validateCard('91110000802100433B'));
    }

    public function testInfo()
    {
        $info = UnifiedSocialCreditIdentifier::getInfo('9141010056103928XQ');
        $this->assertTrue(isset($info['type']));
    }

    public function testProvince()
    {
        $this->assertSame('110000', UnifiedSocialCreditIdentifier::getProvinceCodeByCreditCode('91110000802100433B'));
        $this->assertSame('北京市', UnifiedSocialCreditIdentifier::getProvinceByCreditCode('91110000802100433B'));
    }

    public function testCity()
    {
        $this->assertSame('410100', UnifiedSocialCreditIdentifier::getCityCodeByCreditCode('9141010056103928XQ'));
        $this->assertSame('郑州市', UnifiedSocialCreditIdentifier::getCityByCreditCode('9141010056103928XQ'));
    }

    public function testDistrict()
    {
        $this->assertSame('410100', UnifiedSocialCreditIdentifier::getDistrictCodeByCreditCode('9141010056103928XQ'));
        $this->assertSame('郑州市', UnifiedSocialCreditIdentifier::getDistrictByCreditCode('9141010056103928XQ'));

        $this->assertSame('410105', UnifiedSocialCreditIdentifier::getDistrictCodeByCreditCode('91410105MA9G98K57A'));
        $this->assertSame('金水区', UnifiedSocialCreditIdentifier::getDistrictByCreditCode('91410105MA9G98K57A'));
    }
}
