<?php

namespace RentJeeves\LandlordBundle\Form;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as ImportMapping;
use RentJeeves\LandlordBundle\Form\Enum\ImportType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\Exception\RuntimeException;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ImportFileAccountingType extends AbstractType
{
    protected $em;

    protected $validationGroups;

    protected $isHoldingAdmin = false;

    protected $currentGroup;

    protected $availableValidationGroups = [
        'default', 'integrated_api', 'csv'
    ];

    public function __construct(
        $isHoldingAdmin,
        Group $currentGroup,
        EntityManager $em,
        $validationGroups = ['default']
    ) {
        $this->currentGroup = $currentGroup;
        $this->isHoldingAdmin = $isHoldingAdmin;
        $this->setValidationGroup($validationGroups);
        $this->em = $em;
    }

    protected function setValidationGroup($validationGroups)
    {
        if (empty($validationGroups)) {
            throw new RuntimeException("Empty validation group");
        }

        foreach ($validationGroups as $group) {
            if (!in_array($group, $this->availableValidationGroups)) {
                throw new RuntimeException(sprintf('Wrong validation group:%s', $group));
            }
        }

        $this->validationGroups = $validationGroups;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $group = $this->currentGroup;

        $builder->add(
            'importType',
            'choice',
            [
                'label' => 'import.type',
                'label_attr' => [
                    'data-bind' => 'visible: ($root.source() == "csv")',
                ],
                'attr' => [
                    'class' => 'original widthSelect',
                    'data-bind' => 'value: importType, visible: ($root.source() == "csv")',
                ],
                'choices' => array_merge([
                    ImportType::SINGLE_PROPERTY => 'import.type.single_property',
                    ImportType::MULTI_PROPERTIES => 'import.type.multi_property',
                ], $this->isHoldingAdmin ? [ImportType::MULTI_GROUPS => 'import.type.multi_groups'] : [])
            ]
        );

        $integrationApiSettings = $this->currentGroup->getIntegratedApiSettings();

        if (!is_null($integrationApiSettings) && $integrationApiSettings->isMultiProperty()) {
            $dataBindProperty = 'visible: ($root.source() == "csv")';
            $propertyGroupValidation = [];
        } else {
            $dataBindProperty = sprintf(
                'visible: ($root.importType() == "%s" || $root.source() == "integrated_api")',
                ImportType::SINGLE_PROPERTY
            );
            $propertyGroupValidation = ['integrated_api', 'single_property'];
        }

        $builder->add(
            'property',
            'entity',
            array(
                'empty_value' => 'import.property.empty_value',
                'error_bubbling' => true,
                'class'         => 'RjDataBundle:Property',
                'attr'          => array(
                    'force_row' => true,
                    'class' => 'original widthSelect',
                    'data-bind' => $dataBindProperty,
                ),
                'label_attr' => array(
                    'data-bind' => $dataBindProperty,
                ),
                'required'      => false,
                'mapped'        => false,
                'query_builder' => function (EntityRepository $er) use ($group) {
                    $query = $er->createQueryBuilder('p');
                    $query->innerJoin('p.property_groups', 'g');
                    $query->where('g.id = :group');
                    $query->setParameter('group', $group->getId());

                    return $query;
                },
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups'  => $propertyGroupValidation,
                            'message' => 'import.errors.single_property_select'
                        )
                    )
                )
            )
        );

        if ($this->currentGroup->getHolding()->getApiIntegrationType() !== ApiIntegrationType::NONE) {
            $choices = array(
                'csv'               => 'common.csv',
                'integrated_api'    => 'import.integrated_api',
            );
        } else {
            $choices = array(
                'csv'               => 'common.csv',
            );
        }

        $builder->add(
            'fileType',
            'choice',
            array(
                'choices' => $choices,
                'multiple' => false,
                'data' => 'csv',
                'label' => 'common.source',
                'attr' => array(
                    'data-bind' => 'checked: source',
                ),
                'expanded' => true,
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('default'),
                            'message' => 'yardi.import.error.file_type_empty'
                        )
                    )
                )
            )
        );

        $builder->add(
            'attachment',
            'file',
            array(
                'error_bubbling' => true,
                'required'       => false,
                'label'          => 'csv.file',
                'attr'           => array(
                    'data-bind' => 'visible: ($root.source() == "csv")',
                ),
                'label_attr' => array(
                    'data-bind' => 'visible: ($root.source() == "csv")',
                ),
                'constraints'    => array(
                    new NotBlank(
                        array(
                            'groups'  => array('csv'),
                            'message' => 'error.file.empty'
                        )
                    ),
                    new File(
                        array(
                            'groups'  => array('csv'),
                            'maxSize' => '2M',
                            'mimeTypes' => array(
                                'text/csv',
                                'text/plain'
                            )
                        )
                    )
                ),
            )
        );

        $builder->add(
            'fieldDelimiter',
            'text',
            array(
                'data'           => ',',
                'label'          => 'field.delimiter',
                'attr'           => array(
                    'class' => 'half-width',
                    'data-bind' => 'visible: ($root.source() == "csv")',
                ),
                'label_attr' => array(
                    'data-bind' => 'visible: ($root.source() == "csv")',
                ),
                'constraints'    => array(
                    new NotBlank(
                        array(
                            'groups'  => array('csv'),
                        )
                    ),
                ),
            )
        );

        $builder->add(
            'textDelimiter',
            'text',
            array(
                'data'           => '"',
                'label'          => 'text.delimiter',
                'attr'           => array(
                    'class' => 'half-width',
                    'data-bind' => 'visible: ($root.source() == "csv")',
                ),
                'label_attr' => array(
                    'data-bind' => 'visible: ($root.source() == "csv")',
                ),
                'constraints'    => array(
                    new NotBlank(
                        array(
                            'groups'  => array('csv'),
                        )
                    ),
                ),
            )
        );

        $builder->add(
            'dateFormat',
            'choice',
            array(
                'choices'   => ImportMapping::$mappingDates,
                'label'     => 'common.date_format',
                'attr'      => array(
                    'class' => 'half-width original import-date',
                    'data-bind' => 'visible: ($root.source() == "csv")'
                ),
                'label_attr' => array(
                    'data-bind' => 'visible: ($root.source() == "csv")',
                ),
                'constraints'    => array(
                    new NotBlank(
                        array(
                            'groups'  => array('csv'),
                        )
                    ),
                ),
            )
        );

        $builder->add(
            'propertyId',
            'text',
            array(
                'label'     => 'common.property_id',
                'required'  => false,
                'attr'      => array(
                    'class' => 'half-width original',
                    'data-bind' => 'visible: ($root.source() == "integrated_api")'
                ),
                'label_attr' => array(
                    'data-bind' => 'visible: ($root.source() == "integrated_api")',
                ),
                'constraints'    => array(
                    new NotBlank(
                        array(
                            'groups'  => array('integrated_api'),
                        )
                    ),
                ),
            )
        );

        $builder->add(
            'onlyException',
            'checkbox',
            array(
                'label' => 'import.onlyException',
                'required' => false,
                'attr'      => array(
                    'class' => 'half-width original',
                    //@TODO remove it when for resman it's will work
                    'data-bind' =>
                        'visible:(!($root.integrationType() == "resman" && $root.source() == "integrated_api"))'
                ),
                //@TODO remove it when for resman it's will work
                'label_attr' => array(
                    'data-bind' =>
                        'visible:(!($root.integrationType() == "resman" && $root.source() == "integrated_api"))',
                ),
            )
        );

        $self = $this;
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($self) {
                $form = $event->getForm();
                /**
                 * @var $property Property
                 */
                $property = $form->get('property')->getData();
                $propertyId = $form->get('propertyId')->getData();
                if (!$property || !$propertyId) {
                    return;
                }

                $holdingId = $self->currentGroup->getHolding()->getId();
                /**
                 * @var $propertyMapping PropertyMapping
                 */
                $propertyMapping = $self->em->getRepository('RjDataBundle:PropertyMapping')->findOneBy(
                    array(
                        'property' => $property->getId(),
                        'holding'  => $holdingId
                    )
                );

                if ($propertyMapping && $propertyMapping->getExternalPropertyId() !== $propertyId) {
                    $propertyIdField = $form->get('propertyId');
                    $propertyIdField->addError(new FormError('yardi.import.error.external_property_id'));
                }
            }
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $self = $this;
        $resolver->setDefaults(
            array(
                'csrf_protection'    => true,
                'cascade_validation' => true,
                'validation_groups' => function (FormInterface $form) use ($self) {
                    if ($form->isSubmitted()) {
                        $data = $form->getData();
                        $groups = array('default', $data['fileType']);
                        if (ImportType::SINGLE_PROPERTY == $data['importType']) {
                            $groups = array_merge($groups, ['single_property']);
                        }

                        return $groups;
                    }

                    return $self->validationGroups;
                }
            )
        );
    }

    public function getName()
    {
        return 'import_file_type';
    }
}
