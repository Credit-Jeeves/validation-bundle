<?php

namespace RentJeeves\LandlordBundle\Form;

use RentJeeves\LandlordBundle\Accounting\Import\ImportMapping;
use RentJeeves\LandlordBundle\Accounting\Import\ImportStorage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\Exception\RuntimeException;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ImportFileAccountingType extends AbstractType
{
    protected $group;

    public function __construct($group)
    {
        $this->group = $group;
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
                }
            )
        );

        $builder->add(
            'attachment',
            'file',
            array(
                'error_bubbling' => true,
                'label'          => 'csv.file',
                'constraints'    => array(
                    new NotBlank(
                        array(
                            'message' => 'error.file.empty'
                        )
                    ),
                    new File(
                        array(
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
                    'class' => 'half-width'
                ),
                'constraints'    => array(
                    new NotBlank(),
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
                    'class' => 'half-width'
                ),
                'constraints'    => array(
                    new NotBlank(),
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
                    'class' => 'half-width original import-date'
                ),
                'constraints'    => array(
                    new NotBlank(),
                ),
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection'    => true,
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'import_file_type';
    }
}
