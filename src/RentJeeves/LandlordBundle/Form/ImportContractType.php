<?php

namespace RentJeeves\LandlordBundle\Form;

use CreditJeeves\DataBundle\Entity\Operation;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\LandlordBundle\Accounting\AccountingImport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use CreditJeeves\CoreBundle\Translation\Translator;

/**
 * This form for Contract
 *
 * Class ImportNewUserWithContractType
 * @package RentJeeves\LandlordBundle\Form
 */
class ImportContractType extends AbstractType
{
    protected $isUseToken;

    protected $isUseOperation;

    protected $tenant;

    protected $em;

    protected $translator;

    /**
     * @param Tenant $tenant
     * @param EntityManager $em
     * @param bool $token
     * @param bool $operation
     */
    public function __construct(
        Tenant $tenant,
        EntityManager $em,
        Translator $translator,
        $token = true,
        $operation = true
    ) {
        $this->isUseToken =  $token;
        $this->isUseOperation = $operation;
        $this->tenant = $tenant;
        $this->em = $em;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'startAt',
            'date',
            array(
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
            )
        );


        $builder->add(
            'finishAt',
            'date',
            array(
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
            )
        );

        $builder->add(
            'importedBalance',
            'text',
            array()
        );

        $builder->add(
            'rent',
            'text',
            array()
        );

        $builder->add(
            'skip',
            'checkbox',
            array(
                'data'      => false,
                'required'  => false,
                'mapped'    => false,
            )
        );

        $builder->add(
            'unit',
            new ImportUnitType()
        );

        $builder->add(
            'residentMapping',
            new ImportResidentMappingType(),
            array(
                'mapped' => false,
            )
        );

        if ($this->isUseToken) {
            $builder->add(
                '_token',
                'hidden',
                array(
                    'mapped' => false,
                )
            );
        }

        if ($this->isUseOperation) {
            $builder->add(
                'operation',
                new ImportOperationType(),
                array(
                    'mapped'=> false
                )
            );

            $self = $this;
            $builder->addEventListener(
                FormEvents::SUBMIT,
                function (FormEvent $event) use ($options, $self) {
                    $form = $event->getForm();
                    if (is_null($self->tenant->getId())) {
                        return;
                    }
                    /**
                     * @var $contract Contract
                     */
                    $contract = $form->getData();
                    if (is_null($contract->getId())) {
                        return;
                    }

                    $operationField = $form->get('operation');
                    /**
                     * @var $operation Operation
                     */
                    $operation = $operationField->getData();

                    $operation = $self->em->getRepository('DataBundle:Operation')->getOperationForImport(
                        $self->tenant,
                        $contract,
                        $operation->getPaidFor(),
                        $operation->getAmount()
                    );

                    if (empty($operation)) {
                        return;
                    }

                    $errorMessage = $self->translator->trans('error.operation.exist');
                    $amount = $operationField->get('amount')->addError(new FormError($errorMessage));
                    $paidFor = $operationField->get('paidFor')->addError(new FormError($errorMessage));
                }
            );
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'RentJeeves\DataBundle\Entity\Contract',
                'validation_groups' => array(
                    'import',
                ),
                'csrf_protection'    => false,
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'import_contract';
    }
}
