<?php

namespace RentJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PartnerAdmin extends Admin
{
    const TYPE = 'partner';

    /**
     * {@inheritdoc}
     */
    public function getBaseRouteName()
    {
        return 'admin_' . self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRoutePattern()
    {
        return '/' . self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name')
            ->add('requestName')
            ->add('client.name')
            ->add('poweredBy')
            ->add('logoName')
            ->add('loginUrl')
            ->add('address')
            ->add(
                '_action',
                'actions',
                [
                    'actions' => [
                        'edit' => [],
                        'delete' => [],
                        'partner_users' => [
                            'template' => 'AdminBundle:CRUD:list__action_partner_users.html.twig'
                        ],
                    ]
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name')
            ->add('requestName', null, ['required' => false])
            ->add('client')
            ->add('poweredBy', 'checkbox', ['required' => false])
            ->add('logoName')
            ->add('loginUrl')
            ->add('address');
    }

    /**
     * {@inheritdoc}
     */
    public function getClassnameLabel()
    {
        return 'partner';
    }
}
