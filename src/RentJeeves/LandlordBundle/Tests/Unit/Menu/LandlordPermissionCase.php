<?php

namespace RentJeeves\LandlordBundle\Tests\Unit\Menu;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\CoreBundle\Session\Landlord as SessionLandlord;
use RentJeeves\LandlordBundle\Menu\LandlordPermission;

class LandlordPermissionCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldGetAccessToPropertiesTabIfGroupSettingIsOn()
    {
        $permission = new LandlordPermission($this->getSessionUserMock(true));
        $this->assertTrue($permission->hasAccessToPropertiesTab());
    }

    /**
     * @test
     */
    public function shouldNotGetAccessToPropertiesTabIfGroupSettingIsOff()
    {
        $permission = new LandlordPermission($this->getSessionUserMock(false));
        $this->assertFalse($permission->hasAccessToPropertiesTab());
    }

    /**
     * @param $showPropertiesTabValue
     * @return \PHPUnit_Framework_MockObject_MockObject|SessionLandlord
     */
    protected function getSessionUserMock($showPropertiesTabValue)
    {
        $mock = $this->getMock('\RentJeeves\CoreBundle\Session\Landlord', [], [], '', false);

        $mock->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue(new Landlord()));

        $group = new Group();
        $group->getGroupSettings()->setShowPropertiesTab($showPropertiesTabValue);

        $mock->expects($this->once())
            ->method('getGroup')
            ->will($this->returnValue($group));

        return $mock;
    }
}
