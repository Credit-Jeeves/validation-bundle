<?php

namespace RentJeeves\LandlordBundle\Form;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\DataBundle\Enum\ImportType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ImportFileAccountingType extends AbstractType
{
    /**
     * @var Group
     */
    protected $currentGroup;

    /**
     * @param Group $currentGroup
     */
    public function __construct(Group $currentGroup)
    {
        $this->currentGroup = $currentGroup;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $accountingSystem = $this->currentGroup->getHolding()->getAccountingSystem();
        $importSettings = $this->currentGroup->getImportSettings();

        if ($importSettings->getSource() === ImportSource::CSV &&
            $importSettings->getImportType() === ImportType::SINGLE_PROPERTY
        ) {
            $builder->add(
                'property',
                'entity',
                [
                    'empty_value' => 'import.property.empty_value',
                    'class' => 'RjDataBundle:Property',
                    'attr' => [
                        'force_row' => true,
                        'class' => 'original widthSelect',
                    ],
                    'required' => true,
                    'mapped' => false,
                    'query_builder' => function (EntityRepository $er) {
                        $query = $er->createQueryBuilder('p');
                        $query->innerJoin('p.property_groups', 'g');
                        $query->where('g.id = :group');
                        $query->setParameter('group', $this->currentGroup->getId());

                        return $query;
                    },
                    'constraints' => [
                        new NotBlank(
                            [
                                'groups' => ['import'],
                                'message' => 'import.errors.single_property_select'
                            ]
                        )
                    ]
                ]
            );
        }

        if ($importSettings->getSource() === ImportSource::CSV) {
            $builder->add(
                'attachment',
                'file',
                [
                    'required'       => true,
                    'label'          => 'csv.file',
                    'mapped'         => false,
                    'constraints'    => [
                        new NotBlank(
                            [
                                'groups' => ['import'],
                                'message' => 'error.file.empty'
                            ]
                        ),
                        new File(
                            [
                                'groups' => ['import'],
                                'maxSize' => '2M',
                                'mimeTypes' => [
                                    'text/csv',
                                    'text/plain'
                                ]
                            ]
                        )
                    ],
                ]
            );
        }

        //@TODO remove it when for resman it's will work
        if (AccountingSystem::RESMAN !== $accountingSystem) {
            $builder->add(
                'onlyException',
                'checkbox',
                [
                    'label' => 'import.onlyException',
                    'required' => false,
                    'mapped' => false,
                    'attr' => [
                        'class' => 'half-width original',
                    ]
                ]
            );
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'csrf_protection'    => true,
                'cascade_validation' => true,
                'validation_groups'  => ['import']
            ]
        );
    }

    public function getName()
    {
        return 'import_file_type';
    }
}
