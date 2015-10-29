<?php

namespace RentJeeves\DataBundle\Tests\Entity;

use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentType;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\TestBundle\BaseTestCase;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;

class ContractRepositoryCase extends BaseTestCase
{
    public function dataForGetPotentialLateContract()
    {
        $today = new DateTime();
        $dayOfMonth = $today->format('d');
        $randomTest = ($dayOfMonth >= 27) ? true : false;

        return array(
            //#0 When we don't have payment at all and dueDate today
            //We must send email
            array(
                $startAtOfContract = new DateTime("-1 month"),
                $finishAtOfContract = new DateTime("+5 month"),
                $statusOfContract = ContractStatus::APPROVED,
                $startPayment = null,
                $endPayment = null,
                $statusPayment = null,
                $typePayment =  null,
                $isSendEmail = true,
            ),
            //#1 When we have payment but payment in the past started and don't have finishAt
            //We don't need send email
            array(
                $startAtOfContract = new DateTime("-1 month"),
                $finishAtOfContract = new DateTime("+5 month"),
                $statusOfContract = ContractStatus::APPROVED,
                $startPayment = new DateTime("-6 month"),
                $endPayment = null,
                $statusPayment = PaymentStatus::ACTIVE,
                $typePayment =  PaymentType::RECURRING,
                $isSendEmail = false,
            ),
            //#2 When we have payment but payment in the past started and finishAt in future
            //We don't need send email
            array(
                $startAtOfContract = new DateTime("-1 month"),
                $finishAtOfContract = new DateTime("+5 month"),
                $statusOfContract = ContractStatus::APPROVED,
                $startPayment = new DateTime("-6 month"),
                $endPayment = new DateTime("+6 month"),
                $statusPayment = PaymentStatus::ACTIVE,
                $typePayment =  PaymentType::RECURRING,
                $isSendEmail = false,
            ),
            //#3 When we have payment but payment will started in future and don't have finish date
            //We must send email
            array(
                $startAtOfContract = new DateTime("-1 month"),
                $finishAtOfContract = new DateTime("+5 month"),
                $statusOfContract = ContractStatus::CURRENT,
                $startPayment = new DateTime("+1 month"),
                $endPayment = null,
                $statusPayment = PaymentStatus::ACTIVE,
                $typePayment =  PaymentType::IMMEDIATE,
                $isSendEmail =  true,
            ),
            //#4 When we have payment but payment will started in few days and don't have finish date
            //We don't need send email
            // FIXME potential problems when due date of payment after due date of contract
            // https://credit.atlassian.net/browse/RT-490#comment-12526
            array(
                $startAtOfContract = new DateTime("-1 month"),
                $finishAtOfContract = new DateTime("+5 month"),
                $statusOfContract = ContractStatus::CURRENT,
                $startPayment = new DateTime("+5 days"),
                $endPayment = null,
                $statusPayment = PaymentStatus::ACTIVE,
                $typePayment =  PaymentType::RECURRING,
                $isSendEmail =  $randomTest,
            ),
            //#4 When we have payment but payment in the past started and finished
            //We must send email
            array(
                $startAtOfContract = new DateTime("-1 month"),
                $finishAtOfContract = new DateTime("+5 month"),
                $statusOfContract = ContractStatus::APPROVED,
                $startPayment = new DateTime("-6 month"),
                $endPayment = new DateTime("-3 month"),
                $statusPayment = PaymentStatus::ACTIVE,
                $typePayment =  PaymentType::RECURRING,
                $isSendEmail = true,
            ),
            //#6 When we have payment but payment will started in future and  have finish date in future
            //We must send email
            array(
                $startAtOfContract = new DateTime("-1 month"),
                $finishAtOfContract = new DateTime("+5 month"),
                $statusOfContract = ContractStatus::APPROVED,
                $startPayment = new DateTime("+1 month"),
                $endPayment = new DateTime("+2 month"),
                $statusPayment = PaymentStatus::ACTIVE,
                $typePayment =  PaymentType::ONE_TIME,
                $isSendEmail = true,
            )
        );
    }

