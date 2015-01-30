<?php

namespace RentJeeves\LandlordBundle\Form;

use CreditJeeves\DataBundle\Entity\Operation;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\LandlordBundle\Model\Import;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use CreditJeeves\CoreBundle\Translation\Translator;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\ContractManager;

/**
 * This form for Contract
 *
 * Class ImportNewUserWithContractType
 * @package RentJeeves\LandlordBundle\Form
 */
class ImportContractType extends AbstractType
{
    const NAME = 'import_contract';

    protected $isUseToken;

    protected $isUseOperation;

    protected $import;

    protected $em;

    protected $translator;

    protected $isMultipleProperty;

    protected $sendInvite;

    /**
     * @param Tenant $tenant
     * @param EntityManager $em
     * @param bool $token
     * @param bool $operation
     */
    public function __construct(
        EntityManager $em,
        Translator $translator,
        Import $import,
        $token = true,
        $isMultipleProperty = false,
        $sendInvite = true
    ) {
        $this->isUseToken =  $token;
        $this->em = $em;
        $this->translator = $translator;
        $this->import = $import;
        $this->isMultipleProperty = $isMultipleProperty;
        $this->sendInvite = $sendInvite;
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

        if ($this->sendInvite) {
            $builder->add(
                'sendInvite',
                'checkbox',
                array(
                    'data'      => true,
                    'required'  => false,
                    'mapped'    => false,
                )
            );
        }

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

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($self) {
                $self->setUnitName($event);
                $self->setResidentId($event);
            }
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($self) {
                $self->setUncollectedBalance($event);
                $self->createOperation($event);
                $self->movePaidTo($event);
            }
        );
    }

    public function createOperation(FormEvent $event)
    {
        $handler = $this->import->getHandler();
        $isIsNeedCreateCashOperation = $handler->isNeedCreateCashOperation();
        $dueDate = $handler->getDueDateOfContract();
        if (!$isIsNeedCreateCashOperation) {
            return;
        }

        $operation = $handler->getOperationByDueDate($dueDate);
        $csrfToken = $this->import->getCsrfToken();

        if ($operation &&
            is_null($operation->getContract()) &&
            empty($csrfToken)
        ) {
            $handler->processingOperationAndOrder($operation);
        }
    }

    public function movePaidTo(FormEvent $event)
    {
        /**
         * @var $contract Contract
         */
        $contract = $event->getData();
        $handler = $this->import->getHandler();
        $dueDate = $handler->getDueDateOfContract();
        $handler->movePaidToOfContract($dueDate);
    }

    public function setUncollectedBalance(FormEvent $event)
    {
        /**
         * @var $contract Contract
         */
        $contract = $event->getData();
        $handler = $this->import->getHandler();
        if ($contract->getIntegratedBalance() > 0 && $handler->isFinishedContract()) {
            $contract->setUncollectedBalance($contract->getIntegratedBalance());
        }
    }

    public function setUnitName(FormEvent $event)
    {
        if (!$unit = $this->import->getContract()->getUnit()) {
            return;
        }

        $data = $event->getData();

        if (empty($data)) {
            return;
        }

        if (!isset($data['unit'])) {
            $data['unit'] = array();
        }

        $data['unit'] = array(
            'name' => $unit->getActualName(),
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
            'residentId' => $this->import->getResidentMapping()->getResidentId(),
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
            'externalUnitId' => $this->import->getUnitMapping()->getExternalUnitId(),
        );
        $event->setData($data);
    }

    public function validateUnit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        $isSingle = $form->get('isSingle')->getData();

        if ((empty($data['unit']) && !$isSingle) || (!empty($data['unit']) && $isSingle)) {
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
        return self::NAME;
    }
}
