<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit;

use \RuntimeException;
use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType;
use RentJeeves\CheckoutBundle\Payment\PaymentAccount;
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
    public function createForm($type, $data = null, array $options = array())
    {
        return $this->getContainer()->get('form.factory')->create($type, $data, $options);
    }

    /**
     * @test
     */
    public function correct()
    {
        $payum = $this->getContainer()->get('payum');
        $paymentAccount = new PaymentAccount();
        $paymentAccount->setPayum($payum);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array('email' => 'tenant11@example.com')
        );
        $group = $em->getRepository('DataBundle:Group')->findOneBy(
            array(
                'name'  => 'Test Rent Group',
            )
        );
        $paymentAccountType = $this->createForm(new PaymentAccountType($user));
        $view = $paymentAccountType->createView();
        $testData =  array(
            'name'                              => 'Test payment',
            'PayorName'                         => 'Timothy APPLEGATE',
            'RoutingNumber'                     => '062202574',
            'AccountNumber'                     => array(
                'AccountNumberAgain' => '123245678',
                'AccountNumber'      => '123245678'
            ),
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
            'address'                           => array(
                'street' => '',
                'city'   => '',
                'area'   => '',
                'zip'    => '',
            ),
            'save'                              => 1,
            'id'                                => '',
            'groupId'                           => $group->getId(),
        );

        $paymentAccountType->submit($testData);
        $tokenRequest = $paymentAccount->getTokenRequest(
            $paymentAccountType,
            $user
        );
        try {
            $token = $paymentAccount->getTokenResponse(
                $tokenRequest,
                $merchantName = $group->getMerchantName()
            );
        } catch (RuntimeException $e) {
            $this->assertTrue(false, $e->getMessage()."_".$e->getCode());
        }

        $this->assertNotEmpty($token);
    }
}
