<?php

namespace RentJeeves\LandlordBundle\Form;

use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as ImportMapping;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\Exception\RuntimeException;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ImportFileAccountingType extends AbstractType
{
    protected $group;

    protected $validationGroups;

    protected $availableValidationGroups = array(
        'default', 'yardi', 'csv'
    );

    public function __construct($group, $validationGroups = array('default'))
    {
        $this->group = $group;
        $this->setValidationGroup($validationGroups);
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
        $group = $this->group;

        $builder->add(
            'property',
            'entity',
            array(
                'empty_value'   => 'landlord.form.import.multiple.property',
                'class'         => 'RjDataBundle:Property',
                'attr'          => array(
                    'class' => 'original widthSelect',
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
                            'groups'  => array('yardi'),
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
