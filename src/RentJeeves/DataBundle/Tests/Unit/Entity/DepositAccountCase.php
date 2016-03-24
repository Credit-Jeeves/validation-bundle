<?php
namespace RentJeeves\DataBundle\Tests\Unit\Entity;

use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class DepositAccountCase extends UnitTestBase
{
    /**
     * @return array
     */
    public function providerForGetTitleName()
    {
        return [
            ['FriendlyName', DepositAccountType::APPLICATION_FEE, 'FriendlyName'],
            ['', DepositAccountType::APPLICATION_FEE, DepositAccountType::title(DepositAccountType::APPLICATION_FEE)],
            [null, DepositAccountType::APPLICATION_FEE, DepositAccountType::title(DepositAccountType::APPLICATION_FEE)],
        ];
    }

    /**
     * @test
     * @dataProvider providerForGetTitleName
     *
     * @param string $friendlyName
     * @param string $depositType
     * @param string $result
     */
    public function shouldTestValueForGetTitleName($friendlyName, $depositType, $result)
    {
        $depositAccount = new DepositAccount();
        $depositAccount->setFriendlyName($friendlyName);
        $depositAccount->setType($depositType);

        $this->assertEquals(
            $result,
            $depositAccount->getTitleName(),
            sprintf('Should be %s instead of %s', $result, $depositAccount->getTitleName())
        );
    }
}
