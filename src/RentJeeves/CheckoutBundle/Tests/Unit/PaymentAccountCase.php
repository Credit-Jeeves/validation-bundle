<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use \RuntimeException;
use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType;
use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\PaymentAccountManager;
use RentJeeves\TestBundle\BaseTestCase;
use Symfony\Component\Form\Form;

class PaymentAccountCase extends BaseTestCase
{

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string|FormTypeInterface $type    The built type of the form
     * @param mixed                    $data    The initial data for the form
     * @param array                    $options Options for the form
     *
     * @return Form
     */
    protected function createForm($type, $data = null, array $options = [])
    {
        return $this->getContainer()->get('form.factory')->create($type, $data, $options);
    }

    /**
     * @test
     */
    public function createToken()
    {
        $em = $this->getEntityManager();
        $paymentAccountManager = new PaymentAccountManager(
            $em,
            $this->getContainer()->get('payum2'),
            $this->getContainer()->getParameter('rt_group_code')
        );
       
        $user = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('tenant11@example.com');
        $group = $em->getRepository('DataBundle:Group')->findOneByName('Test Rent Group');
        $paymentAccountType = $this->createForm(new PaymentAccountType($user));
        $view = $paymentAccountType->createView();
        $testData =  [
            'name'                              => 'Test payment',
            'PayorName'                         => 'Timothy APPLEGATE',
            'RoutingNumber'                     => '062202574',
            'AccountNumber'                     => [
                'AccountNumberAgain' => '123245678',
                'AccountNumber'      => '123245678'
            ],
            'ACHDepositType_0'                  => true,
            '_token'                            => $view->children['_token']->vars['value'],

            'type'                              => 'bank',
            'ACHDepositType'                    => 'Checking',
            'CardAccountName'                   => '',
            'CardNumber'                        => '',
            'VerificationCode'                  => '',
            'ExpirationMonth'                   => '',
            'ExpirationYear'                    => '',
            'is_new_address'                    => false,
            'is_new_address_link'               => '',
            'address'                           => [
                'street' => '',
                'city'   => '',
                'area'   => '',
                'zip'    => '',
            ],
            'save'                              => 1,
            'id'                                => '',
            'groupId'                           => $group->getId(),
        ];

        $paymentAccountType->submit($testData);

        $paymentAccountType = $this->getContainer()->get("payment_account.type.mapper")->map($paymentAccountType);
        try {
            $paymentAccountManager->registerPaymentToken(
                $paymentAccountType,
                $user,
                $group->getDepositAccount(DepositAccountType::RENT, PaymentProcessor::HEARTLAND)
            );
        } catch (RuntimeException $e) {
            //if we go into this place, test must be failed and we must show
            //about this on jenkins, because heartland is down.
            $this->assertTrue(false, $e->getMessage()."_".$e->getCode());
        }

        $this->assertNotEmpty($paymentAccountType->getEntity()->getToken());
    }
}
