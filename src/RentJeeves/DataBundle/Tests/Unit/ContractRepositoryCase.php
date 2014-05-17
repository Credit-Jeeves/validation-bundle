<?php

namespace RentJeeves\DataBundle\Tests\Unit;

use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentType;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\TestBundle\BaseTestCase;

class ContractRepositoryCase extends BaseTestCase
{
    public function  dataForGetPotentialLateContract()
    {
        return array(
            //When we don't have payment at all and dueDate today
            //We must send email
            array(
                $startAtOfContract = new DateTime("-1 month"),
                $finishAtOfContract = new DateTime("+5 month"),
                $statusOfContract = ContractStatus::APPROVED,
                $startPayment = null,
                $endPayment = null,
                $statusPayment = null,
                $typePayment =  null,
                $countEmails = 1,
            ),
            //When we have payment but payment in the past started and don't have finishAt
            //We don't need send email
            array(
                $startAtOfContract = new DateTime("-1 month"),
                $finishAtOfContract = new DateTime("+5 month"),
                $statusOfContract = ContractStatus::APPROVED,
                $startPayment = new DateTime("-6 month"),
                $endPayment = null,
                $statusPayment = PaymentStatus::ACTIVE,
                $typePayment =  PaymentType::RECURRING,
                $countEmails = 0,
            ),
            //When we have payment but payment in the past started and finishAt in future
            //We don't need send email
            array(
                $startAtOfContract = new DateTime("-1 month"),
                $finishAtOfContract = new DateTime("+5 month"),
                $statusOfContract = ContractStatus::APPROVED,
                $startPayment = new DateTime("-6 month"),
                $endPayment = new DateTime("+6 month"),
                $statusPayment = PaymentStatus::ACTIVE,
                $typePayment =  PaymentType::RECURRING,
                $countEmails = 0,
            ),
            //When we have payment but payment will started in future and don't have finish date
            //We must send email
            array(
                $startAtOfContract = new DateTime("-1 month"),
                $finishAtOfContract = new DateTime("+5 month"),
                $statusOfContract = ContractStatus::CURRENT,
                $startPayment = new DateTime("+1 month"),
                $endPayment = null,
                $statusPayment = PaymentStatus::ACTIVE,
                $typePayment =  PaymentType::IMMEDIATE,
                $countEmails =  1,
            ),
            //When we have payment but payment in the past started and finished
            //We must send email
            array(
                $startAtOfContract = new DateTime("-1 month"),
                $finishAtOfContract = new DateTime("+5 month"),
                $statusOfContract = ContractStatus::APPROVED,
                $startPayment = new DateTime("-6 month"),
                $endPayment = new DateTime("-3 month"),
                $statusPayment = PaymentStatus::ACTIVE,
                $typePayment =  PaymentType::RECURRING,
                $countEmails = 1,
            ),
            //When we have payment but payment will started in future and  have finish date in future
            //We must send email
            array(
                $startAtOfContract = new DateTime("-1 month"),
                $finishAtOfContract = new DateTime("+5 month"),
                $statusOfContract = ContractStatus::APPROVED,
                $startPayment = new DateTime("+1 month"),
                $endPayment = new DateTime("+2 month"),
                $statusPayment = PaymentStatus::ACTIVE,
                $typePayment =  PaymentType::ONE_TIME,
                $countEmails = 1
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
        $count
    ) {
        $this->load(true);
        $today = new DateTime();
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $contract = new Contract();
        $contract->setRent(999999.99);
        $contract->setBalance(9999.89);
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
            $payment->setStatus($statusPayment);
            $payment->setType($typePayment);
            $payment->setAmount(980);
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

        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $contractRepository = $em->getRepository('RjDataBundle:Contract');
        $contracts = $contractRepository->getPotentialLateContract(new DateTime());
        $this->assertCount($count, $contracts);
    }
}
