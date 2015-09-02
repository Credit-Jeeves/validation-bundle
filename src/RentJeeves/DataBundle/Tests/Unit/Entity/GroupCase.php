<?php
namespace RentJeeves\DataBundle\Tests\Unit\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\TestBundle\BaseTestCase;
use RentJeeves\DataBundle\Entity\GroupSettings;
use RentJeeves\DataBundle\Enum\PaymentProcessor;

class GroupCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCheckSetterOrderAlgorithmWhenItIsCorrect()
    {
        $group = new Group();
        $this->assertEquals(OrderAlgorithmType::SUBMERCHANT, $group->getOrderAlgorithm());
        $group->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $this->assertEquals(OrderAlgorithmType::PAYDIRECT, $group->getOrderAlgorithm());
        $group->setOrderAlgorithm(OrderAlgorithmType::SUBMERCHANT);
        $this->assertEquals(OrderAlgorithmType::SUBMERCHANT, $group->getOrderAlgorithm());

    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldCheckSetterOrderAlgorithmWhenItIsWrong()
    {
        $group = new Group();
        $group->setOrderAlgorithm(null);
    }

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

    /**
     * @test
     */
    public function shouldCheckGroupValidation()
    {
        $group = new Group();
        $groupSettings = new GroupSettings();
        $group->setGroupSettings($groupSettings);
        $groupSettings->setPaymentProcessor(PaymentProcessor::ACI);
        $group->setStatementDescriptor('12345678912345678912345');
        $validator = $this->getContainer()->get('validator');
        $errors = $validator->validate($group, ['holding']);
        $this->assertCount(1, $errors);
    }

    /**
     * @test
     */
    public function shouldGetGroupAccountNumberPerRent()
    {
        $this->load(true);
        /** @var Group $group */
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneByName('Test Rent Group');
        $this->assertNotEmpty($group, 'We should get from fixtures group with name "Test Rent Group"');
        $group->getGroupSettings()->setPaymentProcessor(PaymentProcessor::HEARTLAND);
        $this->getEntityManager()->flush();
        $depositAccount = $group->getRentDepositAccountForCurrentPaymentProcessor();
        $depositAccount->setAccountNumber(999);
        $this->getEntityManager()->flush();

        $this->assertNotEmpty(
            $group->getRentAccountNumberPerCurrentPaymentProcessor(),
            'We should get account number from this group.'
        );
        $this->assertEquals(999, $group->getRentAccountNumberPerCurrentPaymentProcessor());
        $depositAccount->setAccountNumber(null);
        $this->getEntityManager()->flush();
        $this->assertEmpty(
            $group->getRentAccountNumberPerCurrentPaymentProcessor(),
            'We should get empty account number from this group.'
        );

        $depositAccount->setAccountNumber(999);
        $group->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI);
        $this->getEntityManager()->flush();

        $this->assertEmpty(
            $group->getRentAccountNumberPerCurrentPaymentProcessor(),
            'We should get empty account number from this group.'
        );
    }
}
