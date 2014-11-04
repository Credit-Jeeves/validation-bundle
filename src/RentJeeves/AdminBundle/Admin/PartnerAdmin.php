<?php

namespace RentJeeves\AdminBundle\Admin;

use CreditJeeves\DataBundle\Enum\GroupType;
use CreditJeeves\DataBundle\Enum\UserType;
use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;

class PartnerAdmin extends Admin
{

    const TYPE = 'partner';

    /**
     * {@inheritdoc}
     */
    public function getBaseRouteName()
    {
        return 'admin_'.self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRoutePattern()
    {
        return '/'.self::TYPE;
    }
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name')
            ->add('requestName')
            ->add('client.name')
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'edit' => array(),
                        'delete' => array(),
                        'partner_users' => array(
                            'template' => 'AdminBundle:CRUD:list__action_partner_users.html.twig'
                        ),
                    )
                )
            );
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name')
            ->add('requestName', null, ['required' => false])
            ->add('client');
    }

    public function getClassnameLabel()
    {
        return 'partner';
    }
}
