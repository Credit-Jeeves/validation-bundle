<?php

namespace RentJeeves\LandlordBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ContractType extends AbstractType
{

    protected $user;

    public function __construct($user) 
    {
        $this->user = $user;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($isAdmin = $this->user->getIsSuperAdmin()) {
            $holding = $this->user->getHolding();
            $groups = $holding->getGroups() ? $holding->getGroups() : null;
        } else {
            $groups = $this->user->getAgentGroups() ? $this->user->getAgentGroups() : null;
        }

        $builder->add('rent');
        $builder->add(
            'finishAt',
            'date',
            array(
                'input'             => 'string',
                'widget'            => 'single_text',
                'format'            => 'dd/MM/yyyy',
                'error_bubbling'    => true,
            )
        );
        $builder->add(
            'startAt',
            'date',
            array(
                'input'             => 'string',
                'widget'            => 'single_text',
                'format'            => 'dd/MM/yyyy',
                'error_bubbling'    => true,
            )
        );
        $builder->add(
            'property',
            'entity',
             array(
                'class'             => 'RjDataBundle:Property',
                'error_bubbling'    => true,
                'query_builder'     => function(EntityRepository $er) use ($groups) {

                    if (!$groups) {
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
                    $query = $er->createQueryBuilder('p');
                    $query->innerJoin('p.property_groups', 'g');
                    $query->where('g.id IN (:groupsIds)');
                    $query->setParameter('groupsIds', $groupsIds);

                    return $query;
                }
            )
        );
        $builder->add(
            'unit',
            'entity',
            array(
                'class'             => 'RjDataBundle:Property',
                'error_bubbling'    => true,
                'query_builder'     => function(EntityRepository $er) {
                    //it's need becouse I need empty unit field and fill it dinamic
                    $query = $er->createQueryBuilder('p');
                    $query->where('p.id = :sero');
                    $query->setParameter('sero', 0);
                    return $query;
                }
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'RentJeeves\DataBundle\Entity\Contract',
                'validation_groups' => array(
                    'tenant_invite',
                ),
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_publicbundle_invitetype';
    }
}
