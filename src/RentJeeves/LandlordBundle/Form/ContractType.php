<?php

namespace RentJeeves\LandlordBundle\Form;

use RentJeeves\DataBundle\Entity\Contract;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ContractType extends AbstractType
{

    const MONTH_TO_MONTH = 'monthToMonth';

    const FINISH_AT = 'finishAt';

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

        $builder->add('rent', 'text', array('error_bubbling' => true));
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
            'choice',
            array(
                'label'             => 'due_date',
                'choices'           => Contract::getRangeDueDate(),
                'error_bubbling'    => true,
                'required'          => true,
            )
        );

        $builder->add(
            'finishAtType',
            'choice',
            array(
                'choices' => array(
                    'monthToMonth' => self::MONTH_TO_MONTH,
                    'finishAt'     => self::FINISH_AT
                ),
                'expanded'  => true,
                'multiple'  => false,
                'required'  => true,
                'mapped'    => false,
                'data'      => self::FINISH_AT
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
        $self = $this;
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($group, $self) {
                $self->processUnit($event, $group);
            }
        );
    }

    protected function processUnit(FormEvent $event, $group)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (!isset($data['property'])) {
            return;
        }

        $propertyId = $data['property'];

        $formOptions = array(
            'class'             => 'RjDataBundle:Unit',
            'error_bubbling'    => true,
            'query_builder'     => function (EntityRepository $er) use ($group, $propertyId) {

                    $query = $er->createQueryBuilder('u');
                    $query->where('u.property = :propertyId');
                    $query->andWhere('u.group = :groupId');
                    $query->setParameter('propertyId', $propertyId);
                    $query->setParameter('groupId', $group->getId());

                    return $query;
            },
        );

        $form->add('unit', 'entity', $formOptions);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'RentJeeves\DataBundle\Entity\Contract',
                'validation_groups' => array('tenant_invite'),
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_publicbundle_invitetype';
    }
}
