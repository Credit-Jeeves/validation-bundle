<?php
namespace RentJeeves\AdminBundle\Admin;

use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

class DepositAccountAdmin extends Admin
{
    /**
     * @param ListMapper $listMapper
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('merchantName')
            ->add('type')
            ->add('paymentProcessor')
            ->add('status')
            ->add(
                '_action',
                'actions',
                [
                    'actions' => [
                        'add' => [],
                        'edit' => [],
                        'delete' => [],
                    ]
                ]
            );
    }

    /**
     * @param FormMapper $formMapper
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $depositAccountStatuses = DepositAccountStatus::cachedTitles();
        $paymentProcessors = PaymentProcessor::cachedTitles();
        $depositAccountTypes = DepositAccountType::cachedTitles();

        $formMapper
            ->add(
                'paymentProcessor',
                'choice',
                [
                    'choices' => $paymentProcessors
                ]
            )
            ->add(
                'type',
                'choice',
                [
                    'choices' => $depositAccountTypes
                ]
            )
            ->add('merchantName')
            ->add(
                'status',
                'choice',
                [
                    'choices' => $depositAccountStatuses
                ]
            )
            ->add(
                'accountNumber',
                'text',
                [
                    'required' => false,
                ]
            );

    }
}
