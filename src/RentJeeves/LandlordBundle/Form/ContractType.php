<?php

namespace RentJeeves\LandlordBundle\Form;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\Query\Expr;
use RentJeeves\DataBundle\Entity\Contract;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ContractType extends AbstractType
{
    const MONTH_TO_MONTH = 'monthToMonth';
    const FINISH_AT = 'finishAt';

    /**
     * @var Group $group
     */
    protected $group;

    /**
     * @param Group $group
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('rent', 'text', ['error_bubbling' => true]);
        $builder->add(
            'finishAt',
            'date',
            [
                'input' => 'string',
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
                'error_bubbling' => true,
            ]
        );

        $builder->add(
            'startAt',
            'date',
            [
                'input' => 'string',
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
                'error_bubbling' => true,
            ]
        );

        $builder->add(
            'dueDate',
            'choice',
            [
                'label' => 'due_date',
                'choices' => Contract::getRangeDueDate(),
                'error_bubbling' => true,
                'required' => true,
            ]
        );

        $builder->add(
            'finishAtType',
            'choice',
            [
                'choices' => [
                    'monthToMonth' => self::MONTH_TO_MONTH,
                    'finishAt' => self::FINISH_AT
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'mapped' => false,
                'data' => self::FINISH_AT
            ]
        );

        $builder->add(
            'property',
            'entity',
            [
                'class' => 'RjDataBundle:Property',
                'error_bubbling' => true,
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('p')
                        ->addSelect('CONCAT(p.number, p.street) AS HIDDEN sortField')
                        ->innerJoin('p.property_groups', 'g')
                        ->innerJoin('p.units', 'u', Expr\Join::WITH, 'u.group = :group')
                        ->where('g.id = :groupId')
                        ->setParameter('groupId', $this->group->getId())
                        ->setParameter('group', $this->group)
                        ->orderBy('sortField');
                },
            ]
        );
        $builder->add(
            'unit',
            'entity',
            [
                'class' => 'RjDataBundle:Unit',
                'error_bubbling' => true,
                'choices' => []
            ]
        );
        $self = $this;
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($self) {
                $self->processUnit($event);
            }
        );
    }

    /**
     * @param FormEvent $event
     */
    protected function processUnit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (!isset($data['property'])) {
            return;
        }
        $propertyId = $data['property'];

        $form->add(
            'unit',
            'entity',
            [
                'class' => 'RjDataBundle:Unit',
                'error_bubbling' => true,
                'query_builder' => function (EntityRepository $repository) use ($propertyId) {
                    return $repository->createQueryBuilder('u')
                        ->where('u.property = :propertyId')
                        ->andWhere('u.group = :groupId')
                        ->setParameter('propertyId', $propertyId)
                        ->setParameter('groupId', $this->group->getId());
                },
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'RentJeeves\DataBundle\Entity\Contract',
                'validation_groups' => ['tenant_invite'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'rentjeeves_publicbundle_invitetype';
    }
}
