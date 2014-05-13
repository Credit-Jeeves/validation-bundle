<?php

namespace RentJeeves\LandlordBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ContractType extends AbstractType
{

    protected $user;
    protected $group;

    public function __construct($user, $group = null)
    {
        $this->user = $user;
        $this->group = $group;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($isAdmin = $this->user->getIsSuperAdmin()) {
            $holding = $this->user->getHolding();
            $groups = $holding->getGroups() ? $holding->getGroups() : null;
        } else {
            $groups = $this->user->getAgentGroups() ? $this->user->getAgentGroups() : null;
        }
        $group = $this->group;

        $builder->add('rent');
        $builder->add(
            'finishAt',
            'date',
            array(
                'input'             => 'string',
                'widget'            => 'single_text',
                'format'            => 'MM/dd/yyyy',
                'error_bubbling'    => true,
            )
        );

        $builder->add(
            'startAt',
            'date',
            array(
                'input'             => 'string',
                'widget'            => 'single_text',
                'format'            => 'MM/dd/yyyy',
                'error_bubbling'    => true,
            )
        );

        $builder->add(
            'dueDate',
            'choices',
            array(
                'error_bubbling'    => true,
            )
        );

        $builder->add(
            'property',
            'entity',
            array(
                'class'             => 'RjDataBundle:Property',
                'error_bubbling'    => true,
                'query_builder'     => function (EntityRepository $er) use ($groups) {

                    if ($this->group) {
                        $query = $er->createQueryBuilder('p');
                        $query->innerJoin('p.property_groups', 'g');
                        $query->where('g.id = :groupId');
                        $query->setParameter('groupId', $this->group->getId());
                        return $query;
                    }
                
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
                'class'             => 'RjDataBundle:Unit',
                'error_bubbling'    => true
            )
        );

        $builder->addEventListener(
            FormEvents::PRE_BIND,
            function (FormEvent $event) use ($group) {
                $form = $event->getForm();
                $data = $event->getData();
                if (!isset($data['property'])) {
                    return;
                }

                $property = $data['property'];

                $formOptions = array(
                    'class'             => 'RjDataBundle:Unit',
                    'error_bubbling'    => true,
                    'query_builder'     => function (EntityRepository $er) use ($group, $property) {

                        $query = $er->createQueryBuilder('u');
                        $query->where('u.property = :property');
                        $query->andWhere('u.group = :groupId');
                        $query->setParameter('property', $property);
                        $query->setParameter('groupId', $group->getId());
                        
                        return $query;
                    },
                );

                $form->add('unit', 'entity', $formOptions);
            }
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
