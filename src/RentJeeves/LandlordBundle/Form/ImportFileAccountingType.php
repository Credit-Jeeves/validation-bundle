<?php

namespace RentJeeves\LandlordBundle\Form;

use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyMapping;
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

    protected $landlord;

    protected $availableValidationGroups = array(
        'default', 'yardi', 'csv'
    );

    public function __construct(Landlord $landlord, $em, $validationGroups = array('default'))
    {
        $this->landlord = $landlord;
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
        $group = $this->landlord->getCurrentGroup();

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
                ], $this->landlord->getIsSuperAdmin() ? [ImportType::MULTI_GROUPS => 'import.type.multi_groups'] : [])
            ]
        );

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
                    'data-bind' => 'visible: ($root.importType() == "' .
                        ImportType::SINGLE_PROPERTY .
                        '" || $root.source() == "yardi")',
                ),
                'label_attr' => array(
                    'data-bind' => 'visible: ($root.importType() == "' .
                        ImportType::SINGLE_PROPERTY .
                        '" || $root.source() == "yardi")',
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
                            'groups'  => array('yardi', 'single_property'),
                            'message' => 'import.errors.single_property_select'
                        )
                    )
                )
            )
        );

        $builder->add(
            'fileType',
            'choice',
            array(
                'choices' => array(
                    'csv'       => 'common.csv',
                    'yardi'     => 'import.integrated_api',
                ),
                'multiple' => false,
                'data'  => 'csv',
                'label' => 'common.source',
                'attr' => array(
                    'data-bind' => 'checked: source',
                ),
                'expanded' => true,
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups'  => array('default'),
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
                    'data-bind' => 'visible: ($root.source() == "yardi")'
                ),
                'label_attr' => array(
                    'data-bind' => 'visible: ($root.source() == "yardi")',
                ),
                'constraints'    => array(
                    new NotBlank(
                        array(
                            'groups'  => array('yardi'),
                        )
                    ),
                ),
            )
        );

        $builder->add(
            'onlyException',
            'checkbox',
            array(
                'label'     => 'import.onlyException',
                'required'  => false,
                'attr'      => array(
                    'data-bind' => 'visible: ($root.source() == "yardi")'
                ),
                'label_attr' => array(
                    'data-bind' => 'visible: ($root.source() == "yardi")',
                )
            )
        );

        $self = $this;
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($self) {
                $data = $event->getData();
                $form = $event->getForm();
                /**
                 * @var $property Property
                 */
                $property = $form->get('property')->getData();
                $propertyId = $form->get('propertyId')->getData();
                if (!$property || !$propertyId) {
                    return;
                }

                $holdingId = $self->landlord->getCurrentGroup()->getHolding()->getId();
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
