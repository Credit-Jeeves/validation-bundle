<?php

namespace RentJeeves\LandlordBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\Exception\RuntimeException;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ImportFileAccountingType extends AbstractType
{
    protected $user;

    protected $group;


    public function __construct($user)
    {
        $this->user  = $user;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($isAdmin = $this->user->getIsSuperAdmin()) {
            $holding = $this->user->getHolding();
            $groups  = $holding->getGroups() ? $holding->getGroups() : null;
        } else {
            $groups = $this->user->getAgentGroups() ? $this->user->getAgentGroups() : null;
        }

        $builder->add(
            'property',
            'entity',
            array(
                'class'          => 'RjDataBundle:Property',
                'attr'           => array(
                    'class' => 'original widthSelect',
                ),
                'constraints'    => array(
                    new NotBlank(),
                ),
                'query_builder'  => function (EntityRepository $er) use ($groups) {

                    if (empty($groups)) {
                        $query = $er->createQueryBuilder('p');
                        $query->where('p.id = :sero');
                        $query->setParameter('sero', 0);
                        return $query;
                    }

                    $ids = array();
                    foreach ($groups as $group) {
                        $ids[$group->getId()] = $group->getId();
                    }
                    $groupsIds = implode("','", $ids);
                    $query     = $er->createQueryBuilder('p');
                    $query->innerJoin('p.property_groups', 'g');
                    $query->where('g.id IN (:groupsIds)');
                    $query->setParameter('groupsIds', $groupsIds);

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
                    new NotBlank(),
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
