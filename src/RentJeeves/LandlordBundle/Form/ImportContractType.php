<?php

namespace RentJeeves\LandlordBundle\Form;

use CreditJeeves\DataBundle\Entity\Operation;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\UnitMapping;
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

    protected $unit;

    protected $residentMapping;

    protected $unitMapping;

    protected $em;

    protected $translator;

    protected $isMultipleProperty;

    /**
     * @param Tenant $tenant
     * @param EntityManager $em
     * @param bool $token
     * @param bool $operation
     */
    public function __construct(
        EntityManager $em,
        Translator $translator,
        Tenant $tenant,
        ResidentMapping $residentMapping,
        UnitMapping $unitMapping,
        Unit $unit = null,
        $token = true,
        $operation = true,
        $isMultipleProperty = false
    ) {
        $this->isUseToken =  $token;
        $this->isUseOperation = $operation;
        $this->tenant = $tenant;
        $this->em = $em;
        $this->translator = $translator;
        $this->unit = $unit;
        $this->residentMapping = $residentMapping;
        $this->unitMapping = $unitMapping;
        $this->isMultipleProperty = $isMultipleProperty;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $self = $this;

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
            'integratedBalance',
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

        /**
         * we must show this flag only for multiple property
         */
        if ($this->isMultipleProperty) {
            $builder->add(
                'isSingle',
                'checkbox',
                array(
                    'data'      => false,
                    'required'  => false,
                    'mapped'    => false,
                )
            );

            $builder->add(
                'unitMapping',
                new ImportUnitMappingType(),
                array(
                    'mapped' => false,
                )
            );

            $builder->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (FormEvent $event) use ($self) {
                    $self->validateUnit($event);
                    $self->setExternalUnitId($event);
                }
            );
        }

        if ($this->unit) {
            $builder->add(
                'unit',
                new ImportUnitType()
            );
        }

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
            $builder->addEventListener(
                FormEvents::SUBMIT,
                function (FormEvent $event) use ($options, $self) {
                    $self->processOperation($event);
                }
            );
        }

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($options, $self) {
                $self->setUnitName($event);
                $self->setResidentId($event);
            }
        );
    }

    public function setUnitName(FormEvent $event)
    {
        if (!$this->unit) {
            return;
        }

        $data = $event->getData();

        if (empty($data)) {
            return;
        }

        if (!isset($data['unit'])) {
            $data['unit'] = array();
        }

        if (isset($data['unit']['name'])) {
            return;
        }

        $data['unit'] = array(
            'name' => ($this->unit)? $this->unit->getName() : '',
        );
        $event->setData($data);
    }

    public function setResidentId(FormEvent $event)
    {
        $data = $event->getData();
        if (empty($data)) {
            return;
        }
        if (!isset($data['residentMapping'])) {
            $data['residentMapping'] = array();
        }
        $data['residentMapping'] = array(
            'residentId' => $this->residentMapping->getResidentId(),
        );
        $event->setData($data);
    }

    public function setExternalUnitId(FormEvent $event)
    {
        $data = $event->getData();
        if (empty($data)) {
            return;
        }
        if (!isset($data['unitMapping'])) {
            $data['unitMapping'] = array();
        }
        $data['unitMapping'] = array(
            'externalUnitId' => $this->unitMapping->getExternalUnitId(),
        );
        $event->setData($data);
    }

    protected function processOperation(FormEvent $event)
    {
        $form = $event->getForm();
        if (is_null($this->tenant->getId())) {
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

        if (!$operation->getPaidFor() || !$operation->getAmount()) {
            return;
        }

        $operation = $this->em->getRepository('DataBundle:Operation')->getOperationForImport(
            $this->tenant,
            $contract,
            $operation->getPaidFor(),
            $operation->getAmount()
        );

        if (empty($operation)) {
            return;
        }

        $errorMessage = $this->translator->trans('error.operation.exist');
        $amount = $operationField->get('amount')->addError(new FormError($errorMessage));
        $paidFor = $operationField->get('paidFor')->addError(new FormError($errorMessage));
    }

    public function validateUnit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        $isSingle = $form->get('isSingle')->getData();

        if ((empty($data['unit']) && !$isSingle) || (!empty($data['unit']) && $isSingle) ) {
            $errorMessage = $this->translator->trans('import.error.add_single_property_or_add_unit');
            $form->get('isSingle')->addError(new FormError($errorMessage));
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
