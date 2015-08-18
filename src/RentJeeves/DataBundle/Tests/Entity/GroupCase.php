<?php
namespace CreditJeeves\DataBundle\Tests\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\GroupSettings;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\TestBundle\BaseTestCase;

class GroupCase extends BaseTestCase
{
    public function providerForCheckValidationForDescriptor()
    {
        return [
            [PaymentProcessor::HEARTLAND, '123456789123456', false],
            [PaymentProcessor::HEARTLAND, '12345678912', true],
            [PaymentProcessor::ACI, '12345678912345678912345', false],
            [PaymentProcessor::ACI, '123456789123456789123', true]
        ];
    }

    /**
     * @test
     * @dataProvider providerForCheckValidationForDescriptor
     */
    public function shouldCheckValidationForDescriptor($paymentProcessor, $statementDescriptor, $result)
    {
        $group = new Group();
        $groupSettings = new GroupSettings();
        $group->setGroupSettings($groupSettings);
        $groupSettings->setPaymentProcessor($paymentProcessor);
        $group->setStatementDescriptor($statementDescriptor);

        $this->assertEquals($result, $group->isValidDescriptor());
    }
}
