<?php

namespace RentJeeves\ApiBundle\Forms;

use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Contract;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Null;
use Symfony\Component\Validator\ExecutionContextInterface;

class ContractType extends AbstractType
{
    const NAME = '';

    /**
     * @var bool
     */
    public $submit = false;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('unit_url', 'entity', [
            'class' => 'RentJeeves\DataBundle\Entity\Unit',
            'mapped' => false,
            'constraints' => [
                new NotBlank([
                    'groups' => ['unit_url']
                ]),
                new Null([
                    'message' => 'api.errors.contract.unit_url.change',
                    'groups' => ['edit_contract', 'new_unit'],
                ])
            ]
        ]);

        $builder->add('new_unit', new NewUnitType(), [
            'mapped' => false,
            'property_path' => 'new_unit',
            'constraints' => [
                new NotBlank([
                    'message' => 'api.errors.contract.new_unit.empty',
                    'groups' => ['new_unit']
                ]),
                new Callback([
                    'methods' => [
                        [$this, 'isSubmitted']
                    ],
                    'groups' => ['edit_contract', 'unit_url'],
                ])
            ],
        ]);

        $builder->add('rent', null);

        $builder->add('due_date', null);

        $builder->add('lease_start', 'datetime', [
            'property_path' => 'startAt',
            'widget' => 'single_text',
        ]);

        $builder->add('lease_end', 'datetime', [
            'property_path' => 'finishAt',
            'widget' => 'single_text',
        ]);

        $builder->add('experian_reporting', new ReportingType(), [
            'property_path' => 'reportToExperian'
        ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if ($data instanceof Contract) {
                if ($data->getReportToExperian() && !$data->getExperianStartAt()) {
                    $data->setExperianStartAt(new DateTime());
                }
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $submittedData = $event->getData();
            if (!empty($submittedData['new_unit'])) {
                $this->submit = true;
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'RentJeeves\DataBundle\Entity\Contract',
            'csrf_protection' => false,
            'cascade_validation' => true,
            'validation_groups' => function (FormInterface $form) {
                $contract = $form->getData();

                $groups = [];

                if (!$contract->getId()) {
                    $unit = $form->get('unit_url')->getViewData();
                    if (is_null($unit) || $unit === '') {
                        $groups[] = 'new_unit';
                        $groups[] = 'invitationApi';
                    } else {
                        $groups[] = 'unit_url';
                    }
                } else {
                    $groups[] = 'edit_contract';
                }

                return $groups;
            }
        ]);
    }

    /**
     * @param array                     $data
     * @param ExecutionContextInterface $context
     */
    public function isSubmitted($data, ExecutionContextInterface $context)
    {
        if ($this->submit) {
            switch ($context->getGroup()) {
                case 'edit_contract':
                    $context->addViolation('api.errors.contract.new_unit.change');
                    break;
                case 'unit_url':
                    $context->addViolation('api.errors.contract.new_unit.unit_url.collision');
                    break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return static::NAME;
    }
}