    /**
     * @dataProvider dataForGetPotentialLateContract
     * @test
     */
    public function getPotentialLateContract(
        $startAtOfContract,
        $finishAtOfContract,
        $statusOfContract,
        $startPayment,
        $endPayment,
        $statusPayment,
        $typePayment,
        $isSendEmail
    ) {
        $this->load(true);
        $today = new DateTime();
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $contract = new Contract();
        $contract->setRent(999999.99);
        $contract->setStartAt($startAtOfContract);
        $contract->setFinishAt($finishAtOfContract);
        $contract->setDueDate($today->format('j'));

        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email'  => 'tenant11@example.com'
            )
        );

        $this->assertNotNull($tenant);
        $contract->setTenant($tenant);
        /**
         * @var $unit Unit
         */
        $unit = $em->getRepository('RjDataBundle:Unit')->findOneBy(
            array(
                'name'  => '1-a'
            )
        );

        $this->assertNotNull($unit);

        $contract->setUnit($unit);
        $contract->setGroup($unit->getGroup());
        $contract->setHolding($unit->getHolding());
        $contract->setProperty($unit->getProperty());
        $contract->setStatus($statusOfContract);

        if ($typePayment !== null) {
            $payment = new Payment();
            $payment->setContract($contract);
            $payment->setDueDate($today->format('j'));
            $payment->setPaidFor($today);
            $payment->setStatus($statusPayment);
            $payment->setType($typePayment);
            $payment->setAmount(980);
            $payment->setTotal(980);
            $payment->setStartMonth($startPayment->format('n'));
            $payment->setStartYear($startPayment->format('Y'));
            if ($endPayment) {
                $payment->setEndMonth($endPayment->format('n'));
                $payment->setEndYear($endPayment->format('Y'));
            }
            $payment->setPaymentAccount($tenant->getPaymentAccounts()->first());

            $em->persist($payment);
        }
        $em->persist($contract);
        $em->flush();
        $contractId = $contract->getId();
        $contractRepository = $em->getRepository('RjDataBundle:Contract');
        $contracts = $contractRepository->getPotentialLateContract(new DateTime());

        if ($contracts) {
            $contracts = array_filter(
                $contracts,
                function (Contract $contract) use ($contractId) {
                    return $contract->getId() == $contractId;
                }
            );
        }

        $this->assertEquals($isSendEmail, 1 == count($contracts));
    }

    public function dataForGetLateContract()
    {
        return array(
            //We don't have order at all and need send email
            array( # 0
                $hasOrder = false,
                $paidFor = null,
                true
            ),
            //We have order for current month, so we don't need send email
            array( # 1
                $hasOrder = true,
                $paidFor = new DateTime("-5 days"),
                false
            ),
            //We have order for current month, so we don't need send email
            array( # 2
                $hasOrder = true,
                $paidFor = new DateTime("-27 days"),
                false
            ),
            //We don't have order for current month send email
            array( # 3
                $hasOrder = true,
                $paidFor = new DateTime("-43 days"),
                true
            ),
            //We don't have order for current month send email
            array( # 4
                $hasOrder = true,
                $paidFor = new DateTime("+43 days"),
                false # originally it was true FIXME Darryl, what should we do her?
            ),
        );
    }

    /**
     * @dataProvider dataForGetLateContract
     * @test
     */
    public function getLateContract($hasOrder, $paidFor, $isLate)
    {
        $this->load(true);
        $today = new DateTime("-5 days");
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $contract = new Contract();
        $contract->setRent(999999.99);
        $contract->setStartAt(new DateTime("-1 month"));
        $contract->setFinishAt(new DateTime("+5 month"));
        $contract->setDueDate($today->format('j'));

        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email'  => 'tenant11@example.com'
            )
        );

        $this->assertNotNull($tenant);
        $contract->setTenant($tenant);

        /**
         * @var $unit Unit
         */
        $unit = $em->getRepository('RjDataBundle:Unit')->findOneBy(
            array(
                'name'  => '1-a'
            )
        );

        $this->assertNotNull($unit);

        $contract->setUnit($unit);
        $contract->setGroup($unit->getGroup());
        $contract->setHolding($unit->getHolding());
        $contract->setProperty($unit->getProperty());
        $contract->setStatus(ContractStatus::CURRENT);

        if ($hasOrder) {
            $order = new OrderSubmerchant();
            $order->setUser($contract->getTenant());
            $order->setSum(500);
            $order->setPaymentType(OrderPaymentType::CARD);
            $order->setStatus(OrderStatus::COMPLETE);

            $operation = new Operation();
            $operation->setContract($contract);
            $operation->setAmount(500);
            $operation->setGroup($contract->getGroup());
            $operation->setType(OperationType::RENT);
            $operation->setPaidFor($paidFor);
            $operation->setOrder($order);

            $em->persist($operation);
            $em->persist($order);
        }

        $em->persist($contract);
        $em->flush();

        $contractId = $contract->getId();

        $contracts = $em->getRepository('RjDataBundle:Contract')->getLateContracts(5);

        if ($contracts) {
            $contracts = array_filter(
                $contracts,
                function (Contract $contract) use ($contractId) {
                    return $contract->getId() == $contractId;
                }
            );
        }

        $this->assertEquals($isLate, 1 == count($contracts));
    }

    /**
     * @test
     */
    public function makeSureContractWaitingIsRemoved()
    {
        $this->load(true);
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine')->getManager();
        /**
         * @var $unit Unit
         */
        $unit = $em->getRepository('RjDataBundle:Unit')->findOneBy(
            array(
                'name' => '1-a'
            )
        );
        $this->assertNotNull($unit);
        $this->assertTrue($unit->getContractsWaiting()->count() === 1);

        $contractWaiting = new ContractWaiting();
        $contractWaiting->setProperty($unit->getProperty());
        $contractWaiting->setUnit($unit);
        $contractWaiting->setGroup($unit->getGroup());
        $contractWaiting->setResidentId('test');
        $contractWaiting->setIntegratedBalance('3333');
        $contractWaiting->setFinishAt(new DateTime());
        $contractWaiting->setStartAt(new DateTime());
        $contractWaiting->setRent('7777');
        $contractWaiting->setFirstName('Hi');
        $contractWaiting->setLastName('ho');

        $em->persist($contractWaiting);
        $em->flush();
        $id = $contractWaiting->getId();
        $em->clear();
        /**
         * @var $unit Unit
         */
        $unit = $em->getRepository('RjDataBundle:Unit')->findOneBy(
            array(
                'name' => '1-a'
            )
        );
        $this->assertNotNull($unit);
        $this->assertTrue($unit->getContractsWaiting()->count() === 2);
        $em->remove($unit);
        $em->flush();
        $em->clear();

        static::$kernel = null;
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->find($id);
        $this->assertEmpty($contractWaiting);
    }
}
