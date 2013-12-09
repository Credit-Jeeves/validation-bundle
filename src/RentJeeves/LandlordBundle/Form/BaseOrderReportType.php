<?php

namespace RentJeeves\LandlordBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;

class BaseOrderReportType extends AbstractType
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

        $builder->add(
            'type',
            'choice',
            array(
                'choices'   => array(
                    'xml' => 'base.order.report.type.yardi',
                    'csv' => 'base.order.report.type.realpage'
                ),
                'required'  => true,
                'attr'      => array(
                    'class'     => 'original widthSelect'
                ),
                'constraints' => array(
                    new NotBlank(),
                ),
            )
        );

        $builder->add(
            'property',
            'entity',
            array(
                'class'             => 'RjDataBundle:Property',
                'error_bubbling'    => true,
                'attr'              => array(
                    'class'         =>  'original widthSelect',
                ),
                'constraints' => array(
                    new NotBlank(),
                ),
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
            'begin',
            'date',
            array(
                'required'          => true,
                'input'             => 'string',
                'widget'            => 'single_text',
                'format'            => 'MM/dd/yyyy',
                'attr'              => array(
                    'class'     =>  'begin calendar',
                    'force_row' => true
                ),
                'constraints' => array(
                    new NotBlank(),
                    new Date(),
                )
            )
        );

        $builder->add(
            'end',
            'date',
            array(
                'input'             => 'string',
                'required'          => true,
                'widget'            => 'single_text',
                'format'            => 'MM/dd/yyyy',
                'attr'              => array(
                    'class' =>  'end calendar'
                ),
                'constraints' => array(
                    new NotBlank(),
                    new Date(),
                )
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection'    => true,
                'csrf_field_name'    => '_token',
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'base_order_report_type';
    }
}
